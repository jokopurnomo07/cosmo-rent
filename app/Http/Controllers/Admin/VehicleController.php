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
use App\Models\Rental;

class VehicleController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::select('id', 'name', 'brand', 'model', 'year', 'status', 'type', 'vehicle_images')
            ->latest()
            ->paginate(10);

        $notifications = Notification::select('id', 'data', 'is_read', 'user_id', 'created_at')
            ->where('is_read', false)
            ->latest()
            ->paginate(10);

        return view('admin.vehicles.index', compact('vehicles', 'notifications'));
    }

    public function create()
    {
        $rentalPackages = RentalPackage::select('id', 'name', 'duration_hours')
            ->orderBy('duration_hours', 'ASC')
            ->get();

        $notifications = Notification::select('id', 'data', 'is_read', 'user_id', 'created_at')
            ->where('is_read', false)
            ->latest()
            ->paginate(10);

        return view('admin.vehicles.create', compact('rentalPackages', 'notifications'));
    }

    public function show($id)
    {
        $vehicle = Vehicle::with(['features', 'prices'])->findOrFail($id);
        return view('admin.vehicles.show', compact('vehicle'));
    }

    public function store(VehicleRequest $request)
    {
        DB::beginTransaction();

        try {
            $vehicle = Vehicle::create([
                'name'                => ucwords($request->name),
                'type'                => $request->type,
                'brand'               => ucwords($request->brand),
                'model'               => ucwords($request->model),
                'year'                => $request->year,
                'transmission'        => $request->transmission,
                'fuel'                => ucwords($request->fuel),
                'registration_number' => Str::upper($request->license_plate_number),
                'capacity'            => $request->capacity,
                'description'         => $request->description,
                'status'              => 'available',
            ]);

            $price4Hours  = (float) str_replace(',', '', $request->input('price_4_hours'));
            $price12Hours = (float) str_replace(',', '', $request->input('price_12_hours'));
            $price1Day    = (float) str_replace(',', '', $request->input('price_24_hours'));

            VehiclePrice::create([
                'vehicle_id'    => $vehicle->id,
                'price_4_hours' => $price4Hours,
                'price_12_hours'=> $price12Hours,
                'price_24_hours'=> $price1Day,
            ]);

            if ($request->has('features')) {
                $vehicle->features()->attach($request->features);
            }

            if ($request->hasFile('image_vehicle')) {
                $imagePath = $request->file('image_vehicle')->store('vehicles', 'public');
                $vehicle->update(['vehicle_images' => $imagePath]);
            }

            DB::commit();

            return redirect()->route('admin.vehicles.index')->with('success_create', true);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menambah kendaraan. Silakan coba lagi.');
        }
    }

    public function edit($id)
    {
        $rentalPackages = RentalPackage::select('id', 'name', 'duration_hours')
            ->orderBy('duration_hours', 'ASC')
            ->get();

        $vehicle = Vehicle::with(['features', 'prices'])->findOrFail($id);

        $notifications = Notification::select('id', 'data', 'is_read', 'user_id', 'created_at')
            ->where('is_read', false)
            ->latest()
            ->paginate(10);

        return view('admin.vehicles.edit', compact('vehicle', 'rentalPackages', 'notifications'));
    }

    public function update(UpdateVehicleRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $vehicle = Vehicle::findOrFail($id);

            $vehicle->update([
                'name'                => ucwords($request->name),
                'type'                => $request->type,
                'brand'               => ucwords($request->brand),
                'model'               => ucwords($request->model),
                'year'                => $request->year,
                'transmission'        => $request->transmission,
                'fuel'                => ucwords($request->fuel),
                'registration_number' => Str::upper($request->license_plate_number),
                'capacity'            => $request->capacity,
                'description'         => $request->description,
                'status'              => 'available',
            ]);

            $price4Hours  = (float) str_replace(',', '', $request->input('price_4_hours'));
            $price12Hours = (float) str_replace(',', '', $request->input('price_12_hours'));
            $price1Day    = (float) str_replace(',', '', $request->input('price_24_hours'));

            VehiclePrice::updateOrCreate(
                ['vehicle_id' => $vehicle->id],
                [
                    'price_4_hours'  => $price4Hours,
                    'price_12_hours' => $price12Hours,
                    'price_24_hours' => $price1Day,
                ]
            );

            $vehicle->features()->sync($request->input('features', []));

            if ($request->hasFile('image_vehicle')) {
                if ($vehicle->vehicle_images) {
                    Storage::disk('public')->delete($vehicle->vehicle_images);
                }
                $imagePath = $request->file('image_vehicle')->store('vehicles', 'public');
                $vehicle->update(['vehicle_images' => $imagePath]);
            }

            DB::commit();

            return redirect()->route('admin.vehicles.index')->with('success_update', 'Kendaraan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui kendaraan. Silakan coba lagi.');
        }
    }

    public function destroy($id)
    {
        $hasActive = Rental::where('vehicle_id', $id)
            ->whereIn('status', ['paid', 'ongoing'])
            ->exists();

        if ($hasActive) {
            return response()->json([
                'success' => false,
                'message' => 'Kendaraan sedang aktif disewa dan tidak dapat dihapus.',
            ]);
        }

        DB::beginTransaction();
        try {
            $vehicle = Vehicle::findOrFail($id);

            VehiclePrice::where('vehicle_id', $vehicle->id)->delete();

            if ($vehicle->vehicle_images) {
                Storage::disk('public')->delete($vehicle->vehicle_images);
            }

            $vehicle->features()->detach();
            $vehicle->delete();

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false]);
        }
    }

    public function getOptions(Request $request)
    {
        $features = Feature::select('id', 'type', 'name')
            ->whereIn('type', ['both', $request->type])
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $features,
        ]);
    }
}