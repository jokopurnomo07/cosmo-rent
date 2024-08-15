@extends('layouts.admin.app')
@section('title', 'Ubah Data Kendaraan')

@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last"></div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item" aria-current="page">
                                Data Kendaraan
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Ubah Data Kendaraan</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <!-- // Basic multiple Column Form section start -->
        <section id="multiple-column-form">
            <div class="row match-height">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title">
                                Ubah Kendaraan
                            </h5>
                            <a href="{{ route('admin.vehicles.index') }}">
                                <button type="button" class="btn btn-danger rounded-pill">
                                    <i class="fas fa-arrow-circle-left"></i> Kembali
                                </button>
                            </a>
                        </div>
                        <div class="card-content">
                            <div class="card-body">
                                <form class="form" action="{{ route('admin.vehicles.update', ['id' => $vehicle->id]) }}" method="POST" data-parsley-validate enctype="multipart/form-data">
                                    @csrf

                                    <div class="row">
                                        <div class="col-md-6 col-12">
                                            <div class="form-group">
                                                <label for="type" class="form-label">Tipe Kendaraan</label>
                                                <select class="form-control" name="type" id="type" data-parsley-required="true">
                                                    <option value="">Pilih Tipe Kendaraan</option>
                                                    <option value="car" {{ $vehicle->type == "car" ? 'selected' : '' }}>Mobil</option>
                                                    <option value="motorcycle" {{ $vehicle->type == "motorcycle" ? 'selected' : '' }}>Motor</option>
                                                </select>
                                                @error('type')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <div class="form-group mandatory">
                                                <label for="name" class="form-label">Nama Kendaraan</label>
                                                <input type="text" id="name" class="form-control" placeholder="Nama Kendaraan" name="name" data-parsley-required="true" value="{{ $vehicle->name }}" />
                                                <p><small class="text-muted">Contoh: Toyota Camry 2020, BMW 3 Series 2021, dll.</small></p>
                                                @error('name')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <div class="form-group">
                                                <label for="brand" class="form-label">Merek Kendaraan</label>
                                                <input type="text" id="brand" class="form-control" placeholder="Merek Kendaraan" name="brand" data-parsley-required="true" value="{{ $vehicle->brand }}" />
                                                <p><small class="text-muted">Contoh: Toyota, Honda, BMW, Yamaha, Suzuki, dll.</small></p>
                                                @error('brand')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <div class="form-group">
                                                <label for="model" class="form-label">Model Kendaraan</label>
                                                <input type="text" id="model" class="form-control" placeholder="Model Kendaraan" name="model" data-parsley-required="true" value="{{ $vehicle->model }}" />
                                                <p><small class="text-muted">Contoh: Camry, Accord, 3 Series, NMax, GSX-R, dll.</small></p>
                                                @error('model')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <div class="form-group">
                                                <label for="year" class="form-label">Tahun Kendaraan</label>
                                                <input type="text" id="year" class="form-control" name="year" placeholder="Tahun Kendaraan" data-parsley-required="true" data-parsley-type="number" value="{{ $vehicle->year }}" />
                                                <p><small class="text-muted">Contoh: 2020, 2018, 2021, dll.</small></p>
                                                @error('year')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <div class="form-group">
                                                <label for="transmission" class="form-label">Transmisi</label>
                                                <select class="form-control" name="transmission" id="transmission" data-parsley-required="true">
                                                    <option value="">Pilih Transmisi Kendaraan</option>
                                                    <option value="manual" {{ $vehicle->transmission == "manual" ? 'selected' : '' }}>Manual</option>
                                                    <option value="automatic" {{ $vehicle->transmission == "automatic" ? 'selected' : '' }}>Otomatis / Matic</option>
                                                    {{-- <option value="both">Keduanya</option> --}}
                                                </select>
                                                @error('transmission')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <div class="form-group">
                                                <label for="fuel" class="form-label">Bahan Bakar</label>
                                                <input type="text" id="fuel" class="form-control" name="fuel" placeholder="Bahan Bakar Kendaraan" data-parsley-required="true" value="{{ $vehicle->fuel }}" />
                                                @error('fuel')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <div class="form-group">
                                                <label for="license_plate_number" class="form-label">Plat Nomor Kendaraan</label>
                                                <input type="text" id="license_plate_number" class="form-control" name="license_plate_number" placeholder="Plat Nomor Kendaraan" data-parsley-required="true" value="{{ $vehicle->registration_number }}" />
                                                @error('license_plate_number')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <div class="form-group">
                                                <label for="capacity" class="form-label">Kapasitas Penumpang</label>
                                                <input type="text" id="capacity" class="form-control" name="capacity" placeholder="Kapasitas Penumpang" data-parsley-required="true" value="{{ $vehicle->capacity }}" />
                                                @error('capacity')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                
                                    <div class="row">
                                        @foreach ($rentalPackages as $item)
                                            <div class="col-md-6 col-12">
                                                <div class="form-group">
                                                    <label for="price_{{ $item->duration_hours }}_hours" class="form-label">Harga per {{ $item->name }}</label>
                                                    @php
                                                        $durationHoursKey = 'price_' . $item->duration_hours . '_hours';
                                                        $price = isset($vehicle->prices[$durationHoursKey]) ? $vehicle->prices[$durationHoursKey] : '';
                                                    @endphp
                                                    <input type="text" id="price_{{ $item->duration_hours }}_hours" class="form-control numeral-mask-{{ $item->duration_hours }}" name="price_{{ $item->duration_hours }}_hours" placeholder="Harga per {{ $item->name }}" data-parsley-required="true" value="{{ old('price_' . $item->duration_hours . '_hours', $price) }}" />
                                                    @error('price_' . $item->duration_hours . '_hours')
                                                        <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                
                                    <div class="col-md-12 col-12 mt-3">
                                        <div class="form-group">
                                            <label for="checkbox-container" class="form-label">Fitur yang Disediakan</label>
                                            <p><small id="feature-notice" class="form-text text-muted">Pilih tipe kendaraan terlebih dahulu untuk menampilkan fitur yang tersedia.</small></p>
                                            <!-- Checkbox options will be loaded here -->
                                            <div class="row" id="checkbox-container"></div>
                                        </div>
                                    </div>
                                
                                    <div class="row">
                                        <div class="col-md-12 col-12">
                                            <div class="form-group">
                                                <label for="description" class="form-label">Deskripsi</label>
                                                <textarea name="description" id="description" cols="30" rows="3" class="form-control" data-parsley-required="true">{{ $vehicle->description }}</textarea>
                                                @error('description')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 col-12">
                                            <div class="form-group">
                                                <label for="image" class="form-label">Foto Kendaraan</label>
                                                <input type="file" class="image-preview-filepond" name="image_vehicle" id="image" data-parsley-required="true">
                                                @if ( $vehicle->vehicle_images != null )
                                                    <img id="image-preview" src="{{ asset('storage/'. $vehicle->vehicle_images) }}" alt="Image Preview" style="display: block; margin-top: 10px; max-width: 30%; height: auto;">
                                                @endif
                                                @error('image')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>                                    
                                    <div class="row">
                                        <div class="col-12 d-flex justify-content-end">
                                            <button class="btn btn-primary me-1 mb-1">Submit</button>
                                            <button type="reset" class="btn btn-light-secondary me-1 mb-1">Reset</button>
                                        </div>
                                    </div>
                                </form>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
@push('scripts')
    <script>
        $(document).ready(function () {

            var vehicleFeatures = @json($vehicle->features->pluck('id')->toArray());
            $('#type').on('change', function() {
                var type = $(this).val();
                var featureNotice = $('#feature-notice');
                
                if (type) {
                    featureNotice.text('Fitur yang tersedia untuk tipe kendaraan ini:').css('color', 'black');

                    $.ajax({
                        url: "{{ route('admin.vehicles.checkbox') }}",
                        type: 'GET',
                        data: { type: type },
                        success: function(response) {
                            var checkboxContainer = $('#checkbox-container');
                            checkboxContainer.empty(); // Clear previous checkboxes

                            response.data.forEach(function(option) {
                                // Format label
                                var formattedLabel = option.name
                                    .replace(/_/g, ' ') // Replace underscores with spaces
                                    .replace(/\b\w/g, function(l) { return l.toUpperCase(); }); // Capitalize the first letter of each word

                                var isChecked = '{{ json_encode($vehicle->features->pluck("id")->toArray()) }}'.includes(option.id.toString()) ? 'checked' : '';

                                checkboxContainer.append(`
                                    <div class="col-md-4 col-12 mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input form-check-success form-check-glow" id="feature_${option.id}" name="features[]" value="${option.id}" ${isChecked}>
                                            <label class="form-check-label" for="feature_${option.id}">${formattedLabel}</label>
                                        </div>
                                    </div>
                                `);
                            });
                        },
                        error: function(xhr) {
                            console.error('Error fetching checkbox options:', xhr);
                        }
                    });
                } else {
                    featureNotice.text('Pilih tipe kendaraan terlebih dahulu untuk menampilkan fitur yang tersedia.').css('color', 'red');
                    $('#checkbox-container').empty(); // Clear checkboxes if no type selected
                }
            });

            // Trigger the change event on page load to preselect checkboxes based on the current vehicle type
            $('#type').trigger('change');

            const classNames = ['numeral-mask-4', 'numeral-mask-12', 'numeral-mask-24'];

            classNames.forEach(className => {
                const elements = document.querySelectorAll(`.${className}`);
                if (elements.length) {
                    elements.forEach(element => {
                        new Cleave(element, {
                            numeral: true,
                            numeralThousandsGroupStyle: 'thousand'
                        });
                    });
                }
            });


        });
    </script>
@endpush