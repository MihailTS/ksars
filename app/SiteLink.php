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

    public static function validateUrl(String $url, String $domain)
    {
        /*if(preg_match('/^(tel|javascript|mailto)/', $url)===false){
            if(preg_match('/^(http|https):\/\//', $url)){

            }
        }*/
        return $url;
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
        }

        return $siteLink;
    }

    /**
     *  Парсит ссылки на странице
     */
    public function parseLinks()
    {
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
            }
        } catch(ConnectException $e){
            $this->status=0;
        } catch(RequestException $e){
            $this->status=1;
        } finally {
            $this->save();
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
