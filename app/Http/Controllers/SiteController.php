<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Site ;

class SiteController extends Controller
{
    //
    public function index(){
        $site = new Sile();
        $site->name = uniqid();
        
        $site->save();
        var_dump(Site::all());
        exit;
        
      
        
    }
}

