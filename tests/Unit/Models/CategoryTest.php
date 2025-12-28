<?php

namespace Tests\Unit\Models;

use App\Core\Models\Category;
use App\Core\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_localized_name(): void
    {
        $category = Category::factory()->create([
            'name_json' => ['en' => 'Hot Drinks', 'ar' => 'المشروبات الساخنة'],
        ]);

        $this->assertEquals('Hot Drinks', $category->getName('en'));
        $this->assertEquals('المشروبات الساخنة', $category->getName('ar'));
    }

    public function test_has_products_relationship(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(3)->create(['category_id' => $category->id]);

        $this->assertCount(3, $category->products);
    }

    public function test_active_scope(): void
    {
        Category::factory()->create(['is_active' => true]);
        Category::factory()->create(['is_active' => false]);

        $this->assertCount(1, Category::active()->get());
    }

    public function test_local_scope(): void
    {
        Category::factory()->create(['external_source' => 'local']);
        Category::factory()->create(['external_source' => 'foodics']);

        $this->assertCount(1, Category::local()->get());
    }
}

