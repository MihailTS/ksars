<?php

namespace App\Http\Controllers;

use App\Http\Requests\VisitorReceiveRequest;
use App\Services\Contracts\VisitorService;
use App\SiteLink;
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
        $ip = $request->ip();
        $referer = $request->headers->get('referer');
        $ksars = $request->input('ksars');
        $userAgent = $request->userAgent();
        $visitorHash = MD5($ip.$userAgent);
        $visitHash = MD5($ip.time().$userAgent);
        $parsedReferer = parse_url($referer);
        $a=SiteLink::where('url',$referer)->orWhere('url',$parsedReferer['path'])->get();
        echo json_encode(['visitor'=>$visitorHash,'visit'=>$visitHash,'test'=>$a]);
    }


}
