<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
class Visitor extends Model
{
    protected $fillable = [
        'hash'
    ];


    /*public function getCreatedAtColumn($value)
    {
        $c = new Carbon($value);
        return $c->setTimezone('Europe/Moscow');
    }*/


    public function visits(){
        return $this->hasMany(Visit::class);
    }


}
