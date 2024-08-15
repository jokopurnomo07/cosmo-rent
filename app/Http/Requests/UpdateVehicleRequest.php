<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVehicleRequest extends FormRequest
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
            'type' => 'required|in:car,motorcycle',
            'name' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:' . date('Y'),
            'transmission' => 'required|in:automatic,manual',
            'fuel' => 'required|string|max:255',
            'license_plate_number' => 'required|string|max:255|unique:vehicles,registration_number,' .  $this->id,
            'capacity' => 'required|integer',
            'description' => 'nullable|string',
            'image_vehicle' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048', // Validasi file gambar
        ];
    }


    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => 'Nama kendaraan harus diisi.',
            'type.required' => 'Tipe kendaraan harus diisi.',
            'brand.required' => 'Brand kendaraan harus diisi.',
            'model.required' => 'Model kendaraan harus diisi.',
            'fuel.required' => 'Bahan bakar kendaraan harus diisi.',
            'year.required' => 'Tahun pembuatan kendaraan harus diisi.',
            'year.integer' => 'Tahun pembuatan kendaraan harus berupa angka.',
            'license_plate_number.required' => 'Nomor registrasi harus diisi.',
            'license_plate_number.unique' => 'Nomor registrasi sudah terdaftar.',
            'capacity.required' => 'Kapasitas kendaraan harus diisi.',
        ];
    }
}
