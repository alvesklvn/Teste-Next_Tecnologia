<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('user_types')->insert([
            'title' => 'admin',
            'created_at' => now()
        ]);

        DB::table('user_types')->insert([
            'title' => 'client',
            'created_at' => now()
        ]);
    }
}
