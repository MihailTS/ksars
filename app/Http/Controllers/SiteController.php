<?php

namespace App\Http\Controllers;

use App\Site;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function index(){
        $sites = Site::all();
        return view('sites',['sites'=>$sites]);
    }
}
