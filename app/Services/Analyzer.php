<?php

namespace App\Services;

use App\Services\Identity;

function localize($text){
	return $text;
}

class Anazlyzer{

	public static function welcome(Identity $identity){
		if( !$identity->isnull )
			return localize("Welcome to m Pay");
		else
			return localize("Welcome to m Pay. Please select your language");
	}

	public static function canTransfer(Identity $identity, $conversation_id){
		if($identity->auth && !$identity->isnull){
			$transaction = Conversation::where('conversation_id', $conversation_id)->transaction;
			if( !is_null($transaction) ){
				if($transaction->status==0){
					return $transaction;
				}
			}
		}
		return false;
	}

	public static function confirmTransfer(Identity $identity, $conversation_id){
		$transaction = self::canTransfer($identity, $conversation_id);
		if( $transaction != false){
			$transaction->status = 1;
			$transaction->save();
		}
	}

}