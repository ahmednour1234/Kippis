<?php

namespace Database\Factories;

use App\Core\Models\Customer;
use App\Core\Models\LoyaltyWallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Core\Models\LoyaltyWallet>
 */
class LoyaltyWalletFactory extends Factory
{
    protected $model = LoyaltyWallet::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'points' => 0,
        ];
    }
}
