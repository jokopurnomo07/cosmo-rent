<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    
    public function rules()
    {
        return [
            'type' => 'required|in:motorcycle,car',
            'service_id' => 'required|exists:services,id',
            'masa_sewa' => 'required|integer|min:1|max:30',
            'rental_package_id' => 'required|exists:rental_packages,id',
            'start_rent' => 'required|date|after_or_equal:today',
            'end_rent' => 'required|date|after:start_rent',
            'time_pickup' => 'required|date_format:H:i',
            'vehicle_id' => 'required|exists:vehicles,id',
            'address_pickup' => 'required|string|min:10|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'email_guest' => 'required|email|max:255',
            'nama_guest' => 'required|string|min:3|max:255',
            'no_hp_guest' => 'required|string|min:10|max:15',
        ];
    }

    public function messages()
    {
        return [
            'type.required' => 'Tipe kendaraan harus dipilih.',
            'service_id.required' => 'Layanan harus dipilih.',
            'masa_sewa.required' => 'Masa sewa harus dipilih.',
            'rental_package_id.required' => 'Paket sewa harus dipilih.',
            'start_rent.required' => 'Tanggal mulai sewa harus dipilih.',
            'end_rent.required' => 'Tanggal selesai sewa harus dipilih.',
            'time_pickup.required' => 'Waktu pengambilan harus dipilih.',
            'vehicle_id.required' => 'Jenis kendaraan harus dipilih.',
            'address_pickup.required' => 'Alamat penjemputan harus diisi.',
            'latitude.required' => 'Koordinat latitude harus diisi.',
            'longitude.required' => 'Koordinat longitude harus diisi.',
            'email_guest.required' => 'Email harus diisi.',
            'nama_guest.required' => 'Nama lengkap harus diisi.',
            'no_hp_guest.required' => 'Nomor telepon harus diisi.',
        ];
    }
}
