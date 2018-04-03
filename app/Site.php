<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    protected $fillable = [
      'url',
      'name'
    ];

    public function links(){
        return $this->hasMany(SiteLink::class);
    }
}
