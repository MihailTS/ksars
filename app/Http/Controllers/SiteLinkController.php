<?php

namespace App\Http\Controllers;

use App\SiteLink;
use Illuminate\Http\Request;

class SiteLinkController extends Controller
{
    public function similar($id){
        $sl = SiteLink::find($id);
        $sl->findSimilar();
    }
}
