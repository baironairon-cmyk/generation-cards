<?php
namespace App\Services;

use Illuminate\Support\Facades\Storage;

class BackgroundRemovalService
{
    public function removeBackground($imagePath): string
    {
        // In real use, call Remove.bg API or U2Net
        // For now, just copy original to bg_removed

        $bgRemovedPath = 'bg_removed/' . basename($imagePath);

        // Simulate background removal by copying file
        Storage::disk('public')->makeDirectory('bg_removed');
        Storage::disk('public')->copy($imagePath, $bgRemovedPath);

        return $bgRemovedPath;
    }
}
