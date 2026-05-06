<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\PlantModel;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PlantModel>
 */
class PlantModelFactory extends Factory
{
    protected $model = PlantModel::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $vegetables = [
            'Tomato', 'Cabbage', 'Lettuce', 'Eggplant', 'Okra',
            'Kangkong', 'Pechay', 'Sitaw', 'Ampalaya', 'Talong',
            'Carrots', 'Radish', 'Bell Pepper', 'Chili', 'Cucumber'
        ];

        $varieties = [
            'Tomato' => ['Cherry', 'Beefsteak', 'Roma', 'Heirloom'],
            'Cabbage' => ['Green', 'Red', 'Savoy', 'Napa'],
            'Lettuce' => ['Romaine', 'Iceberg', 'Butterhead', 'Loose-leaf'],
            'Eggplant' => ['Purple', 'White', 'Japanese', 'Chinese'],
            'Okra' => ['Clemson Spineless', 'Annie Oakley', 'Burgundy'],
            'Bell Pepper' => ['Red', 'Green', 'Yellow', 'Orange'],
            'Chili' => ['Siling Labuyo', 'Jalapeno', 'Cayenne'],
        ];

        $plantName = $this->faker->randomElement($vegetables);
        $variety = isset($varieties[$plantName]) 
            ? $this->faker->randomElement($varieties[$plantName])
            : null;

        return [
            'name' => $plantName,
            'variety' => $variety,
            'notes' => $this->faker->optional(0.6)->sentence(),
            'date_planted' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'seedling_count' => $this->faker->numberBetween(10, 500),
            'batch_name' => $this->faker->optional(0.5)->bothify('Batch-####'),
        ];
    }
}
