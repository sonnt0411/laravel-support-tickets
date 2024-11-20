<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LabelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // seeding support ticket labels
        \App\Models\Label::insert([
            ['name' => 'Urgent'],
            ['name' => 'Important'],
            ['name' => 'Question'],
            ['name' => 'Problem'],
            ['name' => 'Complaint'],
            ['name' => 'Request'],
            ['name' => 'Other'],
        ]);
    }
}
