<?php

namespace App\Http\Controllers;

use App\Services\SiteLinkService;
use App\SiteLink;
use Illuminate\Http\Request;

class SiteLinkController extends Controller
{

    private $siteLinkService;

    /**
     * @param SiteLinkService $siteLinkService
     */
    public function __construct(SiteLinkService $siteLinkService)
    {
        $this->siteLinkService = $siteLinkService;
    }

    public function linkInfo($id)
    {
        $siteLink = SiteLink::findOrFail($id);
        $similarLinks = $this->siteLinkService->findSimilar($siteLink);
        $siteLinkKeywords = [];
        foreach($siteLink->keywords as $keyword){
            $keywordArr = [];
            $keywordArr["weight"] = $keyword->weight*$keyword->coefficient;
            $keywordArr["name"] = $keyword->name;
            $keywordArr["ID"] = $keyword->id;

            $siteLinkKeywords[] = $keywordArr;
        }
        dd($similarLinks);
        return view('site_link',['siteLink'=>$siteLink,'similarLinks'=>$similarLinks, "siteLinkKeywords"=>$siteLinkKeywords]);
    }
}
