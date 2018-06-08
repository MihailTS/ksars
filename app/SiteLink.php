<?php

namespace App;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use InvalidArgumentException;
use GuzzleHttp\Psr7\Request;
use Wamania\Snowball\Russian;
use Illuminate\Database\Eloquent\Model;
use Malahierba\WordCounter\WordCounter;
use nokogiri;

class SiteLink extends Model
{

    protected $fillable = [
        'url',
        'baseURI',
        'status',
        'site_id'
    ];

    public function site(){
        return $this->belongsTo(Site::class);
    }

    public function keywords(){
        return $this->hasMany(Keyword::class);
    }
}