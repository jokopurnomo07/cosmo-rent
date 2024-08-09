<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use App\Models\RentalPackage;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index(){
        return view('admin.vehicles.index');
    }

    public function create(){
        $rentalPackages = RentalPackage::select('id', 'name', 'duration_hours')
            ->orderBy('duration_hours', 'ASC')
            ->get();

        return view('admin.vehicles.create', [
            'rentalPackages' => $rentalPackages,
        ]);
    }

    public function edit($id){
        return view('admin.vehicles.edit');
    }

    public function getOptions(Request $request){

        $features = Feature::select('id', 'type', 'name')->whereIn('type', ['both', $request->type_vehicle])->get();
        return response()->json([
            'status' => 'success',
            'data' => $features,
        ]);
    }
}
