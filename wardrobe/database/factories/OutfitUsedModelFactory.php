<?php

namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;

// Helper
use App\Helpers\Generator;
// Model
use App\Models\UserModel;
use App\Models\OutfitModel;

class OutfitUsedModelFactory extends Factory
{
    public function definition(): array
    {
        $user_id = UserModel::getRandomWithClothesOutfit(0);

        return [
            'id' => Generator::getUUID(), 
            'outfit_id' => OutfitModel::getRandom(0, $user_id), 
            'created_at' => Generator::getRandomDate(0), 
            'created_by' => $user_id, 
        ];
    }
}
