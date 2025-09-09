<?php

namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;

// Helpers
use App\Helpers\Generator;
use App\Models\UserModel;

class UserTrackModelFactory extends Factory
{
    public function definition(): array
    {
        $track_source = ["Web", "Mobile", "Telegram Bot", "Line Bot"];
        $user_id = UserModel::getRandomWhoHaveClothes(0);
        $lat = strval(-90 + lcg_value() * 180);
        $long = strval(-180 + lcg_value() * 360);

        return [
            'id' => Generator::getUUID(), 
            'track_lat' => $lat, 
            'track_long' => $long, 
            'track_source' => $track_source[mt_rand(0,count($track_source)-1)], 
            'created_at' => Generator::getRandomDate(0), 
            'created_by' => $user_id
        ];
    }
}
