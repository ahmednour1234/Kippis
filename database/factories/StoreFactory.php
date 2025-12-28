<?php

namespace Database\Factories;

use App\Core\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Core\Models\Store>
 */
class StoreFactory extends Factory
{
    protected $model = Store::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'name_localized' => [
                'en' => $this->faker->company(),
                'ar' => $this->faker->company(),
            ],
            'address' => $this->faker->address(),
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'open_time' => '09:00',
            'close_time' => '22:00',
            'is_active' => true,
            'receive_online_orders' => true,
        ];
    }
}
