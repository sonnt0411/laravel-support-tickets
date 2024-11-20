<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // seeding support ticket categories
        Category::insert([
            ['name' => 'General'],
            ['name' => 'Technical'],
            ['name' => 'Sales'],
            ['name' => 'Billing'],
            ['name' => 'Other'],
        ]);
    }
}
