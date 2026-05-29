<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id'           => 'required|exists:users,id',
            'type'              => 'required|in:motorcycle,car',
            'service_id'        => 'required_if:type,car|nullable|exists:services,id',
            'rental_package_id' => 'required|exists:rental_packages,id',
            'start_rent'        => 'required|date|after_or_equal:today',
            'time_pickup'       => 'required|date_format:H:i',
            'vehicle_id'        => 'required|exists:vehicles,id',
            'address_pickup'    => 'required|string|min:10|max:255',
            // Latitude & longitude are nullable for admin (map is optional in back-office)
            'latitude'          => 'nullable|numeric',
            'longitude'         => 'nullable|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required'           => 'Pemesan harus dipilih.',
            'user_id.exists'             => 'User yang dipilih tidak valid.',
            'type.required'              => 'Tipe kendaraan harus dipilih.',
            'type.in'                    => 'Tipe kendaraan tidak valid.',
            'service_id.required_if'     => 'Layanan harus dipilih untuk kendaraan mobil.',
            'service_id.exists'          => 'Layanan yang dipilih tidak valid.',
            'rental_package_id.required' => 'Paket sewa harus dipilih.',
            'rental_package_id.exists'   => 'Paket sewa yang dipilih tidak valid.',
            'start_rent.required'        => 'Tanggal mulai sewa harus dipilih.',
            'start_rent.date'            => 'Format tanggal mulai tidak valid.',
            'start_rent.after_or_equal'  => 'Tanggal mulai tidak boleh sebelum hari ini.',
            'time_pickup.required'       => 'Waktu pengambilan harus dipilih.',
            'time_pickup.date_format'    => 'Format waktu pengambilan tidak valid (gunakan HH:mm).',
            'vehicle_id.required'        => 'Kendaraan harus dipilih.',
            'vehicle_id.exists'          => 'Kendaraan yang dipilih tidak valid.',
            'address_pickup.required'    => 'Alamat penjemputan harus diisi.',
            'address_pickup.min'         => 'Alamat penjemputan terlalu singkat (min 10 karakter).',
        ];
    }
}