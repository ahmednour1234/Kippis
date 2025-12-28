<?php

namespace Database\Factories;

use App\Core\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Core\Models\Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name_json' => [
                'en' => $this->faker->words(2, true),
                'ar' => $this->faker->words(2, true),
            ],
            'description_json' => [
                'en' => $this->faker->sentence(),
                'ar' => $this->faker->sentence(),
            ],
            'is_active' => true,
            'external_source' => 'local',
        ];
    }

    public function foodics(): static
    {
        return $this->state(fn (array $attributes) => [
            'external_source' => 'foodics',
            'foodics_id' => 'FD_' . $this->faker->unique()->numerify('#####'),
        ]);
    }
}
