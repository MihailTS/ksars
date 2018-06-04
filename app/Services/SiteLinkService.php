<?php
namespace App\Services;

use App\Keyword;
use App\SiteLink;
use App\Site;
use App\Services\Contracts\SiteLinkService as SiteLinkServiceContract;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use InvalidArgumentException;
use Wamania\Snowball\Russian;
use Malahierba\WordCounter\WordCounter;
use nokogiri;

class SiteLinkService implements SiteLinkServiceContract
{
    const TAGS_TO_PARSE_COUNT = 10;

    public function validateUrl(String $url, String $siteUrl)
    {
        if(SiteLinkService::isURLBelongsToSiteDomain($url,$siteUrl)){
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
    public function createLink(string $url, string $baseURI, Site $site, int $status=null)
    {
        $siteLink = null;
        if($validURL=$this->validateUrl($url, $site->url))
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
                    $keywordsArray = $this->parseKeywords($content);

                    Keyword::addFromArray($keywordsArray,$siteLink);
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
        $stemTextArr = array_slice($stemTextArr, 0, SiteLinkService::TAGS_TO_PARSE_COUNT, true);
        return $stemTextArr;
    }

    public function findSimilar(SiteLink $siteLink){
        echo "similar to link {$siteLink->url} are:<hr>";
        $siteID = $siteLink->site->id;
        $currentLinkID = $siteLink->id;
        $currentKeywords = $siteLink->keywords->pluck('name','position')->toArray();
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
        $keywordWeightTotals = array_slice($keywordWeightTotals, 0, 5, true);
        foreach($keywordWeightTotals as $linkID=>$weight){
            echo "<br>".SiteLink::find($linkID)->url." : ".$weight;
        }
    }
}