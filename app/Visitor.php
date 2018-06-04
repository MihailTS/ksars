<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    protected $fillable = [
        'hash'
    ];

    public function visits(){
        return $this->hasMany(Visit::class);
    }
}
