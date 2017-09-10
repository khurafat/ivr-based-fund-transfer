<?php

namespace App\Services;

use App\Services\Identity;
use App\Conversation;
use App\Customer;

class Payment{

	public static function makePayment($conversation_id){
		$conversation = Conversation::where('conversation_id', $conversation_id)->orderby('id', 'desc')->first();
		
		if( !is_null($conversation) ){
			if($conversation->authorized==1){
				$transaction = $conversation->transaction;
				if($transaction->status == 2)
					return false;
				$amount = $transaction->amount;
				$customer = Customer::where('id', $transaction->customer->id)->first();
				$receiver_id = $transaction->reciever_id;
				$receiver = Customer::where('id', $receiver_id)->first();
				$new_amount = $customer->balance - floatval($amount);
				$customer->balance = $new_amount;
				$customer->save();
				$receiver->balance += floatval($amount);
				$receiver->save();
				$transaction->status = 2;
				$transaction->save();
				return true;
			}
		}

	}

}