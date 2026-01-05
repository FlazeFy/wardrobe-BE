<?php

namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;

// Helpers
use App\Helpers\Generator;
// Model
use App\Models\DictionaryModel;
use App\Models\UserModel;
use App\Models\ClothesModel;

class WashModelFactory extends Factory
{
    public function definition(): array
    {
        $user_id = UserModel::getRandomWhoHaveClothes(0);
        $ran = mt_rand(0, 1);

        return [
            'id' => Generator::getUUID(), 
            'wash_note' => fake()->words(mt_rand(2,3), true), 
            'clothes_id' => ClothesModel::getRandom(0,$user_id),
            'wash_type' => Generator::getRandomSeed('wash_type'), 
            'wash_checkpoint' => '[{"id":"1","checkpoint_name":"Rendam","is_finished":false},{"id":"2","checkpoint_name":"Rendam 2","is_finished":true}]', 
            'created_at' => Generator::getRandomDate(0), 
            'created_by' => $user_id, 
            'finished_at' => Generator::getRandomDate($ran)
        ];
    }
}
