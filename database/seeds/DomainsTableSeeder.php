<?php

use Illuminate\Database\Seeder;

class DomainsTableSeeder extends Seeder
{
    public function run()
    {
        \App\Models\Domain::truncate();

        \App\Models\Domain::create([
            'domain' => 'www',
            'name' => 'Public Website',
            'auth' => '/account',
            'guest' => '/',
        ]);


        \App\Models\Domain::create([
            'domain' => 'cms',
            'name' => 'Admin Control Panel',
            'auth' => '/dashboard',
            'guest' => '/',
        ]);
    }
}
