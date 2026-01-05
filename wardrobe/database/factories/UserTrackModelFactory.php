<?php

namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;

// Helper
use App\Helpers\Generator;
// Model
use App\Models\UserModel;
use App\Models\DictionaryModel;

class UserTrackModelFactory extends Factory
{
    public function definition(): array
    {
        $user_id = UserModel::getRandomWhoHaveClothes(0);
        $lat = strval(-90 + lcg_value() * 180);
        $long = strval(-180 + lcg_value() * 360);

        return [
            'id' => Generator::getUUID(), 
            'track_lat' => $lat, 
            'track_long' => $long, 
            'track_source' => DictionaryModel::getRandom(0,'track_source'), 
            'created_at' => Generator::getRandomDate(0), 
            'created_by' => $user_id
        ];
    }
}
