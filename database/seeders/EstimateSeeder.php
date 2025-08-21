<?php

namespace Database\Seeders;

use App\Models\Estimate;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EstimateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Estimate::factory()
            ->count(100)
            ->withTasks(2)
            ->create();
    }
}
