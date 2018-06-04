<?php
namespace App\Services;

use App\Http\Requests\VisitorReceiveRequest;
use App\Http\Requests\VisitorReceiveTimeRequest;
use App\Site;
use App\SiteLink;
use App\Visit;
use App\Visitor;
use App\Services\Contracts\VisitorService as VisitorServiceContract;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VisitorService implements VisitorServiceContract
{
    private $siteLinkService;

    public function __construct(SiteLinkService $siteLinkService)
    {
        $this->siteLinkService = $siteLinkService;
    }

    public function receiveVisit(VisitorReceiveRequest $request){
        $referer = $request->getReferer();
        $visitorCookie = $request->getVisitorCookie();
        $visitorHash = $request->getVisitorHash();
        $visitHash = $request->getVisitHash();

        if($visitorCookie){
            $visitorHash = $visitorCookie;
        }

        $refererHost = parse_url($referer,PHP_URL_HOST);
        $site = Site::where('url','like',"%".$refererHost)->first();
        if(!$site){
            return $refererHost;
        }
        $siteLink=SiteLink::where('url',$referer)->first();
        if(!$siteLink){
            $siteLink =$this->siteLinkService->createLink($referer, $site->url, $site);
            if(!$siteLink){
                return null;
            }
        }
        $visitor = Visitor::where('hash',$visitorHash)->first();
        if(!$visitor){
            $visitor = new Visitor;
            $visitor->hash = $visitorHash;
            $visitor->save();
        }

        $visit = new Visit;
        $visit->time_on_page = 0;
        $visit->hash = $visitHash;
        $visit->ip = $request->ip();
        $visit->user_agent = $request->userAgent();
        $visit->site_link_id = $siteLink->id;
        $visit->visitor_id = $visitor->id;
        $visit->save();

        return json_encode(['visitor'=>$visitorHash,'visit'=>$visitHash,'test'=>$siteLink]);
    }

    public function receiveTimeVisit(VisitorReceiveTimeRequest $request){
        $visitHash = $request->getVisitHash();
        $visitorHash = $request->getVisitorHash();


        $visit = Visit::where('hash', $visitHash)->whereHas('visitor', function ($query) use ($visitorHash) {
            $query->where('hash', $visitorHash);
        })->firstOrFail();
        if($visit){
            $visit->time_on_page = Carbon::now()->diffInSeconds($visit->created_at);
            $visit->save();
        }

    }
}