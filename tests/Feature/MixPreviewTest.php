<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Core\Models\Product;
use App\Core\Models\Modifier;

class MixPreviewTest extends TestCase
{
    public function test_mix_preview_returns_total_and_breakdown(): void
    {
        $product = Product::factory()->create(['base_price' => 10.00]);
        $modifier = Modifier::factory()->create(['price' => 2.50, 'max_level' => 5]);

        $payload = [
            'configuration' => [
                'base_id' => $product->id,
                'modifiers' => [ ['id' => $modifier->id, 'level' => 2] ],
            ],
        ];

        $response = $this->postJson('/api/v1/mix/preview', $payload);

        $response->assertStatus(200);
        $response->assertJsonPath('data.total', 15.00);
        $this->assertArrayHasKey('breakdown', $response->json('data'));
    }
}
