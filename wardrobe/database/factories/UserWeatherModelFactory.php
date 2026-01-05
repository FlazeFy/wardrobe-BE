<?php

namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;

// Helper
use App\Helpers\Generator;
// Model
use App\Models\UserModel;
use App\Models\DictionaryModel;

class UserWeatherModelFactory extends Factory
{
    public function definition(): array
    {
        $user_id = UserModel::getRandomWhoHaveClothes(0);

        return [
            'id' => Generator::getUUID(), 
            'weather_temp' => mt_rand(-25.0, 45.0), 
            'weather_humid' => mt_rand(10,100), 
            'weather_city' => fake()->word(), 
            'weather_condition' => DictionaryModel::getRandom(0,'weather_condition'), 
            'weather_hit_from' => DictionaryModel::getRandom(0,'weather_hit_from'), 
            'created_at' => Generator::getRandomDate(0), 
            'created_by' => $user_id
        ];
    }
}
