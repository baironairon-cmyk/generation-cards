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

        $response = $this->client->chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);

        $content = $response->choices[0]->message->content;

        $templates = json_decode($content, true);

        if (!$templates) {
            $templates = [
                [
                    'colors' => ['#ff0000', '#00ff00'],
                    'text_styles' => ['bold', 'italic'],
                    'theme' => 'Swimming pool'
                ],
                // Другие шаблоны...
            ];
        }

        return $templates;
    }
}
