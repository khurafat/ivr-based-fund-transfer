<?php

namespace App\Services;

use App\Services\Identity;

class Payment{

	public static function makePayment($conversation_id){
		$conversation = Conversation::where('conversation_id', $conversation_id)->first();
		
		if( !is_null($transaction) ){
			if($conversation->authorized==1){
				$transaction = $conversation->transaction;
				$amount = $transaction->amount;
				$customer = $transaction->customer;
				$receiver_id = $transaction->receiver_id;
				$receiver = Customer::where('id', $receiver_id)->first();
				$new_amount = $customer->balance - float($amount);
				$customer->update(['balance' => $new_amount]);
				$receiver->balance += float($amount);
				$receiver->save();
				return true;
			}
		}

	}

}