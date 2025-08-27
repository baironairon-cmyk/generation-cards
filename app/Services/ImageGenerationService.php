<?php
namespace App\Services;

use Illuminate\Support\Facades\Storage;

class ImageGenerationService
{
    public function generateProductCards($bgRemovedPath, $templates): array
    {
        // Normally: call Midjourney or DALL-E to generate images based on template+photo

        // For demo, copy bgRemovedPath 5 times to simulate 5 images
        $generatedPaths = [];
        foreach ($templates as $i => $template) {
            $path = 'generated/product_card_' . $i . '.png';
            Storage::copy($bgRemovedPath, $path);
            $generatedPaths[] = $path;
        }

        return $generatedPaths;
    }
}
