<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Services\Identity;
use App\Customer;

class Identity{
	public $auth = false;
	public $lang = 'en';
	public $profile = [];
	public $isnull = true;

	public function __construct($number){
		$customer = Customer::where('number', $number)->first();
		if( !is_null($customer) ){
			$this->profile = $customer;
			$this->isnull = false;
			$this->lang = $customer->language;
		}
	}

	public function authenticate($tpin){
		$customer = Customer::where('tpin', $tpin)->first();
		if( !is_null($customer) ){
			$this->auth = true;
		}
	}




}