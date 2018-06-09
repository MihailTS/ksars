<?php

namespace App\Http\Controllers;

use App\Http\Requests\VisitorReceiveRequest;
use App\Http\Requests\VisitorReceiveTimeRequest;
use App\Services\Contracts\VisitorService;
use App\Services\Contracts\SiteLinkService;
use App\SiteLink;
use App\Visit;
use App\Visitor;
use Carbon;
use Illuminate\Http\Request;

class VisitorController extends Controller
{
    private $visitorService;
    private $siteLinkService;

    /**
     * @param VisitorService $visitorService
     * @param SiteLinkService $siteLinkService
     */
    public function __construct(VisitorService $visitorService,SiteLinkService $siteLinkService)
    {
        $this->visitorService = $visitorService;
        $this->siteLinkService = $siteLinkService;
    }


    public function test(){
        return view('visit');
    }
    public function receive(VisitorReceiveRequest $request){

        echo $this->visitorService->receiveVisit($request);
    }

    public function receiveTime(VisitorReceiveTimeRequest $request){
        $this->visitorService->receiveTimeVisit($request);
    }


    public function allVisitorStats(){
        $visitors = Visitor::all();
        return view('visitors',['visitors'=>$visitors]);
    }

    public function visitorInfo($visitorID){
        $visitor = Visitor::findOrFail($visitorID);
        $visits = $visitor->visits;
        $keywords = [];
        $visitorKeywords = [];
        foreach($visits as $visit){
            $vKeywords = $visit->site_link->keywords;
            if(empty($keywords[$visit->site_link->id])){
                $keywords[$visit->site_link->id] = $vKeywords->toArray();

                foreach($vKeywords as $vKeyword){
                    if(!empty($vKeyword)){
                        $vKeywordKoef = $vKeyword['coefficient']
                            /** (SiteLink::TAGS_TO_PARSE_COUNT - $vKeyword['position'] + 1)*/;
                        if(!empty($visitorKeywords[$vKeyword['name']])){
                            $visitorKeywords[$vKeyword['name']] += $vKeywordKoef;
                        }else{
                            $visitorKeywords[$vKeyword['name']] = $vKeywordKoef;
                        }
                    }
                }
            }
        }
        arsort($visitorKeywords);
        $visitorKeywords = array_slice($visitorKeywords, 0, SiteLink::TAGS_TO_PARSE_COUNT, true);

        $visitorPositionKeywords = [];
        $similarLinks = [];

        $curPosition = 1;
        foreach($visitorKeywords as $visitorKeywordKey=>$visitorKeyword){
            $visitorPositionKeywords[$curPosition] = $visitorKeywordKey;
            $curPosition++;
        }
        $similarLinksData = $this->siteLinkService->findSimilarByKeywords($visitorPositionKeywords);

        foreach($similarLinksData as $similarLinkID=>$similarLinkWeight){
            $similarLinks[] = ["entity"=>SiteLink::find($similarLinkID),"weight"=>$similarLinkWeight];

        }
        return view('visitor',['visits'=>$visits, 'visitor'=>$visitor, 'keywords'=>$keywords,'visitorKeywords'=>$visitorKeywords, 'similarLinks'=>$similarLinks]);
    }
}
