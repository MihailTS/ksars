<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SiteLink extends Model
{
    protected $fillable = [
        'url',
        'lastRequestTime',
        'status',
        'site_id'
    ];

    public function site(){
        return $this->belongsTo(Site::class);
    }
}
