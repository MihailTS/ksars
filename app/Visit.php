<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    protected $fillable = [
        'timeOnPage',
        'visitor_id',
        'site_link_id'
    ];

    public function visitor(){
        return $this->belongsTo(Visitor::class);
    }

    public function site_link(){
        return $this->belongsTo(SiteLink::class);
    }
}
