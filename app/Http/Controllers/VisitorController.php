<?php

namespace App\Http\Controllers;

use App\Http\Requests\VisitorReceiveRequest;
use App\Http\Requests\VisitorReceiveTimeRequest;
use App\Services\Contracts\VisitorService;
use App\Services\Contracts\SiteLinkService;
use App\SiteLink;
use App\Visit;
use App\Visitor;
use Carbon\Carbon;
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
        $visits = $visitor->visits->where('time_on_page','>',10);
        $keywords = [];
        $visitorKeywords = [];
        foreach($visits as $visit){
            $vKeywords = $visit->site_link->keywords;
            if(empty($keywords[$visit->site_link->id])){
                $keywords[$visit->site_link->id] = $vKeywords->toArray();

                foreach($vKeywords as $vKeyword){
                    if(!empty($vKeyword)){
                        $vKeywordKoef = $vKeyword['coefficient'];
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

        $similarLinks = [];

        $similarLinksData = $this->siteLinkService->findSimilarByKeywords($visitorKeywords);

        foreach($similarLinksData as $similarLinkID=>$similarLinkWeight){
            $similarLinks[] = ["entity"=>SiteLink::find($similarLinkID),"weight"=>$similarLinkWeight];

        }
        $allMonths  = ["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь","Декабрь"];
        $month1 = $allMonths[date('n')-1];
        $month2 = (date('n')-2>=0)?$allMonths[date('n')-2]:$allMonths[12+date('n')-2];
        $month3 = (date('n')-3>=0)?$allMonths[date('n')-3]:$allMonths[12+date('n')-3];
        $months=[$month1,$month2,$month3];
        return view('visitor',['visits'=>$visits, 'visitor'=>$visitor, 'keywords'=>$keywords,
            'visitorKeywords'=>$visitorKeywords, 'similarLinks'=>$similarLinks, 'months'=>$months]);
    }

    public function visitorInfoByMonthAgo($visitorID, $monthAgo){
        $visitor = Visitor::findOrFail($visitorID);
        $selectedMonth = (date('n')-$monthAgo)%12;
        if($selectedMonth<=0){
            $selectedMonth+=12;
        }
        $visits = $visitor->visits->where('time_on_page','>',10)
            ->where('created_at', '>=', Carbon::now()->startOfMonth()->subMonth($monthAgo))
            ->where('created_at', '<=', Carbon::now()->subMonth($monthAgo)->endOfMonth());
        $keywords = [];
        $visitorKeywords = [];
        foreach($visits as $visit){
            $vKeywords = $visit->site_link->keywords;
            if(empty($keywords[$visit->site_link->id])){
                $keywords[$visit->site_link->id] = $vKeywords->toArray();

                foreach($vKeywords as $vKeyword){
                    if(!empty($vKeyword)){
                        $vKeywordKoef = $vKeyword['coefficient'];
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
        $allMonths  = ["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь","Декабрь"];
        $monthName = $allMonths[$selectedMonth-1];
        return view('visitor_month',['visits'=>$visits, 'visitor'=>$visitor, 'keywords'=>$keywords,
            'visitorKeywords'=>$visitorKeywords,'monthName'=>$monthName]);
    }
}
