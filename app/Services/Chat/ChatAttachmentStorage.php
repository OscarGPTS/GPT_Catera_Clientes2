<?php

namespace App\Services\Chat;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Almacena attachments del chat optimizando imágenes vía GD.
 *
 * Para JPEG/PNG/WebP: resize si la dimensión mayor supera MAX_DIMENSION, y
 * re-comprime al nivel de calidad indicado. Para otros tipos (PDF, ZIP, etc.)
 * se almacena el archivo original sin modificaciones.
 *
 * Fail-open: si GD no está disponible o algo falla durante el resize, se
 * guarda el archivo tal cual (no perder el upload por una optimización).
 */
class ChatAttachmentStorage
{
    private const MAX_DIMENSION = 1920;
    private const JPEG_QUALITY = 82;
    private const WEBP_QUALITY = 82;
    private const PNG_COMPRESSION = 8;
    private const DISK = 'public';
    private const DIR = 'chat-attachments';

    /**
     * Sube un archivo y devuelve los metadatos a guardar en el campo
     * attachments del ChatMensaje.
     *
     * @return array{name:string, path:string, type:string, size:int, url:string}
     */
    public function store(TemporaryUploadedFile $file): array
    {
        $mime = $file->getMimeType() ?? 'application/octet-stream';
        $originalName = $file->getClientOriginalName();
        $safeName = $this->safeFilename($originalName);
        $relativePath = self::DIR . '/' . time() . '_' . bin2hex(random_bytes(3)) . '_' . $safeName;

        $optimizedTmp = null;
        if ($this->isOptimizableImage($mime)) {
            $optimizedTmp = $this->tryOptimize($file->getRealPath(), $mime);
        }

        if ($optimizedTmp !== null) {
            Storage::disk(self::DISK)->put($relativePath, file_get_contents($optimizedTmp));
            @unlink($optimizedTmp);
        } else {
            $file->storeAs(self::DIR, basename($relativePath), self::DISK);
        }

        $finalSize = Storage::disk(self::DISK)->size($relativePath) ?: $file->getSize();

        return [
            'name' => $originalName,
            'path' => $relativePath,
            'type' => $mime,
            'size' => $finalSize,
            'url' => Storage::disk(self::DISK)->url($relativePath),
        ];
    }

    private function isOptimizableImage(string $mime): bool
    {
        if (! extension_loaded('gd')) {
            return false;
        }

        return in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true);
    }

    /**
     * Devuelve el path temporal del archivo optimizado, o null si falló.
     */
    private function tryOptimize(string $sourcePath, string $mime): ?string
    {
        try {
            $source = match ($mime) {
                'image/jpeg' => @imagecreatefromjpeg($sourcePath),
                'image/png'  => @imagecreatefrompng($sourcePath),
                'image/webp' => function_exists('imagecreatefromwebp')
                    ? @imagecreatefromwebp($sourcePath)
                    : false,
                default      => false,
            };

            if ($source === false) {
                return null;
            }

            $width = imagesx($source);
            $height = imagesy($source);
            $maxDim = max($width, $height);

            if ($maxDim > self::MAX_DIMENSION) {
                $ratio = self::MAX_DIMENSION / $maxDim;
                $newW = (int) round($width * $ratio);
                $newH = (int) round($height * $ratio);
                $resized = imagecreatetruecolor($newW, $newH);

                if ($mime === 'image/png' || $mime === 'image/webp') {
                    imagealphablending($resized, false);
                    imagesavealpha($resized, true);
                    $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
                    imagefilledrectangle($resized, 0, 0, $newW, $newH, $transparent);
                }

                imagecopyresampled($resized, $source, 0, 0, 0, 0, $newW, $newH, $width, $height);
                imagedestroy($source);
                $source = $resized;
            }

            $tmpPath = tempnam(sys_get_temp_dir(), 'chatimg_');
            $ok = match ($mime) {
                'image/jpeg' => imagejpeg($source, $tmpPath, self::JPEG_QUALITY),
                'image/png'  => imagepng($source, $tmpPath, self::PNG_COMPRESSION),
                'image/webp' => imagewebp($source, $tmpPath, self::WEBP_QUALITY),
                default      => false,
            };
            imagedestroy($source);

            if (! $ok) {
                @unlink($tmpPath);
                return null;
            }

            return $tmpPath;
        } catch (\Throwable $e) {
            Log::warning('Chat attachment optimization failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Normaliza el filename para evitar caracteres problemáticos en URLs/SO.
     */
    private function safeFilename(string $name): string
    {
        $name = preg_replace('/[^A-Za-z0-9._-]/', '_', $name) ?: 'file';
        return substr($name, -120);
    }
}
