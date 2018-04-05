<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    protected $fillable = [
      'url'
    ];

    public function parse()
    {
        if($this->links->count()===0){//ссылки для сайта еще не обрабатывались
            $siteMainLink=SiteLink::createLink($this->url,$this->url,$this);
            $siteMainLink->parseLinks();
        }else{
            foreach($this->links as $link){
                $link->parseLinks();
            }
        }
    }

    public function links(){
        return $this->hasMany(SiteLink::class);
    }
}
