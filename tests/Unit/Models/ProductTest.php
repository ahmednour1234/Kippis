<?php

namespace Tests\Unit\Models;

use App\Core\Models\Category;
use App\Core\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_belongs_to_category(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->assertEquals($category->id, $product->category->id);
    }

    public function test_can_get_localized_name(): void
    {
        $product = Product::factory()->create([
            'name_json' => ['en' => 'Coffee', 'ar' => 'قهوة'],
        ]);

        $this->assertEquals('Coffee', $product->getName('en'));
        $this->assertEquals('قهوة', $product->getName('ar'));
    }

    public function test_active_scope(): void
    {
        Product::factory()->create(['is_active' => true]);
        Product::factory()->create(['is_active' => false]);

        $this->assertCount(1, Product::active()->get());
    }
}

