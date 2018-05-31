<?php
namespace App\Services\Contracts;

use App\Site;
use Illuminate\Support\Collection;

interface SiteService
{
    public function parse(Site $site);

    public function parseAll();
}