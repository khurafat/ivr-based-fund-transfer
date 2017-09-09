<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    //
    protected $fillable = [
        '*'
    ];
    public function customer()
    {
    	return $this->belongsTo('App\Customer');
    }
    public function conversation()
    {
    	return $this->hasMany('App\Conversation');
    }
}
