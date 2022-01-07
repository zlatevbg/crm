<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        \App\Models\User::truncate();

        \App\Models\User::create([
            'name' => 'Dimitar Zlatev',
            'email' => 'mitko@sunsetresort.bg',
            'password' => Hash::make('unikat'),
            'gender' => 'male',
            'domain_id' => 2,
        ]);
    }
}
