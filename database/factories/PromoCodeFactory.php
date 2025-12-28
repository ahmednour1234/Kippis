<?php

namespace Database\Factories;

use App\Core\Models\PromoCode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Core\Models\PromoCode>
 */
class PromoCodeFactory extends Factory
{
    protected $model = PromoCode::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->bothify('???###')),
            'discount_type' => $this->faker->randomElement(['percentage', 'fixed']),
            'discount_value' => $this->faker->randomFloat(2, 5, 50),
            'valid_from' => now(),
            'valid_to' => now()->addMonths(3),
            'usage_limit' => $this->faker->numberBetween(100, 1000),
            'usage_per_user_limit' => $this->faker->numberBetween(1, 5),
            'minimum_order_amount' => $this->faker->randomFloat(2, 50, 200),
            'active' => true,
        ];
    }
}
