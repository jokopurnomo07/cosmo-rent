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
                            <li class="breadcrumb-item active" aria-current="page">
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
                                Ubah Data Kendaraan
                            </h5>
                            <a href="{{ route('admin.vehicles.index') }}">
                                <button type="button" class="btn btn-danger rounded-pill">
                                    <i class="fas fa-arrow-circle-left"></i> Kembali
                                </button>
                            </a>
                        </div>
                        <div class="card-content">
                            <div class="card-body">
                                <form class="form" data-parsley-validate>
                                    <div class="row">
                                        <div class="col-md-6 col-12">
                                            <div class="form-group">
                                                <label for="type_vehicle" class="form-label">Tipe Kendaraan</label>
                                                <select class="form-control" name="type_vehicle" id="type_vehicle">
                                                    <option value="">Pilih Tipe Kendaraan</option>
                                                    <option value="car">Mobil</option>
                                                    <option value="motorcycle">Motor</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <div class="form-group mandatory">
                                                <label for="name_vehicle" class="form-label">Nama Kendaraan</label>
                                                <input type="text" id="name_vehicle" class="form-control"
                                                    placeholder="Nama Kendaraan" name="name_vehicle"
                                                    data-parsley-required="true" />
                                                <p><small class="text-muted">Contoh: Toyota Camry 2020, BMW 3 Series 2021, dll.</small></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <div class="form-group">
                                                <label for="brand_vehicle" class="form-label">Merek Kendaraan</label>
                                                <input type="text" id="brand_vehicle" class="form-control"
                                                    placeholder="Merek Kendaraan" name="brand_vehicle"
                                                    data-parsley-required="true" />
                                                
                                                <p><small class="text-muted">Contoh: Toyota, Honda, BMW, Yamaha, Suzuki, dll.</small></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <div class="form-group">
                                                <label for="model_vehicle" class="form-label">Model Kendaraan</label>
                                                <input type="text" id="model_vehicle" class="form-control"
                                                placeholder="Model Kendaraan" name="model_vehicle" data-parsley-required="true" />
                                                
                                                <p><small class="text-muted">Contoh: Camry, Accord, 3 Series, NMax, GSX-R, dll.</small></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <div class="form-group">
                                                <label for="year_vehicle" class="form-label">Tahun Kendaraan</label>
                                                <input type="text" id="year_vehicle" class="form-control"
                                                    name="year_vehicle" placeholder="Tahun Kendaraan"
                                                    data-parsley-required="true" data-parsley-type="number" />
                                                    
                                                <p><small class="text-muted">Contoh: 2020, 2018, 2021, dll.</small></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <div class="form-group">
                                                <label for="transmition_vehicle" class="form-label">Transmisi</label>
                                                <select class="form-control" name="transmition_vehicle" id="transmition_vehicle">
                                                    <option value="">Pilih Transmisi Kendaraan</option>
                                                    <option value="manual">Manual</option>
                                                    <option value="automatic">Otomatis / Matic</option>
                                                    <option value="both">Keduanya</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <div class="form-group">
                                                <label for="fuel_vehicle" class="form-label">Bahan Bakar</label>
                                                <input type="text" id="fuel_vehicle" class="form-control"
                                                    name="fuel_vehicle" placeholder="Bahan Bakar Kendaraan"
                                                    data-parsley-required="true"  />
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <div class="form-group">
                                                <label for="license_plate_number" class="form-label">Plat Nomor Kendaraan</label>
                                                <input type="text" id="license_plate_number" class="form-control"
                                                    name="license_plate_number" placeholder="Plat Nomor Kendaraan"
                                                    data-parsley-required="true"  />
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <div class="form-group">
                                                <label for="capacity" class="form-label">Kapasitas Penumpang</label>
                                                <input type="text" id="capacity" class="form-control"
                                                    name="capacity" placeholder="Kapasitas Penumpang"
                                                    data-parsley-required="true"  />
                                            </div>
                                        </div>

                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 col-12">
                                            <div class="form-group">
                                                <label for="price_4_hours" class="form-label">Harga per 4 Jam</label>
                                                <input type="text" id="price_4_hours" class="form-control"
                                                    name="price_4_hours" placeholder="Harga per 4 Jam"
                                                    data-parsley-required="true"  />
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <div class="form-group">
                                                <label for="price_12_hours" class="form-label">Harga per 12 Jam</label>
                                                <input type="text" id="price_12_hours" class="form-control"
                                                    name="price_12_hours" placeholder="Harga per 12 Jam"
                                                    data-parsley-required="true"  />
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <div class="form-group">
                                                <label for="price_24_hours" class="form-label">Harga per 24 Jam</label>
                                                <input type="text" id="price_24_hours" class="form-control"
                                                    name="price_24_hours" placeholder="Harga per 24 Jam"
                                                    data-parsley-required="true"  />
                                            </div>
                                        </div>
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
                                                <textarea name="description" id="description" cols="30" rows="3" class="form-control"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 col-12">
                                            <div class="form-group">
                                                <label for="image_vehicle" class="form-label">Foto Kendaraan</label>
                                                <input type="file" class="image-preview-filepond" name="image_vehicle" id="image_vehicle" >
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 d-flex justify-content-end">
                                            <button type="submit" class="btn btn-primary me-1 mb-1">
                                                Submit
                                            </button>
                                            <button type="reset" class="btn btn-light-secondary me-1 mb-1">
                                                Reset
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- // Basic multiple Column Form section end -->
    </div>
@endsection
@push('scripts')
    <script>
        $(document).ready(function () {

            $('#type_vehicle').on('change', function() {
                var type = $(this).val();
                var featureNotice = $('#feature-notice');
                
                if (type) {
                    featureNotice.text('Fitur yang tersedia untuk tipe kendaraan ini:').css('color', 'black');

                    $.ajax({
                        url: "{{ route('admin.vehicles.checkbox') }}",
                        type: 'GET',
                        data: { type_vehicle: type },
                        success: function(response) {
                            var checkboxContainer = $('#checkbox-container');
                            checkboxContainer.empty(); // Clear previous checkboxes

                            response.data.forEach(function(option) {
                                // Format label
                                var formattedLabel = option.name
                                    .replace(/_/g, ' ') // Replace underscores with spaces
                                    .replace(/\b\w/g, function(l) { return l.toUpperCase(); }); // Capitalize the first letter of each word

                                checkboxContainer.append(`
                                    <div class="col-md-4 col-12 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input form-check-success form-check-glow" type="checkbox" id="${option.id}" name="${option.name}" value="${option.id}">
                                            <label class="form-check-label" for="${option.id}">
                                                ${formattedLabel}
                                            </label>
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

        });
    </script>
@endpush