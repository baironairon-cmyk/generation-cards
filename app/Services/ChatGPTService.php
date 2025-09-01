<?php
namespace App\Services;

use OpenAI;

class ChatGPTService
{
    protected OpenAI\Client $client;

    public function __construct()
    {
        $this->client = OpenAI::client(env('OPENAI_API_KEY', 'fake-api-key'));
    }

    public function generateTemplates(string $productDescription): array
    {
        $prompt = "Generate 5 creative product card templates for marketplace. " .
            "Include color palettes, text styles, and themes based on: " . $productDescription .
            ". Provide output as JSON with keys: colors, text_styles, theme.";

        $response = $this->client->responses()->create([
            'model' => 'gpt-5',
            'input' => "$prompt"
        ]);

        $content = $response->outputText;

        $templates = json_decode($content, true);

        if (!$templates) {
            $templates = [
                [
                    'colors' => ['#ff0000', '#00ff00'],
                    'text_styles' => ['bold', 'italic'],
                    'theme' => 'Swimming pool'
                ],
                ['colors' => ['#222222', '#ffffff'], 'text_styles' => ['bold', 'caps'], 'theme' => 'Minimalist contrast'],
                ['colors' => ['#6d28d9', '#fef3c7'], 'text_styles' => ['serif', 'thin'], 'theme' => 'Premium luxury'],
                ['colors' => ['#059669', '#ecfeff'], 'text_styles' => ['rounded', 'medium'], 'theme' => 'Eco fresh'],
                ['colors' => ['#dc2626', '#fee2e2'], 'text_styles' => ['impact', 'shadow'], 'theme' => 'Sale highlight'],
            ];
        }

        return $templates;
    }
}
