<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class MonsterImageComposerService
{
    private string $publicPath;
    private int $targetWidth = 1200;
    private int $targetHeight = 600; // Easy to adjust as requested

    public function __construct(string $projectDir)
    {
        $this->publicPath = $projectDir . '/public';
    }

    /**
     * Compose a monster image with the divisor border
     * 
     * @param string $monsterImagePath Path to monster image (can be URL or local path)
     * @param string|null $outputPath Optional output path, if null returns image data
     * @return string Path to composed image or base64 data
     */
    public function composeImage(string $monsterImagePath, ?string $outputPath = null): string
    {
        // Load divisor image
        $divisorPath = $this->publicPath . '/media/layout/divisor01.png';
        if (!file_exists($divisorPath)) {
            throw new FileNotFoundException("Divisor image not found at: {$divisorPath}");
        }

        $divisor = imagecreatefrompng($divisorPath);
        if (!$divisor) {
            throw new \RuntimeException("Failed to load divisor image");
        }

        // Load monster image (handle both URL and local path)
        $monsterImage = $this->loadImage($monsterImagePath);
        if (!$monsterImage) {
            // Fallback: create blank image
            $monsterImage = imagecreatetruecolor($this->targetWidth, $this->targetHeight);
            $white = imagecolorallocate($monsterImage, 255, 255, 255);
            imagefill($monsterImage, 0, 0, $white);
        }

        // Get dimensions
        $monsterWidth = imagesx($monsterImage);
        $monsterHeight = imagesy($monsterImage);

        // Calculate scale ratios for both dimensions
        $widthRatio = $this->targetWidth / $monsterWidth;
        $heightRatio = $this->targetHeight / $monsterHeight;

        // Use the LARGER ratio to ensure the image covers the entire canvas (Cover strategy)
        $scale = max($widthRatio, $heightRatio);

        $scaledWidth = (int) round($monsterWidth * $scale);
        $scaledHeight = (int) round($monsterHeight * $scale);

        // Create the final composite image
        $composite = imagecreatetruecolor($this->targetWidth, $this->targetHeight);

        // Enable alpha logic to preserve transparency (as requested)
        imagealphablending($composite, false);
        imagesavealpha($composite, true);

        // Calculate destination alignment (centering the *scaled* image on the target canvas)
        // The offset will effectively be negative or zero relative to the target top-left.
        $dstX = (int) round(($this->targetWidth - $scaledWidth) / 2);
        $dstY = (int) round(($this->targetHeight - $scaledHeight) / 2);

        // Resize and place monster image on composite
        // imagecopyresampled(dst_im, src_im, dst_x, dst_y, src_x, src_y, dst_w, dst_h, src_w, src_h)
        imagecopyresampled(
            $composite,
            $monsterImage,
            $dstX,
            $dstY,
            0,
            0,
            $scaledWidth,
            $scaledHeight,
            $monsterWidth,
            $monsterHeight
        );

        // Get divisor dimensions and scale it to match target width
        $divisorWidth = imagesx($divisor);
        $divisorHeight = imagesy($divisor);
        $scaledDivisorWidth = $this->targetWidth;
        $scaledDivisorHeight = (int) ($divisorHeight * ($this->targetWidth / $divisorWidth));

        // Place divisor at the bottom of the composite image
        // Move it 2% of its height lower to eliminate visible border
        $divisorY = $this->targetHeight - $scaledDivisorHeight + 3;

        // Note: For divisor, we might want to blend it if it has transparency? 
        // But user provided logic sets alphablending false globally. Let's stick to it unless it breaks divisor.
        // Actually, for adding a divisor on top, we probably want normal blending?
        // But if we already did 'imagealphablending(false)', subsequent draws replace pixels.
        // Let's re-enable blending for the divisor overlay if needed.
        imagealphablending($composite, true); // Re-enable for overlaying divisor

        imagecopyresampled(
            $composite,
            $divisor,
            0,
            $divisorY,
            0,
            0,
            $scaledDivisorWidth,
            $scaledDivisorHeight,
            $divisorWidth,
            $divisorHeight
        );

        // Output or save
        if ($outputPath) {
            // Ensure directory exists
            $dir = dirname($outputPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            imagepng($composite, $outputPath);
            $result = $outputPath;
        } else {
            // Return as base64 data URL
            ob_start();
            imagepng($composite);
            $imageData = ob_get_clean();
            $result = 'data:image/png;base64,' . base64_encode($imageData);
        }

        // Clean up
        imagedestroy($monsterImage);
        imagedestroy($divisor);
        imagedestroy($composite);

        return $result;
    }

    /**
     * Load image from path or URL
     */
    private function loadImage(string $path): \GdImage|false
    {
        // Check if it's a URL
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            // Download image
            $imageData = @file_get_contents($path);
            if (!$imageData) {
                return false;
            }
            return imagecreatefromstring($imageData);
        }

        // Handle local path
        if (str_starts_with($path, '/')) {
            $fullPath = $this->publicPath . $path;
        } else {
            $fullPath = $path;
        }

        if (!file_exists($fullPath)) {
            return false;
        }

        // Detect image type and load accordingly
        $imageInfo = @getimagesize($fullPath);
        if (!$imageInfo) {
            return false;
        }

        return match ($imageInfo[2]) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($fullPath),
            IMAGETYPE_PNG => imagecreatefrompng($fullPath),
            IMAGETYPE_GIF => imagecreatefromgif($fullPath),
            IMAGETYPE_WEBP => imagecreatefromwebp($fullPath),
            default => imagecreatefromstring(file_get_contents($fullPath)),
        };
    }

    /**
     * Set custom dimensions (easy adjustment as requested)
     */
    public function setDimensions(int $width, int $height): void
    {
        $this->targetWidth = $width;
        $this->targetHeight = $height;
    }
    /**
     * Get debug data for image composition
     */
    public function getCompositionDebugData(string $monsterImagePath): array
    {
        $monsterImage = $this->loadImage($monsterImagePath);
        if (!$monsterImage) {
            return ['error' => 'Could not load image'];
        }

        $monsterWidth = imagesx($monsterImage);
        $monsterHeight = imagesy($monsterImage);

        // Calculate scale ratios
        $widthRatio = $this->targetWidth / $monsterWidth;
        $heightRatio = $this->targetHeight / $monsterHeight;

        // Use the LARGER ratio (Cover strategy)
        $scale = max($widthRatio, $heightRatio);

        $scaledWidth = (int) round($monsterWidth * $scale);
        $scaledHeight = (int) round($monsterHeight * $scale);

        $dstX = (int) round(($this->targetWidth - $scaledWidth) / 2);
        $dstY = (int) round(($this->targetHeight - $scaledHeight) / 2);

        imagedestroy($monsterImage);

        return [
            'original_width' => $monsterWidth,
            'original_height' => $monsterHeight,
            'target_width' => $this->targetWidth,
            'target_height' => $this->targetHeight,
            'width_ratio' => $widthRatio,
            'height_ratio' => $heightRatio,
            'scale_used' => $scale,
            'scaled_width' => $scaledWidth,
            'scaled_height' => $scaledHeight,
            'crop_x_offset' => $dstX,
            'crop_y_offset' => $dstY,
        ];
    }
}
