<?php
namespace App\Services\Contracts;

use App\Site;
use App\SiteLink;
use nokogiri;

interface SiteLinkService
{
    public function validateUrl(String $url, String $siteUrl);

    public function createLink(string $url, string $baseURI, Site $site, int $status=null);

    public function analyzePage(SiteLink $siteLink);

    /**
     *  Парсит ссылки на странице
     */
    public function parseLinks(SiteLink $siteLink, nokogiri $content);

    /**
     *  Парсит ключевые слова на странице
     */
    public function parseKeywords(nokogiri $content);

    public function findSimilar(SiteLink $siteLink);
}