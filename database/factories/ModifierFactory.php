<?php

namespace Database\Factories;

use App\Core\Models\Modifier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Core\Models\Modifier>
 */
class ModifierFactory extends Factory
{
    protected $model = Modifier::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(['sweetness', 'fizz', 'caffeine', 'extra']);

        return [
            'type' => $type,
            'name_json' => [
                'en' => $this->faker->word(),
                'ar' => $this->faker->word(),
            ],
            'max_level' => in_array($type, ['sweetness', 'fizz', 'caffeine']) ? $this->faker->numberBetween(3, 10) : null,
            'price' => $type === 'extra' ? $this->faker->randomFloat(2, 2, 10) : 0,
            'is_active' => true,
        ];
    }
}
