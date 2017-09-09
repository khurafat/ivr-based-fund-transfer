<?php

use Illuminate\Database\Seeder;

class CustomersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('customers')->insert([
        	'first_name' => 'piyush',
        	'last_name' => 'agrawal',
        	'number' => 919818325292,
        	'balance' => 100000.98,
        	'tpin' => 1234,
        	'language' => 'en',
        ]);
    }
}
