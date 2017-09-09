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
    	return $this->belongsTo('App\Transaction');
    }
    public function customer()
    {
    	return $this->hasOne('App\Customer');
    }
}
