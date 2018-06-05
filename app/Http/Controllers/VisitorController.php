<?php

namespace App\Http\Controllers;

use App\Http\Requests\VisitorReceiveRequest;
use App\Http\Requests\VisitorReceiveTimeRequest;
use App\Services\Contracts\VisitorService;
use App\SiteLink;
use App\Visit;
use App\Visitor;
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

        echo $this->visitorService->receiveVisit($request);
    }

    public function receiveTime(VisitorReceiveTimeRequest $request){
        $this->visitorService->receiveTimeVisit($request);
    }


    public function allVisitorStats(){
        $visitors = Visitor::all();
        return view('visitors',['visitors'=>$visitors]);
    }

    public function allVisits(Visitor $visitor){
        $visitors = Visitor::all();
        return view('visits',['visitors'=>$visitors]);
    }
}
