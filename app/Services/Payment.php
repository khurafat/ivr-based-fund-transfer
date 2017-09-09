<?php

namespace App\Services;

use App\Services\Identity;

class Payment{

	public static function makePayment($identity, $conversation_id, $amount, $receiver){
		if($identity->auth && !$identity->isnull){
			$transaction = Conversation::where('conversation_id', $conversation_id)->transaction;
			if( !is_null($transaction) ){
				if($transaction->status==1){
					$customer = $transaction->customer;
					$receiver = $transaction->receiver;
					$customer->balance -= float($amount);
					$receiver->balance += float($amount);
					$customer->save();
					$receiver->save();
					return true;
				}
			}
		}

	}

}