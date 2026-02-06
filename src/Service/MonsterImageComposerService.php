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
        
        // Use the LARGER ratio to ensure the image covers the entire canvas
        // (like background-size: cover - image will be larger than or equal to canvas)
        $scale = max($widthRatio, $heightRatio);
        
        $scaledWidth = (int)($monsterWidth * $scale);
        $scaledHeight = (int)($monsterHeight * $scale);
        
        // Create the final composite image
        $composite = imagecreatetruecolor($this->targetWidth, $this->targetHeight);
        $white = imagecolorallocate($composite, 255, 255, 255);
        imagefill($composite, 0, 0, $white);
        
        // Enable alpha blending
        imagealphablending($composite, true);
        imagesavealpha($composite, true);
        
        // Calculate position to center the monster image
        $monsterX = (int)(($this->targetWidth - $scaledWidth) / 2);
        $monsterY = 0; // Align to top as requested
        
        // Resize and place monster image on composite (aligned to top)
        imagecopyresampled(
            $composite,
            $monsterImage,
            $monsterX,
            $monsterY,
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
        $scaledDivisorHeight = (int)($divisorHeight * ($this->targetWidth / $divisorWidth));
        
        // Place divisor at the bottom of the composite image
        // Move it 2% of its height lower to eliminate visible border
        $divisorY = $this->targetHeight - $scaledDivisorHeight+3;
        
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
}
