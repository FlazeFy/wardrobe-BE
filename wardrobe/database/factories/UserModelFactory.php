<?php

namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;

// Helpers
use App\Helpers\Generator;

class UserModelFactory extends Factory
{
    public function definition(): array
    {
        $ran = mt_rand(0, 1);

        return [
            'id' => Generator::getUUID(), 
            'username' => fake()->username(), 
            'password' => fake()->password(), 
            'email' => fake()->unique()->freeEmail(), 
            'telegram_user_id' => null,
            'telegram_is_valid' => 0,
            'timezone' => Generator::getRandomTimezone(), 
            'created_at' => Generator::getRandomDate(0), 
            'updated_at' => Generator::getRandomDate($ran)
        ];
    }
}
