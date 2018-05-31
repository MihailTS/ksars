<?php

namespace App\Http\Controllers;

use App\Http\Requests\VisitorReceiveRequest;
use App\Services\Contracts\VisitorService;
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
        echo json_encode($request->cookie('laravel_session'));
        //dd($request->ip().$request->userAgent());
    }


}
