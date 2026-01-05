<?php

namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;

// Helper
use App\Helpers\Generator;
// Model
use App\Models\UserModel;
use App\Models\ClothesModel;
use App\Models\DictionaryModel;

class ScheduleModelFactory extends Factory
{
    public function definition(): array
    {
        $user_id = UserModel::getRandomWithClothesOutfit(0);
        $clothes = ClothesModel::getRandomWithFreeSchedule($user_id);
        $day_names = DictionaryModel::getDictionaryByType('day_name')->pluck('dictionary_name')->toArray();
        $day_found = $clothes->days ? array_map('trim', explode(',', $clothes->days)) : [];

        if (empty($day_found)) {
            // If clothes still not used, just take random day
            $day = $day_names[array_rand($day_names)];
        } else {
            // If clothes already used, pick random day that not used in the schedule
            $available_days = array_diff($day_names, $day_found);
            $day = !empty($available_days) ? $available_days[array_rand($available_days)] : null;
        }

        return [
            'id' => Generator::getUUID(),
            'clothes_id' => $clothes->id, 
            'day' => $day, 
            'is_remind' => mt_rand(0,1), 
            'schedule_note' => mt_rand(0, 1) === 1 ? fake()->words(mt_rand(4,10), true) : null, 
            'created_at' => Generator::getRandomDate(0), 
            'created_by' => $user_id
        ];
    }
}
