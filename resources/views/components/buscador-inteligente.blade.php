@props([
    // Identificador del origen para la API de Consultas: su clave canónica o cualquier
    // alias (nombre del sistema, tag o URL). Por defecto toma el configurado por env
    // (CONSULTAS_ORIGEN), que es la fuente única de verdad para toda la app.
    'origen' => null,
])

@php($origen = $origen ?? config('services.consultas.origen'))

{{--
    Buscador inteligente del dashboard.
    Envía consultas en lenguaje natural (texto o voz) al proxy Laravel, que reenvía a la
    API de Consultas a Datos, y pinta la respuesta estructurada (texto / tabla / gráfico).
    La grabación de voz usa MediaRecorder (mismo enfoque que la integración /voz).
--}}
<div
    x-data="buscadorInteligente({
        origen: @js($origen),
        urlTexto: @js(route('consulta-ia.texto')),
        urlVoz: @js(route('consulta-ia.voz')),
    })"
    class="mt-4"
>
    {{-- Barra de búsqueda (tono ejecutivo + glow degradado difuminado) --}}
    <div class="relative">
        {{-- Glow degradado detrás de la barra --}}
        <div aria-hidden="true" class="pointer-events-none absolute -inset-1 z-0 overflow-hidden rounded-[1.4rem]">
            <div class="absolute right-3 top-1/2 h-20 w-48 -translate-y-1/2 rounded-full bg-gradient-to-r from-indigo-500 via-violet-500 to-fuchsia-500 opacity-20 blur-3xl"
                 :class="(loading || recording) ? 'opacity-40 animate-pulse' : 'opacity-20'"></div>
        </div>

        <div class="relative z-10 rounded-2xl border border-slate-200/70 bg-white/90 shadow-lg shadow-indigo-500/5 ring-1 ring-slate-900/5 backdrop-blur transition focus-within:border-indigo-300/80 focus-within:ring-indigo-200">
            <div class="flex items-center gap-2.5 px-3 py-2.5 sm:px-4 sm:py-3">
                {{-- Icono IA en cápsula con gradiente --}}
                <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white shadow-sm shadow-indigo-600/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </span>

                <input
                    type="text"
                    x-model="query"
                    x-ref="input"
                    @keydown.enter.prevent="submitTexto()"
                    :disabled="loading || recording"
                    placeholder="Pregúntale a la IA: «clientes por sector», «top 5 proyectos por monto»…"
                    class="min-w-0 flex-1 bg-transparent text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none disabled:opacity-60"
                >

                {{-- Micrófono / grabando --}}
                <button
                    type="button"
                    @click="toggleMic()"
                    :disabled="loading"
                    :title="recording ? 'Detener y enviar' : 'Consultar por voz'"
                    class="flex-shrink-0 rounded-xl p-2 transition-colors disabled:opacity-50"
                    :class="recording ? 'bg-red-500 text-white animate-pulse' : 'text-slate-400 hover:bg-slate-100 hover:text-indigo-600'"
                >
                    <svg x-show="!recording" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-14 0m7 7v3m0-3a4 4 0 01-4-4V6a4 4 0 118 0v5a4 4 0 01-4 4z"/>
                    </svg>
                    <svg x-show="recording" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                        <rect x="6" y="6" width="12" height="12" rx="2"/>
                    </svg>
                </button>

                {{-- Enviar (gradiente) --}}
                <button
                    type="button"
                    @click="submitTexto()"
                    :disabled="loading || recording || query.trim().length < 3"
                    class="relative flex-shrink-0 rounded-xl bg-gradient-to-br from-indigo-600 to-violet-600 px-3.5 py-2 text-sm font-medium text-white shadow-sm shadow-indigo-600/20 transition hover:from-indigo-700 hover:to-violet-700 hover:shadow-md hover:shadow-indigo-600/30 disabled:cursor-not-allowed disabled:from-slate-400 disabled:to-slate-400 disabled:shadow-none"
                >
                    <svg x-show="!loading" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                    <svg x-show="loading" x-cloak class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                </button>
            </div>

            {{-- Estado / loading --}}
            <div x-show="loading" x-cloak class="border-t border-slate-100 px-4 py-2 text-xs text-slate-500" x-text="loadingMsg"></div>
            <div x-show="recording" x-cloak class="border-t border-slate-100 px-4 py-2 text-xs font-medium text-red-500">
                🎙️ Grabando… toca el botón de stop para enviar tu pregunta.
            </div>
        </div>
    </div>

    {{-- Accesos rápidos: consultas predefinidas (se ocultan al mostrar un resultado) --}}
    <div x-show="!result && !loading" x-cloak class="mt-2.5 flex flex-wrap items-center gap-1.5">
        <span class="mr-0.5 text-[11px] font-medium uppercase tracking-wide text-slate-400">Accesos rápidos</span>
        <template x-for="s in sugerencias" :key="s.label">
            <button
                type="button"
                @click="usarSugerencia(s)"
                :disabled="loading || recording"
                :title="s.prompt"
                class="group inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white/70 px-2.5 py-1 text-xs text-slate-600 backdrop-blur transition hover:border-indigo-300 hover:bg-indigo-50/70 hover:text-indigo-700 disabled:opacity-50"
            >
                <span x-text="s.icon" class="text-[12px] leading-none"></span>
                <span x-text="s.label"></span>
            </button>
        </template>
    </div>

    {{-- Resultado --}}
    <div x-show="error" x-cloak class="mt-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        <span x-text="error"></span>
    </div>

    <div x-show="result" x-cloak class="mt-3 rounded-xl border border-slate-200 bg-white shadow-sm">
        {{-- Cabecera del resultado --}}
        <div class="flex items-start justify-between gap-3 border-b border-slate-100 px-4 py-3">
            <div class="min-w-0">
                <h4 class="truncate text-sm font-semibold text-slate-800" x-text="result?.titulo || 'Resultado'"></h4>
                <p x-show="transcrita" x-cloak class="mt-0.5 text-xs italic text-slate-400">
                    «<span x-text="transcrita"></span>»
                </p>
            </div>
            <div class="flex items-center gap-1.5">
                <button x-show="audioUrl" x-cloak type="button" @click="replayAudio()" title="Reproducir respuesta"
                    class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-indigo-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072M17.95 6.05a8 8 0 010 11.9M6.5 8.5H4a1 1 0 00-1 1v5a1 1 0 001 1h2.5l4 4V4.5l-4 4z"/>
                    </svg>
                </button>
                <button type="button" @click="reset()" title="Cerrar"
                    class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="px-4 py-4">
            {{-- Texto (siempre que venga el resumen) --}}
            <p x-show="result?.texto" x-cloak class="mb-3 text-sm text-slate-600" x-text="result?.texto"></p>

            {{-- Gráfico --}}
            <div x-show="result?.tipo === 'grafico'" x-cloak class="relative h-72 w-full">
                <canvas x-ref="chartCanvas"></canvas>
            </div>

            {{-- Tabla (también se muestra junto al gráfico si trae filas) --}}
            <div x-show="hasTabla()" x-cloak class="mt-3 overflow-x-auto rounded-lg border border-slate-100">
                <table class="min-w-full text-xs">
                    <thead class="bg-slate-50">
                        <tr>
                            <template x-for="(col, i) in (result?.tabla?.columnas || [])" :key="i">
                                <th class="whitespace-nowrap px-3 py-2 text-left font-semibold text-slate-600" x-text="col"></th>
                            </template>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(fila, r) in (result?.tabla?.filas || [])" :key="r">
                            <tr class="border-t border-slate-100 hover:bg-slate-50">
                                <template x-for="(celda, c) in fila" :key="c">
                                    <td class="whitespace-nowrap px-3 py-1.5 text-slate-700" x-text="fmt(celda)"></td>
                                </template>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <div x-show="result?.tabla?.truncado" x-cloak class="px-3 py-1.5 text-[11px] text-amber-600">
                    Resultados truncados (<span x-text="result?.tabla?.total_filas"></span> filas en total).
                </div>
            </div>

            {{-- Meta: SQL/endpoint ejecutado (auditoría) --}}
            <details x-show="result?.meta?.consulta_generada" x-cloak class="mt-3 text-xs">
                <summary class="cursor-pointer text-slate-400 hover:text-slate-600">Ver consulta generada</summary>
                <pre class="mt-1.5 overflow-x-auto rounded-lg bg-slate-50 p-3 font-mono text-[11px] text-slate-600" x-text="result?.meta?.consulta_generada"></pre>
            </details>
        </div>
    </div>
</div>

@once
<script>
(function () {
    // Registra el componente tanto en la carga inicial (alpine:init) como tras una
    // navegación SPA de Livewire (Alpine ya existe). Re-registrar es inocuo.
    function registrarBuscadorInteligente() {
    Alpine.data('buscadorInteligente', (opts) => ({
        origen: opts.origen,
        urlTexto: opts.urlTexto,
        urlVoz: opts.urlVoz,
        csrf: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',

        query: '',
        loading: false,
        loadingMsg: '',
        recording: false,
        error: null,
        result: null,
        transcrita: null,
        audioUrl: null,
        chart: null,
        rec: null,
        stream: null,
        chunks: [],

        // Accesos rápidos: consultas predefinidas alineadas a las vistas mapeadas
        // (dashboard de ofertas, oportunidades, clientes). `formato` fuerza el tipo de salida.
        sugerencias: [
            { label: 'Clientes por sector',    icon: '📊', prompt: 'cuántos clientes hay por sector',                                   formato: 'grafico' },
            { label: 'Top proyectos por monto', icon: '🏆', prompt: 'top 5 proyectos por monto en usd',                                  formato: 'tabla' },
            { label: 'Pipeline por estado',     icon: '📈', prompt: 'cuántos proyectos hay por estado',                                  formato: 'grafico' },
            { label: 'Ofertas del año',         icon: '📋', prompt: 'lista de oportunidades del año actual con su monto y estado',       formato: 'tabla' },
            { label: 'Clientes activos',        icon: '👥', prompt: 'cuántos clientes activos hay',                                       formato: 'texto' },
            { label: 'Monto total cartera',     icon: '💰', prompt: 'monto total de la cartera de proyectos en usd',                     formato: 'texto' },
        ],

        usarSugerencia(s) {
            if (this.loading || this.recording) return;
            this.query = s.prompt;
            this.submitTexto(s.formato || null);
        },

        // ── Consulta por texto ──
        async submitTexto(formato = null) {
            const q = this.query.trim();
            if (q.length < 3 || this.loading || this.recording) return;
            this.begin('Consultando…');
            try {
                const payload = { consulta: q, origen: this.origen };
                if (formato) payload.formato = formato;
                const { ok, data } = await this.post(this.urlTexto, { json: payload });
                this.finish(ok, data, null, null);
            } catch (e) {
                this.fail('No se pudo completar la consulta.');
            }
        },

        // ── Consulta por voz (MediaRecorder) ──
        async toggleMic() {
            if (this.recording) { this.stopMic(); return; }
            if (this.loading) return;
            try {
                this.stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            } catch (e) {
                this.fail('No se pudo acceder al micrófono. Revisa los permisos del navegador.');
                return;
            }
            this.chunks = [];
            this.rec = new MediaRecorder(this.stream);
            this.rec.ondataavailable = (e) => { if (e.data && e.data.size) this.chunks.push(e.data); };
            this.rec.onstop = () => this.sendAudio();
            this.rec.start();
            this.recording = true;
        },

        stopMic() {
            if (this.rec && this.rec.state !== 'inactive') this.rec.stop();
            this.recording = false;
            if (this.stream) { this.stream.getTracks().forEach((t) => t.stop()); this.stream = null; }
        },

        async sendAudio() {
            const blob = new Blob(this.chunks, { type: 'audio/webm' });
            if (!blob.size) return;
            this.begin('Transcribiendo y consultando…');
            try {
                const fd = new FormData();
                fd.append('file', blob, 'pregunta.webm');
                fd.append('origen', this.origen);
                const { ok, data } = await this.post(this.urlVoz, { form: fd });
                if (ok && data && data.resultado) {
                    if (data.pregunta_transcrita) this.query = data.pregunta_transcrita;
                    this.finish(true, data.resultado, data.pregunta_transcrita, data.audio_base64);
                } else {
                    this.finish(false, data, null, null);
                }
            } catch (e) {
                this.fail('No se pudo procesar el audio.');
            }
        },

        // ── HTTP helper ──
        async post(url, { json, form } = {}) {
            const headers = { 'X-CSRF-TOKEN': this.csrf, 'Accept': 'application/json' };
            let body;
            if (json) { headers['Content-Type'] = 'application/json'; body = JSON.stringify(json); }
            else { body = form; }
            const r = await fetch(url, { method: 'POST', headers, body });
            const data = await r.json().catch(() => null);
            return { ok: r.ok, status: r.status, data };
        },

        // ── Estado ──
        begin(msg) {
            this.loading = true;
            this.loadingMsg = msg;
            this.error = null;
        },

        fail(msg) {
            this.loading = false;
            this.recording = false;
            this.result = null;
            this.error = msg;
        },

        finish(ok, result, transcrita, audioB64) {
            this.loading = false;
            if (!ok || !result) {
                this.fail((result && result.detail) || 'Ocurrió un error al consultar el servicio.');
                return;
            }
            this.error = null;
            this.transcrita = transcrita || null;
            this.result = result;
            this.destroyChart();
            this.clearAudio();
            if (audioB64) this.loadAudio(audioB64);
            if (result.tipo === 'grafico' && result.grafico) {
                this.$nextTick(() => this.drawChart(result.grafico));
            }
        },

        reset() {
            this.result = null;
            this.error = null;
            this.transcrita = null;
            this.destroyChart();
            this.clearAudio();
        },

        // ── Tabla ──
        hasTabla() {
            return !!(this.result && this.result.tabla && Array.isArray(this.result.tabla.filas) && this.result.tabla.filas.length);
        },

        fmt(v) {
            if (v === null || v === undefined) return '—';
            if (typeof v === 'number') return v.toLocaleString('es-MX', { maximumFractionDigits: 2 });
            return v;
        },

        // ── Gráfico (Chart.js) ──
        drawChart(grafico) {
            const canvas = this.$refs.chartCanvas;
            if (!canvas || typeof Chart === 'undefined') return;
            const palette = [
                'rgb(99,102,241)', 'rgb(34,197,94)', 'rgb(245,158,11)', 'rgb(239,68,68)',
                'rgb(6,182,212)', 'rgb(168,85,247)', 'rgb(236,72,153)', 'rgb(59,130,246)',
            ];
            const tipo = grafico.tipo_grafico || 'bar';
            const datasets = (grafico.series || []).map((s, i) => {
                const color = palette[i % palette.length];
                const isArea = tipo === 'line';
                return {
                    label: s.label,
                    data: s.data,
                    backgroundColor: (tipo === 'pie' || tipo === 'doughnut')
                        ? (s.data || []).map((_, j) => palette[j % palette.length])
                        : (isArea ? color.replace('rgb', 'rgba').replace(')', ',0.12)') : color),
                    borderColor: color,
                    borderWidth: 2,
                    fill: isArea,
                    tension: 0.35,
                    pointRadius: isArea ? 3 : 0,
                    borderRadius: tipo === 'bar' ? 6 : 0,
                };
            });
            this.chart = new Chart(canvas.getContext('2d'), {
                type: tipo,
                data: { labels: grafico.etiquetas || [], datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: datasets.length > 1 || tipo === 'pie' || tipo === 'doughnut',
                            position: 'bottom',
                            labels: { font: { size: 11, family: 'Inter, sans-serif' }, color: '#64748b', usePointStyle: true, padding: 12 },
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15,23,42,0.85)', titleColor: '#e2e8f0', bodyColor: '#cbd5e1',
                            padding: 10, cornerRadius: 8,
                        },
                    },
                    scales: (tipo === 'pie' || tipo === 'doughnut') ? {} : {
                        x: { grid: { display: false }, ticks: { font: { size: 10 }, color: '#94a3b8' } },
                        y: { grid: { color: 'rgba(148,163,184,0.12)' }, ticks: { font: { size: 10 }, color: '#94a3b8' }, beginAtZero: true },
                    },
                },
            });
        },

        destroyChart() {
            if (this.chart) { this.chart.destroy(); this.chart = null; }
        },

        // ── Audio (WAV base64 → reproducible) ──
        loadAudio(b64) {
            try {
                const bytes = Uint8Array.from(atob(b64), (c) => c.charCodeAt(0));
                this.audioUrl = URL.createObjectURL(new Blob([bytes], { type: 'audio/wav' }));
                new Audio(this.audioUrl).play().catch(() => {});
            } catch (e) { /* audio opcional */ }
        },

        replayAudio() {
            if (this.audioUrl) new Audio(this.audioUrl).play().catch(() => {});
        },

        clearAudio() {
            if (this.audioUrl) { URL.revokeObjectURL(this.audioUrl); this.audioUrl = null; }
        },
    }));
    }

    if (window.Alpine) {
        registrarBuscadorInteligente();
    } else {
        document.addEventListener('alpine:init', registrarBuscadorInteligente);
    }
})();
</script>
@endonce
