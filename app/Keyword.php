<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Keyword extends Model
{
    protected $fillable = [
        'keyword',
        'site_link_id',
        'weight',
        'coefficient'
    ];


    public static function addFromArray($keywords, $siteLink){
        $position = 1;

        $totalWeight = array_sum($keywords)?:1;
        foreach($keywords as $keyword=>$weight){
            $k = new Keyword();
            $k->name = $keyword;
            $k->weight = $weight;
            $k->site_link_id = $siteLink->id;
            $k->position = $position;
            $k->coefficient = 1 + $weight/$totalWeight;
            $k->save();
            $position++;
        }
    }

    public static function clearTagsOfSiteLink($siteLink){
        Keyword::where('site_link_id',$siteLink->id)->delete();
    }

    public function site_link(){
        return $this->belongsTo(SiteLink::class);
    }
}