<?php

namespace App\Services;

use App\Conversation;
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

	public function authenticate($tpin, $convId){
        $convO = Conversation::where('conversation_id', $convId)->orderBy('id',                                                'DESC')->first();
		$customer = Customer::where('tpin', $tpin)->where("id", $convO->customer_id)->first();
		if( !is_null($customer) ){
			$this->auth = true;
            $conv = Conversation::where('customer_id', $customer->id)->orderBy('id',                                                'DESC')->first();
			$conv->update(['authorized' => true]);
		}
	}




}
