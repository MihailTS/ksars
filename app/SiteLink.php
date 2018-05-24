<?php

namespace App;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use InvalidArgumentException;
use GuzzleHttp\Psr7\Request;
use Wamania\Snowball\Russian;
use Illuminate\Database\Eloquent\Model;
use Malahierba\WordCounter\WordCounter;
use nokogiri;

class SiteLink extends Model
{
    const TAGS_TO_PARSE_COUNT = 10;

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
                if(substr($url, -1)==='/'){
                    $url = substr($url, 0, -1);
                }
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
        $siteLink = null;
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
            return (substr($urlHost, -strlen($domainHost)) === $domainHost);
        }
    }

    public function analyzePage(){
        ini_set('max_execution_time', 300);
        if(SiteLink::isURLBelongsToSiteDomain($this->url,$this->site->url)){
            $client = new Client(['base_uri'=>$this->baseURI]);
            try{
                $res = $client->get($this->url);
                $this->status=$res->getStatusCode();
                if($this->status===200){
                    $html=$res->getBody()->getContents();
                    $content = new nokogiri($html);
                    $this->parseLinks($content);

                    $this->parseKeywords($content);

                }
            } catch(ConnectException $e){
                //
            } catch(RequestException $e){
                //
            } catch(InvalidArgumentException $e){

            }
        }
    }

    /**
     *  Парсит ссылки на странице
     */
    public function parseLinks(nokogiri $content)
    {
        $links = $content->get('a');
        foreach($links as $link){
            if(!empty($link['href'])){
                SiteLink::createLink($link['href'],$this->url,$this->site);
            }
        }
        $this->save();
    }

    /**
     *  Парсит ключевые слова на странице
     */
    public function parseKeywords(nokogiri $content)
    {
        $allText = $content->toText();
        $wordcounter = new WordCounter($allText);

        $total = $wordcounter->countEachWord();
        $total = array_filter($total,function($item){
            $valid = false;
            if(mb_strlen($item->word)>4){
                $valid = !preg_match('/[^А-Яа-яЁё]/', $item->word);
            }
            return $valid;
        });
        $stemmer = new Russian();
        $stemTextArr = array_reduce($total,function($carry,$item) use ($stemmer){
            $word = $stemmer->stem($item->word);
            if(!key_exists($word,$carry)){
                $carry[$word] = $item->count;
            }else{
                $carry[$word] += $item->count;
            }
            return $carry;
        },[]);
        arsort($stemTextArr);
        $stemTextArr = array_slice($stemTextArr, 0, SiteLink::TAGS_TO_PARSE_COUNT, true);

        Keyword::clearTagsOfSiteLink($this);
        Keyword::addFromArray($stemTextArr,$this);
    }

    public function findSimilar(){
        $siteID = $this->site->id;
        $currentLinkID = $this->id;
        $currentKeywords = $this->keywords->pluck('name','position')->toArray();
        $keywordPositions = array_flip($currentKeywords);
        $keywordsByLink = Keyword::orderBy('site_link_id','asc')->whereIn('name',$currentKeywords)->whereHas(
            'site_link',
            function($query) use ($siteID,$currentLinkID){
                $query->where('id','!=',$currentLinkID)->where('site_id', $siteID);
            }
        )->get()->groupBy('site_link_id');

        $keywordWeightTotals = [];
        foreach($keywordsByLink as $keywordByLink){
            $summ = 0;
            $positionsCount = count($keywordPositions);
            foreach($keywordByLink  as $keyword) {
                $summ+=($positionsCount - $keyword->position+1) *
                    $keyword->coefficient *
                    ($positionsCount-$keywordPositions[$keyword->name]+1);
            }
            $keywordWeightTotals[$keyword->site_link_id]=$summ;
        }
        arsort($keywordWeightTotals);
        var_dump($keywordWeightTotals);
    }

    public function site(){
        return $this->belongsTo(Site::class);
    }

    public function keywords(){
        return $this->hasMany(Keyword::class);
    }
}
