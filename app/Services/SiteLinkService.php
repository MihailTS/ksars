<?php
namespace App\Services;

use App\Keyword;
use App\SiteLink;
use App\Site;
use App\Services\Contracts\SiteLinkService as SiteLinkServiceContract;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Wamania\Snowball\Russian;
use Malahierba\WordCounter\WordCounter;
use nokogiri;

class SiteLinkService implements SiteLinkServiceContract
{

    function _normalise($path, $encoding="UTF-8") {

        // Attempt to avoid path encoding problems.
        //$path = iconv($encoding, "$encoding//IGNORE//TRANSLIT", $path);
        // Process the components
        $parts = explode('/', $path);
        $safe = array();
        foreach ($parts as $idx => $part) {
            if (empty($part) || ('.' == $part)) {
                continue;
            } elseif ('..' == $part) {
                array_pop($safe);
                continue;
            } else {
                $safe[] = $part;
            }
        }

        // Return the "clean" path
        $path = implode('/', $safe);
        return $path;
    }

    function resolveUrl($base, $url) {
        if(!$url){
            return false;
        }
        $url = mb_substr($url,0,strrpos($url,'#')?:null);
        if(!$base){
            return false;
        }

        $base = trim($base, '/').'/';

        $array_url = parse_url($url);

        //Если ссылка абсолютная возвращаем ее
        if(isset($array_url['scheme']) && isset($array_url['host'])){
            $url = trim($url, '/').'/';
            return $url;
        }

        $array_base = parse_url($base);

        $res = '';

        if(empty($array_base['scheme']) || empty($array_base['host'])){
            return false;
        }

        //Собираем абсолютную ссылку
        $res.=$array_base['scheme'] . '://';

        if(isset($array_base['user'])) {
            $res .= $array_base['user'].':';
        }

        if(isset($array_base['pass'])) {
            $res .= $array_base['pass'].'@';
        }

        $res.=$array_base['host'];

        if(isset($array_base['port'])) {
            $res .= ':'.$array_base['port'];
        }

        if(isset($array_url['path'])) {
            //Если в относительной ссылке слеш указывает на корень сайта
            if(strpos($array_url['path'],'/') === 0){
                $base_path = '/';
            } else {
                $base_path = $array_base['path'];
            }

            $res .='/'.$this->_normalise($base_path.$array_url['path']);
        }
        $res = trim($res, '/').'/';
        if(isset($array_url['query'])){
            $res.='?'.$array_url['query'];
        }
        return $res;
    }

    public function validateUrl(String $url, String $siteUrl)
    {
        $url = $this->resolveUrl($siteUrl,$url);
        if(SiteLinkService::isURLBelongsToSiteDomain($url,$siteUrl)){
            if(
                $url!='/' &&
                !preg_match('/^(\'|#|stype|tel|javascript|mailto)/i', $url)
            ){
                return $url;
            }
        }
        return false;
    }
    public function createLink(string $url, string $baseURI, Site $site, int $status=null)
    {
        $siteLink = null;
        if($validURL=$this->validateUrl($url, $site->url))
        {
            $siteLinkDublicate=SiteLink::where("url",$validURL)->first();
            if($siteLinkDublicate){
                return $siteLinkDublicate;
            }
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
    private function isURLBelongsToSiteDomain($url, $siteUrl){
        $domainHost = parse_url($siteUrl,PHP_URL_HOST);
        $urlHost = parse_url($url,PHP_URL_HOST);
        if(!$urlHost){//относительная ссылка
            return true;
        }else{
            return (substr($urlHost, -strlen($domainHost)) === $domainHost);
        }
    }

    public function analyzePage(SiteLink $siteLink){
        ini_set('max_execution_time', 300);
        if($this->isURLBelongsToSiteDomain($siteLink->url,$siteLink->site->url)){
            $baseURL = $siteLink->baseURI;
            if(substr($baseURL,0,1)==='/'){
                $baseURL = $siteLink->site->url . $baseURL;
            }

            $client = new Client(['base_uri'=>$baseURL]);
            try{
                $res = $client->get($siteLink->url);
                $siteLink->status=$res->getStatusCode();
                if($siteLink->status===200){
                    $html=$res->getBody()->getContents();
                    $content = new nokogiri($html);
                    $this->parseLinks($siteLink, $content);

                    Keyword::clearTagsOfSiteLink($siteLink);
                    if($this->isParsingAllowed($content)){
                        $keywordsArray = $this->parseKeywords($content);
                        Keyword::addFromArray($keywordsArray,$siteLink);
                    }
                }
            } catch(ConnectException $e){
                //
            } catch(RequestException $e){
                //
            } catch(InvalidArgumentException $e){

            }
        }
    }

    public function isParsingAllowed(nokogiri $content){
        $allowed = false;
        try {
            $ksarsMeta = $content->get('meta[property="ksars"]');
            foreach($ksarsMeta as $ksarsMetaTag){
                if($ksarsMetaTag["allow"] == 'true'){
                    $allowed = true;
                }
            }
        }catch(\Exception $e){

        }
        return $allowed;

    }
    /**
     *  Парсит ссылки на странице
     */
    public function parseLinks(SiteLink $siteLink, nokogiri $content)
    {
        $links = $content->get('a');
        foreach($links as $link){
            if(!empty($link['href'])){
                SiteLinkService::createLink($link['href'],$siteLink->url,$siteLink->site);
            }
        }
        $siteLink->save();
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
            if(mb_strlen($item->word)>3){
                $valid = !preg_match('/[^А-Яа-яЁё]/u', $item->word);
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
        return $stemTextArr;
    }


    public function findSimilarByKeywords($keywords){
        $keywordsByLink = Keyword::orderBy('site_link_id','asc')->whereIn('name',array_keys($keywords))
            ->get()->groupBy('site_link_id');
        $keywordWeightTotals = [];
        foreach($keywordsByLink as $keywordByLink){
            $summ = 0;
            foreach($keywordByLink  as $keyword) {
                $summ+= $keyword->coefficient *
                    $keywords[$keyword->name];
                $keywordWeightTotals[$keyword->site_link_id]=$summ;
            }
        }
        arsort($keywordWeightTotals);
        $keywordWeightTotals = array_slice($keywordWeightTotals, 0, SiteLink::TAGS_TO_PARSE_COUNT, true);
        return $keywordWeightTotals;
    }

    public function findSimilar(SiteLink $siteLink){
        $siteID = $siteLink->site->id;
        $currentLinkID = $siteLink->id;
        $currentKeywords = $siteLink->keywords;
        $currentKeywordsArray = $siteLink->keywords->pluck('name')->toArray();
        $keywordsByLink = Keyword::orderBy('site_link_id','asc')->whereIn('name',$currentKeywordsArray)->whereHas(
            'site_link',
            function($query) use ($siteID,$currentLinkID){
                $query->where('id','!=',$currentLinkID)->where('site_id', $siteID);
            }
        )->get()->groupBy('site_link_id');

        $keywordWeightTotals = [];
        foreach($keywordsByLink as $keywordByLink){

            $summ = 0;
            foreach($keywordByLink  as $keyword) {
                $compareKeyword = $currentKeywords->where('name',$keyword->name)->first();
                $summ+= $keyword->coefficient *
                    $compareKeyword->coefficient;
                $keywordWeightTotals[$keyword->site_link_id]=$summ;
            }
        }
        arsort($keywordWeightTotals);
        $keywordWeightTotals = array_slice($keywordWeightTotals, 0, SiteLink::TAGS_TO_PARSE_COUNT, true);
        return $keywordWeightTotals;
    }
}
