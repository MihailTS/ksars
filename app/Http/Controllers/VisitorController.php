<?php

namespace App\Http\Controllers;

use App\Http\Requests\VisitorReceiveRequest;
use App\Services\Contracts\VisitorService;
use App\SiteLink;
use App\Visit;
use Carbon;
use Illuminate\Http\Request;

class VisitorController extends Controller
{
    private $visitorService;

    /**
     * @param VisitorService $visitorService
     */
    public function __construct(VisitorService $visitorService)
    {
        $this->visitorService = $visitorService;
    }


    public function test(){
        return view('visit');
    }
    public function receive(VisitorReceiveRequest $request){
        $referer = $request->headers->get('referer');
        $visitorCookie = $request->input('ksars');
        if($visitorCookie){
            $visitorHash = $visitorCookie;
        }else{
            $ip = $request->ip();
            $userAgent = $request->userAgent();
            $visitorHash = MD5($ip.$userAgent);
        }
        $visitHash = MD5($ip.time().$userAgent);

        $siteLink=SiteLink::where('url',$referer)->get();

        try{
            $visitor = Visitor::where('hash',$visitorHash)->findOrfail();
        }catch(\Exception $e){
            $visitor = new Visitor;
            $visitor->hash = $visitorHash;
            $visitor->save();
        }

        $visit = new Visit;
        $visit->timeOnPage = 0;
        $visit->hash = $visitHash;
        $visit->site_link_id = $siteLink->id;
        $visit->visitor_id = $visitor->id;
        $visit->save();

        echo json_encode(['visitor'=>$visitorHash,'visit'=>$visitHash,'test'=>$siteLink]);
    }

    public function receiveTime(){
        $visitHash = Request::input('visit');
        $visitorHash = Request::input('visitor');


        try {
            $visit = Visit::where('hash', $visitHash)->whereHas('visitor', function ($query) use ($visitorHash) {
                $query->where('hash', $visitorHash);
            })->firstOrFail();
            $visit->timeOnPage = Carbon::now()->diffInSeconds($visit->created_at);

            $visit->save();
        }catch(\Exception $e){

        }
    }
}
