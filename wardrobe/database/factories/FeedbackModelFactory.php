<?php

namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;

// Helper
use App\Helpers\Generator;
use App\Models\UserModel;

class FeedbackModelFactory extends Factory
{
    public function definition(): array
    {        
        $user_id = UserModel::getRandomWhoHaveClothes(0);

        return [
            'id' => Generator::getUUID(), 
            'feedback_rate' => mt_rand(1,5), 
            'feedback_body' => fake()->sentence(),
            'created_at' => Generator::getRandomDate(0), 
            'created_by' => $user_id, 
        ];
    }
}
