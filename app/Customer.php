<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    //
    protected $fillable = [
        '*'
    ];

    public function conversation()
    {
    	return $this->hasOne('App\Conversation');

    }
    
    public function transaction()
    {
    	return $this->hasMany('App\Transaction');
    }
}
