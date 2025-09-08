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

use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Delete All 
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        FeedbackModel::truncate();
        ClothesUsedModel::truncate();
        WashModel::query()->delete();
        ClothesModel::truncate();
        HistoryModel::truncate();
        UserModel::truncate();
        AdminModel::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Factory
        AdminModel::factory(10)->create();
        UserModel::factory(10)->create();
        ClothesModel::factory(10)->create();
        WashModel::factory(30)->create();
        ClothesUsedModel::factory(30)->create();
        FeedbackModel::factory(30)->create();
        HistoryModel::factory(50)->create();
    }
}
