<?php

namespace App\Http\Controllers;

use App\Site;
use App\SiteLink;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;
use Illuminate\Http\Request;
use nokogiri;

class SiteController extends Controller
{
    public function index()
    {
        $links=[];
        $this->parseAll();
        return view('sites',['sites'=>$links]);
    }

    public function parseAll()
    {
        $sites = Site::all();
        foreach($sites as $site){
            $site->parse($site);
        }
    }
}
