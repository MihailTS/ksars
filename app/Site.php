<?php

namespace App;

use Carbon\Carbon;
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
            if($siteMainLink){
                $siteMainLink->analyzePage();
            }
        }else{
            foreach($this->links as $link){
                $link->analyzePage();
                //var_dump($link->url);

            }
        }
    }

    public function links(){
        return $this->hasMany(SiteLink::class)
            ->whereNull('status')
            ->orWhere('status','!=',200)
            ->orWhere('updated_at','<', Carbon::yesterday());
    }
}
