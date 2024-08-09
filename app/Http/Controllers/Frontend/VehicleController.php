<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index(){
        return view('frontend.our_armada');
    }

    public function show($id){
        return view('frontend.detail_armada');
    }
}
