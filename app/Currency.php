<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    public $timestamps = false;

    public function latestPrice()
    {
       return $this->hasOne('App\Price')->latest();
    }
}
