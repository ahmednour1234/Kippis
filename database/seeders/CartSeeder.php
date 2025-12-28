<?php

namespace Database\Seeders;

use App\Core\Models\Cart;
use App\Core\Models\CartItem;
use App\Core\Models\Customer;
use App\Core\Models\Product;
use App\Core\Models\Store;
use App\Core\Models\PromoCode;
use Illuminate\Database\Seeder;

class CartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = Customer::all();
        $stores = Store::all();
        $products = Product::where('is_active', true)->get();
        $promoCodes = PromoCode::where('active', true)->get();

        if ($customers->isEmpty() || $stores->isEmpty() || $products->isEmpty()) {
            $this->command->warn('Skipping CartSeeder: Required data (customers, stores, products) not found. Please run CustomerSeeder, StoreSeeder, and ProductSeeder first.');
            return;
        }

        // Create active carts (not abandoned)
        $this->command->info('Creating active carts...');
        for ($i = 0; $i < 20; $i++) {
            $customer = $customers->random();
            $store = $stores->random();
            $storeProducts = $products->where('category_id', '!=', null)->take(10);

            $cart = Cart::create([
                'customer_id' => $customer->id,
                'store_id' => $store->id,
                'promo_code_id' => $promoCodes->isNotEmpty() && rand(1, 100) <= 20 ? $promoCodes->random()->id : null,
                'subtotal' => 0,
                'discount' => 0,
                'total' => 0,
            ]);

            // Add 1-5 items to cart
            $itemsCount = rand(1, 5);
            $selectedProducts = $storeProducts->random(min($itemsCount, $storeProducts->count()));

            foreach ($selectedProducts as $product) {
                $quantity = rand(1, 3);
                $price = $product->base_price;
                
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'modifiers_snapshot' => [],
                ]);
            }

            $cart->recalculate();
        }

        // Create abandoned carts
        $this->command->info('Creating abandoned carts...');
        for ($i = 0; $i < 15; $i++) {
            $customer = $customers->random();
            $store = $stores->random();
            $storeProducts = $products->where('category_id', '!=', null)->take(10);

            $cart = Cart::create([
                'customer_id' => $customer->id,
                'store_id' => $store->id,
                'abandoned_at' => now()->subDays(rand(1, 30)),
                'subtotal' => 0,
                'discount' => 0,
                'total' => 0,
            ]);

            // Add 1-4 items to cart
            $itemsCount = rand(1, 4);
            $selectedProducts = $storeProducts->random(min($itemsCount, $storeProducts->count()));

            foreach ($selectedProducts as $product) {
                $quantity = rand(1, 2);
                $price = $product->base_price;
                
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'modifiers_snapshot' => [],
                ]);
            }

            $cart->recalculate();
        }

        $this->command->info('Created ' . Cart::count() . ' carts with items.');
    }
}

