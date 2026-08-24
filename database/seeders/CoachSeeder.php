<?php

namespace Database\Seeders;

use App\Models\Coach;
use Illuminate\Database\Seeder;

class CoachSeeder extends Seeder
{
    public function run(): void
    {
        Coach::create(['name' => 'Vito Piperis Coach', 'slug' => 'vito-piperis']);
        Coach::create(['name' => 'Francesco Piperis Coach', 'slug' => 'francesco-piperis']);
    }
}
