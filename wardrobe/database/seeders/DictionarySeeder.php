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
            'clothes_made_from' => ['cotton','wool','silk','linen','polyester','denim','leather','nylon','rayon','synthetic','cloth'],
            'clothes_type' => ['hat', 'pants', 'shirt', 'jacket', 'shoes', 'socks', 'scarf', 'gloves', 'shorts', 'skirt', 'dress', 'blouse', 'sweater', 'hoodie', 'tie', 'belt', 
            'coat', 'underwear', 'swimsuit', 'vest', 't-shirt', 'jeans', 'leggings', 'boots', 'sandals', 'sneakers', 'raincoat', 'poncho', 'cardigan'],
            'used_context' => ['Worship','Shopping','Work','School','Campus','Sport','Party']
        ];
        $now = Carbon::now();

        foreach ($dictionaries as $type => $dt) {
            foreach ($dt as $name) {
                DictionaryModel::create([
                    'id' => Generator::getUUID(), 
                    'dictionary_type' => $type,
                    'dictionary_name' => $name,
                ]);
            }
        }
    }
}
