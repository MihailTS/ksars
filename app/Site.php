<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    protected $fillable = [
      'url'
    ];


    public function links(){
        return $this->hasMany(SiteLink::class)
            ->whereNull('status')
            ->orWhere('status','!=',200)
            ->orWhere('updated_at','<', Carbon::yesterday());
    }
}
