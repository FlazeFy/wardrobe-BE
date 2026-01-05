<?php

namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;

// Helper
use App\Helpers\Generator;
// Model
use App\Models\UserModel;
use App\Models\OutfitModel;
use App\Models\OutfitRelModel;
use App\Models\ClothesModel;

class OutfitRelModelFactory extends Factory
{
    public function definition(): array
    {
        $user_id = UserModel::getRandomWithClothesOutfit(0);
        $outfit_id = OutfitModel::getRandom(0, $user_id);
        $clothes_attached = OutfitRelModel::getClothes($outfit_id, $user_id);

        // If outfit already have a clothes. Make sure to add new outfit relation with unique clothes type
        // So each outfit doesnt have multiple clothes with same clothes type
        if ($clothes_attached && count($clothes_attached) > 0){
            $clothes_type_used = $clothes_attached->pluck('clothes_type')->toArray();
            $clothes_id = ClothesModel::getRandom(0, $user_id, $clothes_type_used);
        } else {
            $clothes_id = ClothesModel::getRandom(0, $user_id);
        }

        return [
            'id' => Generator::getUUID(), 
            'outfit_id' => $outfit_id, 
            'clothes_id' => $clothes_id, 
            'created_at' => Generator::getRandomDate(0), 
            'created_by' => $user_id
        ];
    }
}
