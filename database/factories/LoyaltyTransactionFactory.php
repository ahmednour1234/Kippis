<?php

namespace Database\Factories;

use App\Core\Models\LoyaltyTransaction;
use App\Core\Models\LoyaltyWallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Core\Models\LoyaltyTransaction>
 */
class LoyaltyTransactionFactory extends Factory
{
    protected $model = LoyaltyTransaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'wallet_id' => LoyaltyWallet::factory(),
            'type' => $this->faker->randomElement(['earned', 'redeemed', 'adjusted']),
            'points' => $this->faker->numberBetween(10, 500),
            'description' => $this->faker->sentence(),
            'reference_type' => $this->faker->randomElement(['order', 'qr_receipt', 'system', null]),
            'reference_id' => $this->faker->numberBetween(1, 1000),
        ];
    }
}
