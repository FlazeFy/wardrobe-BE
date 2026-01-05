<?php

namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;

// Helper
use App\Helpers\Generator;
// Model
use App\Models\UserModel;

class OutfitModelFactory extends Factory
{
    public function definition(): array
    {
        $ran = mt_rand(0, 1);
        $ran2 = mt_rand(0, 1);

        return [
            'id' => Generator::getUUID(), 
            'outfit_name' => fake()->words(mt_rand(2,3), true), 
            'outfit_note' => mt_rand(0, 1) === 1 ? fake()->words(mt_rand(4,10), true) : null, 
            'is_favorite' => mt_rand(0, 1), 
            'is_auto' => mt_rand(0, 1), 
            'created_at' => Generator::getRandomDate(0), 
            'created_by' => UserModel::getRandom(0), 
            'updated_at' => Generator::getRandomDate($ran), 
        ];
    }
}
