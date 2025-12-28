<?php

namespace Tests\Feature\Api\V1;

use App\Core\Models\Category;
use App\Core\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_products(): void
    {
        Product::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'name', 'base_price', 'image'],
                ],
            ]);
    }

    public function test_can_get_single_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $product->id);
    }

    public function test_returns_404_for_nonexistent_product(): void
    {
        $response = $this->getJson('/api/v1/products/999');

        $response->assertStatus(404);
    }

    public function test_can_filter_by_category(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id]);
        Product::factory()->create();

        $response = $this->getJson("/api/v1/products?category_id={$category->id}");

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_can_search_products(): void
    {
        Product::factory()->create(['name_json' => ['en' => 'Coffee Latte', 'ar' => 'لاتيه']]);
        Product::factory()->create(['name_json' => ['en' => 'Tea', 'ar' => 'شاي']]);

        $response = $this->getJson('/api/v1/products?q=Coffee');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }
}

