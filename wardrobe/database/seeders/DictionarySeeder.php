<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

use App\Models\DictionaryModel;
use App\Helpers\Generator;

use Illuminate\Support\Facades\DB;

class DictionarySeeder extends Seeder
{
    public function run(): void
    {
        // Delete All 
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DictionaryModel::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $dictionaries = [
            'clothes_category' => ['upper_body','bottom_body','head','foot','hand','full_body'],
            'clothes_gender' => ['male','female','unisex'],
            'clothes_made_from' => ['cotton','wool','silk','linen','polyester','denim','leather','nylon','rayon','synthetic','cloth'],
            'clothes_size' => ['S','M','L','XL','XXL','XXXL'],
            'clothes_type' => [
                'hat', 'pants', 'shirt', 'jacket', 'shoes', 'socks', 'scarf', 'gloves', 'shorts', 'skirt', 'dress', 'blouse', 'sweater', 'hoodie', 'tie', 'belt', 
                'coat', 'underwear', 'swimsuit', 'vest', 't-shirt', 'jeans', 'leggings', 'boots', 'sandals', 'sneakers', 'raincoat', 'poncho', 'cardigan'
            ],
            'day_name' => ['sun','mon','tue','wed','thu','fri','sat'],
            'track_source' => ['web','mobile','telegram bot','line bot'],
            'used_context' => ['worship','shopping','Work','school','campus','sport','party'],
            'wash_type' => ['laundry','self-wash'],
            'weather_hit_from' => ["task schedule", "manual"],
            'weather_condition' => ["thunderstorm", "drizzle", "rain", "snow", "mist", "smoke", "haze", "dust", "fog", "sand", "ash", "squall", "tornado", "clear", "clouds"] 
        ];
        $now = Carbon::now();

        foreach ($dictionaries as $type => $dt) {
            foreach ($dt as $name) {
                DictionaryModel::createDictionary([
                    'dictionary_type' => $type,
                    'dictionary_name' => $name,
                ]);
            }
        }
    }
}
