<?php
namespace App\Services;

use App\Site;
use App\Services\Contracts\SiteService as SiteServiceContract;

class SiteService implements SiteServiceContract
{
    private $siteLinkService;

    public function __construct(SiteLinkService $siteLinkService)
    {
        $this->siteLinkService = $siteLinkService;
    }

    public function parse(Site $site)
    {
        if($site->links->count()===0){//ссылки для сайта еще не обрабатывались
            $siteMainLink=$this->siteLinkService->createLink($site->url,$site->url,$site);
            if($siteMainLink){
                $this->siteLinkService->analyzePage($siteMainLink);
            }
        }else{
            foreach($site->links as $link){
                $this->siteLinkService->analyzePage($link);

            }
        }
    }

    public function parseAll()
    {
        $sites = Site::all();
        foreach($sites as $site){
            $this->parse($site);
        }
    }
}