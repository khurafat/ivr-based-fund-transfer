<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    //
    protected $fillable = [
        '*'
    ];
    public function transaction()
    {
    	return $this->hasOne('App\Transaction');
    }
    public function customer()
    {
    	return $this->belongsTo('App\Customer');
    }
}
