<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    protected $fillable = [
        'time_on_page',
        'visitor_id',
        'site_link_id',
        'ip',
        'user_agent'
    ];

    public function visitor(){
        return $this->belongsTo(Visitor::class);
    }

    public function site_link(){
        return $this->belongsTo(SiteLink::class);
    }
}
