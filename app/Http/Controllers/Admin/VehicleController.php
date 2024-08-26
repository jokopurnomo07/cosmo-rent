<?php

namespace App\Http\Controllers\Admin;

use App\Models\Feature;
use App\Models\Vehicle;
use Illuminate\Support\Str;
use App\Models\Notification;
use App\Models\VehiclePrice;
use Illuminate\Http\Request;
use App\Models\RentalPackage;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\VehicleRequest;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\UpdateVehicleRequest;

class VehicleController extends Controller
{
    public function index(){
        $vehicles = Vehicle::all();
        $notifications = Notification::where('is_read', false)->get();
        return view('admin.vehicles.index', ['vehicles' => $vehicles, 'notifications' => $notifications]);
    }

    public function create(){
        $rentalPackages = RentalPackage::select('id', 'name', 'duration_hours')
            ->orderBy('duration_hours', 'ASC')
            ->get();

        return view('admin.vehicles.create', [
            'rentalPackages' => $rentalPackages,
        ]);
    }

    public function show($id){
        $vehicle = Vehicle::with(['features', 'prices'])->findOrFail($id); 
        return view('admin.vehicles.show', compact('vehicle'));
    }

    public function store(VehicleRequest $request)
    {
        DB::beginTransaction();

        try {
            // Store Vehicle Information
            $vehicle = Vehicle::create([
                'name' => ucwords($request->name),
                'type' => $request->type,
                'brand' => ucwords($request->brand),
                'model' => ucwords($request->model),
                'year' => $request->year,
                'transmission' => $request->transmission,
                'fuel' => ucwords($request->fuel),
                'registration_number' => Str::upper($request->license_plate_number),
                'capacity' => $request->capacity,
                'description' => $request->description,
                'status' => 'available',
            ]);
            
            $price4Hours = str_replace(',', '', $request->input('price_4_hours'));
            $price12Hours = str_replace(',', '', $request->input('price_12_hours'));
            $price1Day = str_replace(',', '', $request->input('price_24_hours'));

            // Convert cleaned strings to numbers
            $price4Hours = (float) $price4Hours;
            $price12Hours = (float) $price12Hours;
            $price1Day = (float) $price1Day;

            VehiclePrice::create([
                'vehicle_id' => $vehicle->id,
                'price_4_hours' => $price4Hours,
                'price_12_hours' => $price12Hours,
                'price_24_hours' => $price1Day,
            ]);

            // Store Vehicle Features
            if ($request->has('features')) {
                $vehicle->features()->attach($request->features);
            }

            // Handle Image Upload
            if ($request->hasFile('image_vehicle')) {
                $imagePath = $request->file('image_vehicle')->store('vehicles', 'public');
                $vehicle->update(['vehicle_images' => $imagePath]);
            }

            DB::commit();

            return redirect()->route('admin.vehicles.index')->with('success', true);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to add vehicle. Please try again.');
        }
    }

    public function edit($id){
        $rentalPackages = RentalPackage::select('id', 'name', 'duration_hours')
            ->orderBy('duration_hours', 'ASC')
            ->get();
        $vehicle = Vehicle::with(['features', 'prices'])->findOrFail($id); 
        return view('admin.vehicles.edit', [
            'vehicle' => $vehicle,
            'rentalPackages' => $rentalPackages,
        ]);
    }

    public function update(UpdateVehicleRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $vehicle = Vehicle::findOrFail($id);

            // Update Vehicle Information
            $vehicle->update([
                'name' => ucwords($request->name),
                'type' => $request->type,
                'brand' => ucwords($request->brand),
                'model' => ucwords($request->model),
                'year' => $request->year,
                'transmission' => $request->transmission,
                'fuel' => ucwords($request->fuel),
                'registration_number' => Str::upper($request->license_plate_number),
                'capacity' => $request->capacity,
                'description' => $request->description,
                'status' => 'available', // Assuming you may update the status
            ]);

            // Update Vehicle Prices
            $price4Hours = (float) str_replace(',', '', $request->input('price_4_hours'));
            $price12Hours = (float) str_replace(',', '', $request->input('price_12_hours'));
            $price1Day = (float) str_replace(',', '', $request->input('price_24_hours'));

            $vehiclePrice = VehiclePrice::updateOrCreate(
                ['vehicle_id' => $vehicle->id],
                [
                    'price_4_hours' => $price4Hours,
                    'price_12_hours' => $price12Hours,
                    'price_24_hours' => $price1Day,
                ]
            );

            // Sync vehicle features
            $features = $request->input('features', []);
            $vehicle->features()->sync($features);

            // Handle Image Upload
            if ($request->hasFile('image_vehicle')) {
                // Delete old image if exists
                if ($vehicle->vehicle_images) {
                    Storage::disk('public')->delete($vehicle->vehicle_images);
                }

                // Store new image
                $imagePath = $request->file('image_vehicle')->store('vehicles', 'public');
                $vehicle->update(['vehicle_images' => $imagePath]);
            }

            DB::commit();

            return redirect()->route('admin.vehicles.index')->with('success', 'Vehicle updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Failed to update vehicle. Please try again.');
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $vehicle = Vehicle::findOrFail($id);

            VehiclePrice::where('vehicle_id', $vehicle->id)->delete();
            if ($vehicle->vehicle_images) {
                Storage::disk('public')->delete($vehicle->vehicle_images);
            }

            // Detach features
            $vehicle->features()->detach();

            // Delete the vehicle
            $vehicle->delete();

            DB::commit();

            return response()->json([
                'success' => true,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false
            ]);
        }
    }

    public function getOptions(Request $request){
        $features = Feature::select('id', 'type', 'name')->whereIn('type', ['both', $request->type])->get();
        return response()->json([
            'status' => 'success',
            'data' => $features,
        ]);
    }
}
