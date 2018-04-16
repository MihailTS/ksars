<?php

namespace App;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Illuminate\Database\Eloquent\Model;
use nokogiri;

class SiteLink extends Model
{
    protected $fillable = [
        'url',
        'baseURI',
        'status',
        'site_id'
    ];

    public static function validateUrl(String $url, String $siteUrl)
    {
        if(SiteLink::isURLBelongsToSiteDomain($url,$siteUrl)){
            //if(!preg_match('/^(#|\\\'|\/|tel|javascript|mailto)/i', $url)){
            if(
                $url!='/' &&
                !preg_match('/^(\'|#|tel|javascript|mailto)/i', $url)
            ){
                $siteLinkDublicate=SiteLink::where("url",$url)->first();
                if(!$siteLinkDublicate){
                    return $url;
                }
            }
        }
        return false;
    }
    public static function createLink(String $url, String $baseURI, Site $site, Integer $status=null)
    {
        if($validURL=SiteLink::validateUrl($url,$site->url))
        {
            $siteLink = new SiteLink;
            $siteLink->url = $validURL;
            $siteLink->baseURI = $baseURI;
            $siteLink->site_id = $site->id;
            $siteLink->status = $status;
            $siteLink->save();
            return $siteLink;
        }

    }

    /**
     * Возвращает принадлежит ли ссылка домену данного сайта
     * @param $url
     * @param $siteUrl
     * @return bool
     */
    private static function isURLBelongsToSiteDomain($url, $siteUrl){
        $domainHost = parse_url($siteUrl,PHP_URL_HOST);
        $urlHost = parse_url($url,PHP_URL_HOST);
        if(!$urlHost){//относительная ссылка
            return true;
        }else{
            var_dump($urlHost.":".$domainHost.
                "(".(substr($urlHost, -strlen($domainHost)) === $domainHost).")");
            //return true;
            return (substr($urlHost, -strlen($domainHost)) === $domainHost);
            //return true;//(substr($urlHost, 0, strlen($domainHost)) === $domainHost);
        }
    }

    /**
     *  Парсит ссылки на странице
     */
    public function parseLinks()
    {
        if(SiteLink::isURLBelongsToSiteDomain($this->url,$this->site->url)){
            $client = new Client(['base_uri'=>$this->baseURI]);
            try{
                $res = $client->get($this->url);
                $this->status=$res->getStatusCode();
                if($this->status===200){
                    $html=$res->getBody()->getContents();
                    $saw = new nokogiri($html);
                    $links = $saw->get('a');
                    foreach($links as $link){
                        if(!empty($link['href'])){
                            SiteLink::createLink($link['href'],$this->url,$this->site);
                        }
                    }
                    $this->save();
                }
            } catch(ConnectException $e){
                //
            } catch(RequestException $e){
                //
            }
        }
    }

    /**
     *  Парсит ключевые слова на странице
     */
    public function parseKeywords()
    {

    }

    public function site(){
        return $this->belongsTo(Site::class);
    }
}
