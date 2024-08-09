<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function index(){
        return view('admin.reservation.index');
    }

    public function create(){
        return view('admin.reservation.create');
    }

    public function edit($id){
        return view('admin.reservation.edit');
    }
}
