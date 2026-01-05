<?php

namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;

// Helper
use App\Helpers\Generator;
// Model
use App\Models\DictionaryModel;
use App\Models\UserModel;
use App\Models\ClothesModel;

class ClothesUsedModelFactory extends Factory
{
    public function definition(): array
    {
        $user_id = UserModel::getRandomWhoHaveClothes(0);

        return [
            'id' => Generator::getUUID(), 
            'clothes_id' => ClothesModel::getRandom(0,$user_id),
            'clothes_note' => fake()->words(mt_rand(2,3), true),  
            'used_context' => DictionaryModel::getRandom(0,'used_context'), 
            'created_at' => Generator::getRandomDate(0), 
            'created_by' => $user_id, 
        ];
    }
}
