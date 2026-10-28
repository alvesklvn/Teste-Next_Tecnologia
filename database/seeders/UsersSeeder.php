<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'name' => 'administrador',
            'email' => 'admin123@gmail.com',
            #administradorbarber123!
            'password' => "\$2y\$12\$MogElyrAjy9Oiwp0X7mMBeYIMG9OYZiIK77nHw3B.HVCGOsrlLVwW",
            'email_verified_at' => now(),
            'user_type_id' => 1,
            'created_at' => now()
        ]);
    }
}
