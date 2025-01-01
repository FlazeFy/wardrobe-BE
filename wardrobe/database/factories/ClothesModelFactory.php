<?php

namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;

// Helpers
use App\Helpers\Generator;

// Models
use App\Models\DictionaryModel;
use App\Models\UserModel;

class ClothesModelFactory extends Factory
{
    public function definition(): array
    {
        $ran = mt_rand(0, 1);
        $ran2 = mt_rand(0, 1);
        $price = round(mt_rand(5000, 5000000) / 5000) * 5000;

        return [
            'id' => Generator::getUUID(), 
            'clothes_name' => fake()->words(mt_rand(2,3), true), 
            'clothes_desc' => fake()->paragraph(), 
            'clothes_merk' => fake()->company(), 
            'clothes_size' => Generator::getRandomSeed('clothes_size'), 
            'clothes_gender' => Generator::getRandomSeed('clothes_gender'), 
            'clothes_made_from' => DictionaryModel::getRandom(0,'clothes_made_from'), 
            'clothes_color' => fake()->colorName(), 
            'clothes_category' => Generator::getRandomSeed('clothes_category'), 
            'clothes_type' => DictionaryModel::getRandom(0,'clothes_type'), 
            'clothes_price' => $price, 
            'clothes_buy_at' => Generator::getRandomDate(0), 
            'clothes_qty' => mt_rand(1, 3), 
            'is_faded' => $ran, 
            'has_washed' => $ran, 
            'has_ironed' => $ran2, 
            'is_favorite' => $ran2, 
            'is_scheduled' => 0, 
            'created_at' => Generator::getRandomDate(0), 
            'created_by' => UserModel::getRandom(0), 
            'updated_at' => Generator::getRandomDate($ran), 
            'deleted_at' => Generator::getRandomDate($ran2) 
        ];
    }
}
