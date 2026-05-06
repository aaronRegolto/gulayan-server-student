<?php

// migration; seeder; test files > AI

# php artisan db:seed --class=PlantSeeder

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PlantModel;

class PlantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 50 random plants using the factory
        PlantModel::factory()->count(50)->create();

        // Create some specific plants with known data for testing
        PlantModel::create([
            'name' => 'Tomato',
            'variety' => 'Cherry',
            'notes' => 'First batch of cherry tomatoes for the season',
            'date_planted' => '2026-03-01',
            'seedling_count' => 100,
            'batch_name' => 'Batch-001',
        ]);

        PlantModel::create([
            'name' => 'Lettuce',
            'variety' => 'Romaine',
            'notes' => 'For salad production',
            'date_planted' => '2026-03-15',
            'seedling_count' => 200,
            'batch_name' => 'Batch-002',
        ]);

        PlantModel::create([
            'name' => 'Eggplant',
            'variety' => 'Purple',
            'notes' => 'Growing well in greenhouse area',
            'date_planted' => '2026-02-20',
            'seedling_count' => 75,
            'batch_name' => 'Batch-003',
        ]);

        PlantModel::create([
            'name' => 'Kangkong',
            'variety' => null,
            'notes' => 'Fast-growing leafy vegetable',
            'date_planted' => '2026-04-01',
            'seedling_count' => 150,
            'batch_name' => 'Batch-004',
        ]);
    }
}
