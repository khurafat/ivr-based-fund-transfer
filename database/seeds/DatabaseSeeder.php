<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        $this->call(CustomersTableSeeder::class);

        DB::table('customers')->insert([
        	'first_name' => 'piyush',
        	'last_name' => 'agrawal',
        	'number' => '16613804601',
        	'balance' => 100000.98,
        	'tpin' => 1234,
        	'language' => 'en',
        ]);
    }
}
