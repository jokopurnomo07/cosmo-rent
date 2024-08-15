<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Feature;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class VehicleController extends Controller
{
    public function index(){
        $vehicles = Vehicle::with(['features','prices'])->paginate(6);
        return view('frontend.our_armada', ['vehicles' => $vehicles]);
    }

    public function show($id){
        
        $vehicle = Vehicle::with(['features','prices'])->findOrFail($id);
        $recommendationVehicle = Vehicle::with(['features','prices'])->inRandomOrder()->limit(3)->get();
        $features = Feature::whereIn('type', ['both', $vehicle->type])->get()->toArray();
        return view('frontend.detail_armada', [
            'vehicle' => $vehicle,
            'features' => $features,
            'recommendation' => $recommendationVehicle,
        ]);
    }
}
