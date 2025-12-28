<?php

namespace Database\Factories;

use App\Core\Models\Cart;
use App\Core\Models\Customer;
use App\Core\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Core\Models\Cart>
 */
class CartFactory extends Factory
{
    protected $model = Cart::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'store_id' => Store::factory(),
            'subtotal' => 0,
            'discount' => 0,
            'total' => 0,
        ];
    }
}
