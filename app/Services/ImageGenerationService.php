<?php
namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use OpenAI;

class ImageGenerationService
{
    public function generateProductCards($bgRemovedPath, $templates): array
    {
        // Use OpenAI Images API (DALL·E / gpt-image-1) to generate product card images
        $client = OpenAI::client(env('OPENAI_API_KEY'));

        Storage::disk('public')->makeDirectory('generated');

        $generatedPaths = [];
        foreach ($templates as $i => $template) {
            $prompt = is_array($template)
                ? (json_encode($template))
                : (string) $template;

            $response = $client->images()->create([
                'model' => 'gpt-image-1',
                'prompt' => 'Product card design for e-commerce marketplace, include clean layout, price badge, and space for product image. Theme: ' . $prompt,
                'size' => '1024x1024',
                'n' => 1,
            ]);

            $url = $response->data[0]->url ?? null;
            if (!$url) {
                continue;
            }

            $httpResponse = Http::get($url);
            if (!$httpResponse->ok()) {
                continue;
            }

            $binary = $httpResponse->body();
            $filename = 'generated/product_card_' . $i . '_' . uniqid() . '.png';
            Storage::disk('public')->put($filename, $binary);
            $generatedPaths[] = $filename;
        }

        return $generatedPaths;
    }
}
