<?php

namespace Tests\Unit\Repositories;

use App\Core\Models\Category;
use App\Core\Models\Product;
use App\Core\Repositories\ProductRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ProductRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ProductRepository();
    }

    public function test_can_get_paginated_products(): void
    {
        Product::factory()->count(15)->create();

        $result = $this->repository->getPaginated([], 10);

        $this->assertCount(10, $result->items());
        $this->assertEquals(15, $result->total());
    }

    public function test_can_filter_by_category(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id]);
        Product::factory()->create();

        $result = $this->repository->getPaginated(['category_id' => $category->id], 10);

        $this->assertEquals(1, $result->total());
    }

    public function test_can_search_products(): void
    {
        Product::factory()->create(['name_json' => ['en' => 'Coffee', 'ar' => 'قهوة']]);
        Product::factory()->create(['name_json' => ['en' => 'Tea', 'ar' => 'شاي']]);

        $result = $this->repository->getPaginated(['q' => 'Coffee'], 10);

        $this->assertEquals(1, $result->total());
    }

    public function test_can_find_by_id(): void
    {
        $product = Product::factory()->create();

        $found = $this->repository->findById($product->id);

        $this->assertNotNull($found);
        $this->assertEquals($product->id, $found->id);
    }
}

