<?php

namespace Database\Seeders;

use App\Models\AdminModel;
use App\Models\UserModel;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        AdminModel::factory(10)->create();
        UserModel::factory(10)->create();
    }
}
