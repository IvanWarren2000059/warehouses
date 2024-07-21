<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([

            [   
                'id' => 1,
                'name' => 'Admin',
                'user_Type' => 'Admin',
                'email' => 'admin@gmail.com',
                'password' => bcrypt('admin1'),
                'age' => 18,
                'gender' => 'Female',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [   
                'id' => 2,
                'name' => 'Dr. Samantha',
                'user_Type' => 'Doctor',
                'email' => 'dr.samantha@gmail.com',
                'password' => bcrypt('dr.samantha'),
                'age' => 35,
                'gender' => 'Female',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'id' => 3,
                'name' => 'Cherry White',
                'user_Type' => 'Patient',
                'email' => 'cherrywhite@gmail.com',
                'password' => bcrypt('cherrywhite'),
                'age' => 18,
                'gender' => 'Female',
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ]);
    }
}
