<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Proxy del buscador inteligente del dashboard hacia la API de Consultas a Datos.
 *
 * El navegador nunca habla directo con el servicio externo (evita CORS y oculta la
 * URL/credenciales). Estos endpoints reenvían la petición server-side y devuelven el
 * objeto estructurado tal cual (tipo / texto / tabla / grafico / meta).
 *
 * Ver docs/API_CONSULTAS.md (proyecto langchain) para el contrato completo.
 */
class ConsultaIaController extends Controller
{
    /** Consulta por texto: POST {url}/api/v1/consultas/ */
    public function consultar(Request $request): JsonResponse
    {
        $data = $request->validate([
            'consulta' => ['required', 'string', 'min:3', 'max:1000'],
            'origen'   => ['nullable', 'string'],
            'formato'  => ['nullable', 'in:texto,tabla,grafico,informe'],
            'objetivo' => ['nullable', 'string'],
        ]);

        $payload = array_filter([
            'consulta' => $data['consulta'],
            'origen'   => $data['origen'] ?? config('services.consultas.origen'),
            'formato'  => $data['formato'] ?? null,
            'objetivo' => $data['objetivo'] ?? null,
            'usuario'  => $request->user()?->name,
        ], fn ($v) => $v !== null && $v !== '');

        try {
            $resp = $this->client()
                ->asJson()
                ->post($this->endpoint('/api/v1/consultas/'), $payload);
        } catch (\Throwable $e) {
            return $this->connectionError($e);
        }

        return $this->passthrough($resp);
    }

    /** Consulta por voz: POST {url}/api/v1/consultas/voz (multipart con audio). */
    public function voz(Request $request): JsonResponse
    {
        $data = $request->validate([
            'file'     => ['required', 'file', 'max:25600'], // 25 MB
            'origen'   => ['nullable', 'string'],
            'formato'  => ['nullable', 'in:texto,tabla,grafico,informe'],
            'objetivo' => ['nullable', 'string'],
        ]);

        $file = $request->file('file');

        $fields = array_filter([
            'origen'        => $data['origen'] ?? config('services.consultas.origen'),
            'formato'       => $data['formato'] ?? null,
            'objetivo'      => $data['objetivo'] ?? null,
            'usuario'       => $request->user()?->name,
            'responder_voz' => 'true',
        ], fn ($v) => $v !== null && $v !== '');

        try {
            $resp = $this->client()
                ->attach(
                    'file',
                    $file->get(),
                    $file->getClientOriginalName() ?: 'pregunta.webm',
                    ['Content-Type' => $file->getMimeType() ?: 'audio/webm']
                )
                ->post($this->endpoint('/api/v1/consultas/voz'), $fields);
        } catch (\Throwable $e) {
            return $this->connectionError($e);
        }

        return $this->passthrough($resp);
    }

    /** Estado del servicio y orígenes disponibles: GET {url}/api/v1/consultas/health */
    public function health(): JsonResponse
    {
        try {
            $resp = $this->client()->get($this->endpoint('/api/v1/consultas/health'));
        } catch (\Throwable $e) {
            return $this->connectionError($e);
        }

        return $this->passthrough($resp);
    }

    /** Cliente HTTP con timeout y, opcionalmente, headers de Cloudflare Access. */
    private function client()
    {
        $headers = [];
        $cfId = config('services.consultas.cf_client_id');
        $cfSecret = config('services.consultas.cf_client_secret');
        if ($cfId && $cfSecret) {
            $headers['CF-Access-Client-Id'] = $cfId;
            $headers['CF-Access-Client-Secret'] = $cfSecret;
        }

        return Http::timeout(config('services.consultas.timeout', 120))
            ->withHeaders($headers);
    }

    private function endpoint(string $path): string
    {
        return config('services.consultas.url') . $path;
    }

    /** Reenvía la respuesta del servicio (JSON) conservando el status code. */
    private function passthrough($resp): JsonResponse
    {
        $body = $resp->json();

        if ($body === null) {
            // El servicio no devolvió JSON (p.ej. error HTML de un gateway).
            return response()->json(
                ['detail' => 'Respuesta no válida del servicio de consultas.'],
                $resp->status() ?: 502
            );
        }

        if (is_array($body)) {
            $body = $this->renderInformeMarkdown($body);
        }

        return response()->json($body, $resp->status());
    }

    /**
     * Cuando la respuesta es un informe (`tipo === 'informe'`), el campo `texto`
     * trae Markdown. Lo convertimos a HTML SANEADO server-side y lo agregamos como
     * `texto_html`, para que el front lo pinte con x-html sin parsear Markdown en JS
     * ni arriesgar XSS. Maneja la forma de texto (tipo en la raíz) y la de voz
     * (el resultado viene anidado bajo `resultado`). Los demás tipos quedan intactos.
     */
    private function renderInformeMarkdown(array $body): array
    {
        if (($body['tipo'] ?? null) === 'informe' && ! empty($body['texto'])) {
            $body['texto_html'] = $this->markdownToHtml($body['texto']);
        }

        if (isset($body['resultado']) && is_array($body['resultado'])
            && ($body['resultado']['tipo'] ?? null) === 'informe'
            && ! empty($body['resultado']['texto'])) {
            $body['resultado']['texto_html'] = $this->markdownToHtml($body['resultado']['texto']);
        }

        return $body;
    }

    /** Markdown → HTML con el saneado de CommonMark (escapa HTML embebido, links seguros). */
    private function markdownToHtml(string $md): string
    {
        return Str::markdown($md, [
            'html_input'         => 'escape',
            'allow_unsafe_links' => false,
        ]);
    }

    private function connectionError(\Throwable $e): JsonResponse
    {
        Log::warning('Consulta IA: fallo de conexión con el servicio', [
            'url'   => config('services.consultas.url'),
            'error' => $e->getMessage(),
        ]);

        return response()->json([
            'detail' => 'No se pudo conectar con el servicio de consultas. Verifica que esté disponible.',
        ], 503);
    }
}
