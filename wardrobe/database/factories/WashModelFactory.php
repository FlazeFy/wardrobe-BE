<?php

namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;

// Helpers
use App\Helpers\Generator;
// Model
use App\Models\UserModel;
use App\Models\ClothesModel;

class WashModelFactory extends Factory
{
    public function definition(): array
    {
        $user_id = UserModel::getRandomWhoHaveClothes(0);
        $ran = mt_rand(0, 1);
        // Step 1: Soak
        $isFinished1 = (bool) mt_rand(0, 1);
        // Step 2: Wash
        $isFinished2 = $isFinished1 ? (bool) mt_rand(0, 1) : false;
        // Step 3: Dry
        $isFinished3 = ($isFinished2) ? (bool) mt_rand(0, 1) : false;

        return [
            'id' => Generator::getUUID(), 
            'wash_note' => fake()->words(mt_rand(2,3), true), 
            'clothes_id' => ClothesModel::getRandom(0,$user_id),
            'wash_type' => Generator::getRandomSeed('wash_type'), 
            'wash_checkpoint' => [
                [
                    'id' => 1, 
                    'checkpoint_name' => 'Soak',
                    'is_finished' => $isFinished1,
                ],
                [
                    'id' => 2,
                    'checkpoint_name' => 'Wash',
                    'is_finished' => $isFinished2,
                ],
                [
                    'id' => 3,
                    'checkpoint_name' => 'Dry',
                    'is_finished' => $isFinished3,
                ],
            ], 
            'created_at' => Generator::getRandomDate(0), 
            'created_by' => $user_id, 
            'finished_at' => $isFinished1 && $isFinished2 && $isFinished3 ? Generator::getRandomDate($ran) : null
        ];
    }
}