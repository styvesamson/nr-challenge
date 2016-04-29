<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\File ;

class FileController extends Controller
{
    //
    public function index(){
//        $file = new File();
//        $file->name = uniqid();
//        $file->save();
        var_dump(File::all());
        exit;
        
      
        
    }
}
