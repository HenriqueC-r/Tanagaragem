<?php

namespace App\Services;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class ImageProcessor
{
    private const MAX_SIDE = 1600;
    private const MAX_PIXELS = 50_000_000;
    private const QUALITY = 82;

    /**
     * Lê a imagem, corrige a rotação, reduz e devolve um JPEG NOVO.
     * Como o arquivo é recriado do zero, o EXIF (incluindo GPS) não vai junto.
     */
    public function toJpeg(UploadedFile $file): string
    {
        $path = $file->getRealPath();
        $size = @getimagesize($path);

        if ($size === false || ($size[0] * $size[1]) > self::MAX_PIXELS) {
            $this->fail();
        }

        $source = @imagecreatefromstring((string) file_get_contents($path));

        if ($source === false) {
            $this->fail();
        }

        $source = $this->applyOrientation($source, $path, $file->getMimeType());

        $width = imagesx($source);
        $height = imagesy($source);
        $scale = min(1, self::MAX_SIDE / max($width, $height));
        $newWidth = max(1, (int) round($width * $scale));
        $newHeight = max(1, (int) round($height * $scale));

        // JPEG não tem transparência: desenha sobre fundo branco.
        $canvas = imagecreatetruecolor($newWidth, $newHeight);
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imageinterlace($canvas, true);

        ob_start();
        imagejpeg($canvas, null, self::QUALITY);

        return (string) ob_get_clean();
    }

    private function applyOrientation(GdImage $image, string $path, ?string $mime): GdImage
    {
        if ($mime !== 'image/jpeg' || ! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($path);

        $angle = match ((int) ($exif['Orientation'] ?? 1)) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        if ($angle === 0) {
            return $image;
        }

        $rotated = imagerotate($image, $angle, 0);

        return $rotated ?: $image;
    }

    private function fail(): never
    {
        throw ValidationException::withMessages([
            'photos' => 'Uma das imagens não pôde ser lida. Envie arquivos JPG, PNG ou WebP válidos.',
        ]);
    }
}
