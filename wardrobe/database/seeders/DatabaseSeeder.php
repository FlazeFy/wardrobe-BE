<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;

// Models
use App\Models\AdminModel;
use App\Models\UserModel;
use App\Models\ClothesModel;
use App\Models\ClothesUsedModel;
use App\Models\WashModel;
use App\Models\FeedbackModel;
use App\Models\HistoryModel;
use App\Models\QuestionModel;
use App\Models\OutfitModel;
use App\Models\OutfitUsedModel;
use App\Models\UserTrackModel;
use App\Models\UserWeatherModel;

use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Delete All 
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        FeedbackModel::truncate();
        QuestionModel::truncate();
        OutfitModel::truncate();
        OutfitUsedModel::truncate();
        ClothesUsedModel::truncate();
        WashModel::query()->delete();
        ClothesModel::truncate();
        HistoryModel::truncate();
        UserWeatherModel::truncate();
        UserTrackModel::truncate();
        UserModel::truncate();
        AdminModel::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Factory
        AdminModel::factory(10)->create();
        UserModel::factory(20)->create();
        ClothesModel::factory(300)->create();
        OutfitModel::factory(50)->create();
        OutfitUsedModel::factory(150)->create();
        WashModel::factory(600)->create();
        ClothesUsedModel::factory(800)->create();
        FeedbackModel::factory(20)->create();
        HistoryModel::factory(400)->create();
        QuestionModel::factory(20)->create();
        UserTrackModel::factory(200)->create();
        UserWeatherModel::factory(200)->create();
    }
}
