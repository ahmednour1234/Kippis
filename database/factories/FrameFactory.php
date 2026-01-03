<?php

namespace Database\Factories;

use App\Core\Models\Frame;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Core\Models\Frame>
 */
class FrameFactory extends Factory
{
    protected $model = Frame::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name_json' => [
                'en' => $this->faker->words(2, true) . ' Frame',
                'ar' => $this->faker->words(2, true) . ' إطار',
            ],
            'thumbnail_path' => null,
            'overlay_path' => 'frames/overlays/test.png',
            'is_active' => true,
            'sort_order' => 0,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ];
    }
}
