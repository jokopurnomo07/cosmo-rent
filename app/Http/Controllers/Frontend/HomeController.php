<?php

namespace App\Http\Controllers\Frontend;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index(){

        $recommendationVehicle = Vehicle::with(['features','prices'])->inRandomOrder()->limit(3)->latest();
        $totalVehicle = Cache::remember('total_vehicle', 600, fn() => Vehicle::count());
        $totalUser = Cache::remember('total_users', 600, fn() => User::role('user')->count());

        return view('frontend.home', [
            'vehicles' => $recommendationVehicle,
            'totalVehicle' => $totalVehicle,
            'totalUser' => $totalUser,
        ]);
    }

    public function about(){
        return view('frontend.about');
    }

    public function contact(){
        return view('frontend.contact');
    }
}
