<?php

namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;

// Helper
use App\Helpers\Generator;
// Model
use App\Models\UserModel;

class QuestionModelFactory extends Factory
{
    public function definition(): array
    {        
        $rand = mt_rand(0,1);

        return [
            'id' => Generator::getUUID(), 
            'question' => fake()->sentence(), 
            'answer' => $rand == 1 ? fake()->paragraph() : null, 
            'is_show' => mt_rand(0,1), 
            'created_at' => Generator::getRandomDate(0), 
        ];
    }
}
