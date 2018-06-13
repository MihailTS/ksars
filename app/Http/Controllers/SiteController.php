<?php

namespace App\Http\Controllers;

use App\Services\Contracts\SiteService;

class SiteController extends Controller
{

    private $siteService;

    /**
     * @param SiteService $siteService
     */
    public function __construct(SiteService $siteService)
    {
        $this->siteService = $siteService;
    }

    public function index()
    {
        $links=[];
        $this->siteService->parseAll();
        return view('sites',['sites'=>$links]);
    }
}
