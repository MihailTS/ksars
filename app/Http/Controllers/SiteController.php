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
        /*$site = Site::first();
        if($site->links->count()===0){
            $this->parse($site->url);
        }else{
           $links=$site->links;
        }*/
        return view('sites',['sites'=>$links]);
    }

    public function parseAll()
    {
        $sites = Site::all();
        foreach($sites as $site){
            $site->parse($site);
        }
    }


    /*private function parse(String $url){
        $client = new Client();
        $siteId = 1;
        $res = $client->request('GET', $url);
        $html=$res->getBody()->getContents();

        $saw = new nokogiri($html);
        $links = $saw->get('a');
        foreach($links as $link){
            $siteLink = new SiteLink;
            $siteLink->url = $link['href'];
            $siteLink->site_id = $siteId;
            $siteLink->status = 200;
            $siteLink->lastRequestTime = Carbon::now();
            $siteLink->save();
        }

    }*/
}
