<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Keyword extends Model
{
    protected $fillable = [
        'keyword',
        'site_link_id',
        'weight',
    ];


    public static function addFromArray($keywords, $siteLink){
        foreach($keywords as $keyword=>$weight){
            $k = new Keyword();
            $k->keyword = $keyword;
            $k->weight = $weight;
            $k->site_link_id = $siteLink->id;
            $k->save();
        }
    }

    public static function clearTagsOfSiteLink($siteLink){
        Keyword::where('site_link_id',$siteLink->id)->delete();
    }
}
