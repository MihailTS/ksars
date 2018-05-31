<?php

namespace App\Http\Controllers;

use App\Services\Contracts\SiteLinkService;
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

    public function similar($id){
        $this->siteLinkService->findSimilar($id);
    }
}
