<?php
namespace App\Services;

use OpenAI;

class ProductCardGenerationService
{
	public function generate(array $input): array
	{
		$client = OpenAI::client(env('OPENAI_API_KEY'));

		$prompt = 'Generate normalized product card data suitable for marketplaces (Wildberries, Ozon, Yandex.Market). '
			. 'Return strict JSON with keys: name, description, brand, category, price, sku, '
			. 'specs (object of key->value), attributes (object), marketplace (object with keys wb, ozon, yandexMarket each containing their specific fields). '
			. 'Base it on: ' . json_encode($input, JSON_UNESCAPED_UNICODE);

		$response = $client->chat()->create([
			'model' => 'gpt-4o-mini',
			'messages' => [
				['role' => 'system', 'content' => 'You are a data normalizer for e-commerce marketplaces. Output only JSON.'],
				['role' => 'user', 'content' => $prompt],
			],
			'temperature' => 0.3,
		]);

		$content = $response->choices[0]->message->content ?? '';
		$json = json_decode($content, true);

		if (!$json) {
			$json = [
				'name' => $input['name'] ?? 'Product',
				'description' => $input['description'] ?? null,
				'brand' => $input['brand'] ?? null,
				'category' => $input['category'] ?? null,
				'price' => $input['price'] ?? null,
				'sku' => $input['sku'] ?? null,
				'specs' => [
					'Material' => 'Plastic',
					'Color' => 'Black',
				],
				'attributes' => [
					'country_of_origin' => 'CN',
				],
				'marketplace' => [
					'wb' => [
						'object' => 'General',
						'brand' => $input['brand'] ?? 'No brand',
						'name' => $input['name'] ?? 'Product',
					],
					'ozon' => [
						'name' => $input['name'] ?? 'Product',
						'price' => $input['price'] ?? 0,
						'barcode' => null,
					],
					'yandexMarket' => [
						'name' => $input['name'] ?? 'Product',
						'vendor' => $input['brand'] ?? null,
						'category' => $input['category'] ?? null,
					],
				],
			];
		}

		return $json;
	}
}