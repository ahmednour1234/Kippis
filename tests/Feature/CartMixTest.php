<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Core\Models\Product;
use App\Core\Models\Modifier;
use App\Core\Models\Cart;

class CartMixTest extends TestCase
{
    public function test_add_mix_to_cart_saves_snapshot_and_price(): void
    {
        $product = Product::factory()->create(['base_price' => 12.00]);
        $modifier = Modifier::factory()->create(['price' => 1.50, 'max_level' => 5]);
        $store = \App\Core\Models\Store::factory()->create();
        $customer = \App\Core\Models\Customer::factory()->create();

        // init cart
        $this->actingAs($customer, 'api')
            ->postJson('/api/v1/cart/init', ['store_id' => $store->id]);

        $configuration = ['base_id' => $product->id, 'modifiers' => [['id' => $modifier->id, 'level' => 1]]];

        $response = $this->actingAs($customer, 'api')
            ->postJson('/api/v1/cart/items', ['item_type' => 'mix', 'quantity' => 1, 'configuration' => $configuration]);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);

        $cart = Cart::where('customer_id', $customer->id)->latest()->first();
        $this->assertNotNull($cart);
        $this->assertGreaterThan(0, $cart->items()->count());

        $item = $cart->items()->first();
        $this->assertEquals('mix', $item->item_type);
        $this->assertNotNull($item->configuration);
        $this->assertEquals(13.50, (float)$item->price);
    }
}
