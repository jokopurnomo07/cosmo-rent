@extends('layouts.admin.app')
@section('title', 'Tambah Reservasi')

@push('styles')
{{-- Leaflet CSS — dibutuhkan oleh mymaps.js --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last"></div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.reservations.index', 'pending') }}">Data Reservasi</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Tambah Reservasi</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Terdapat kesalahan pada form:</strong>
            <ul class="mb-0 mt-1">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <section id="reservation-form">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Tambah Reservasi</h5>
                        <a href="{{ route('admin.reservations.index', 'pending') }}">
                            <button type="button" class="btn btn-danger rounded-pill">
                                <i class="fas fa-arrow-circle-left me-1"></i> Kembali
                            </button>
                        </a>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('admin.reservations.store') }}" method="POST">
                            @csrf

                            {{-- ══════════════════════════════════════════════════════ --}}
                            {{-- DETAIL PESANAN                                         --}}
                            {{-- ══════════════════════════════════════════════════════ --}}
                            <h6 class="text-uppercase text-muted fw-bold mb-3">Detail Pesanan</h6>
                            <hr>

                            <div class="row">

                                {{-- Tipe Kendaraan --}}
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="form-label">
                                            Tipe Kendaraan <span class="text-danger">*</span>
                                        </label>
                                        <select name="type" class="form-control" id="tipe_kendaraan" required>
                                            <option value="">Pilih Tipe Kendaraan</option>
                                            <option value="motorcycle" {{ old('type') === 'motorcycle' ? 'selected' : '' }}>
                                                Motor
                                            </option>
                                            <option value="car" {{ old('type') === 'car' ? 'selected' : '' }}>
                                                Mobil
                                            </option>
                                        </select>
                                        @error('type')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Layanan (hanya tampil untuk mobil) --}}
                                <div class="col-md-6 col-12" id="service-group" style="display:none;">
                                    <div class="form-group">
                                        <label class="form-label">
                                            Layanan <span class="text-danger">*</span>
                                        </label>
                                        <select name="service_id" class="form-control" id="service">
                                            <option value="">Pilih Layanan</option>
                                            @foreach ($services as $item)
                                                <option value="{{ $item->id }}"
                                                    {{ old('service_id') == $item->id ? 'selected' : '' }}>
                                                    {{ $item->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('service_id')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Paket Sewa --}}
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="form-label">
                                            Paket Sewa <span class="text-danger">*</span>
                                        </label>
                                        <select name="rental_package_id" class="form-control" id="paket_sewa" required>
                                            <option value="">Pilih Paket Sewa</option>
                                            @foreach ($rentalPackages as $item)
                                                <option value="{{ $item->id }}"
                                                        data-duration-hours="{{ $item->duration_hours }}"
                                                        {{ old('rental_package_id') == $item->id ? 'selected' : '' }}>
                                                    {{ $item->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('rental_package_id')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Pilih Kendaraan (AJAX Select2, difilter berdasarkan tipe) --}}
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="form-label">
                                            Pilih Kendaraan <span class="text-danger">*</span>
                                        </label>
                                        <select name="vehicle_id" class="form-control" id="jenis_kendaraan" required>
                                            <option value="">Cari kendaraan...</option>
                                        </select>
                                        <small class="text-muted">Pilih tipe kendaraan terlebih dahulu.</small>
                                        @error('vehicle_id')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Tanggal Mulai --}}
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="form-label">
                                            Tanggal Mulai <span class="text-danger">*</span>
                                        </label>
                                        <input type="date"
                                               name="start_rent"
                                               id="start_date"
                                               class="form-control @error('start_rent') is-invalid @enderror"
                                               min="{{ date('Y-m-d') }}"
                                               value="{{ old('start_rent') }}"
                                               required>
                                        @error('start_rent')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Waktu Pengambilan --}}
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="form-label">
                                            Waktu Pengambilan <span class="text-danger">*</span>
                                        </label>
                                        <input type="time"
                                               name="time_pickup"
                                               id="time_pickup"
                                               class="form-control @error('time_pickup') is-invalid @enderror"
                                               value="{{ old('time_pickup') }}"
                                               required>
                                        @error('time_pickup')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">
                                            Waktu pengambilan kendaraan atau pengantaran ke lokasi pemesan.
                                        </small>
                                    </div>
                                </div>

                                {{-- Estimasi Tanggal Selesai (readonly, dihitung otomatis oleh JS) --}}
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="form-label">Estimasi Tanggal Selesai</label>
                                        <input type="date" id="end_date" class="form-control" readonly>
                                    </div>
                                </div>

                                {{-- Estimasi Waktu Selesai (readonly, dihitung otomatis oleh JS) --}}
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="form-label">Estimasi Waktu Selesai</label>
                                        <input type="time" id="end_time" class="form-control" readonly>
                                        <small class="text-muted">
                                            Dihitung otomatis dari waktu pengambilan + durasi paket.
                                        </small>
                                    </div>
                                </div>

                            </div>{{-- /.row --}}

                            {{-- Peta Penjemputan --}}
                            <div class="row mb-3">
                                <div class="col-12">
                                    <label class="form-label">Lokasi Penjemputan</label>
                                    <div id="map" style="min-height:300px; border-radius:8px; border:1px solid #dee2e6;"></div>
                                    <small class="text-muted">Klik pada peta untuk menentukan titik penjemputan.</small>
                                </div>
                            </div>

                            {{-- Alamat Penjemputan --}}
                            <div class="form-group">
                                <label class="form-label">
                                    Alamat Penjemputan <span class="text-danger">*</span>
                                </label>
                                <textarea name="address_pickup"
                                          id="alamat"
                                          class="form-control @error('address_pickup') is-invalid @enderror"
                                          rows="3"
                                          required>{{ old('address_pickup') }}</textarea>
                                @error('address_pickup')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    Sesuaikan alamat jika berbeda dari yang terisi otomatis dari peta.
                                </small>
                            </div>

                            {{-- Hidden: koordinat dari peta --}}
                            <input type="hidden" id="lat" name="latitude"  value="{{ old('latitude') }}">
                            <input type="hidden" id="lng" name="longitude" value="{{ old('longitude') }}">

                            {{-- ══════════════════════════════════════════════════════ --}}
                            {{-- INFORMASI PEMESAN                                      --}}
                            {{-- ══════════════════════════════════════════════════════ --}}
                            <h6 class="text-uppercase text-muted fw-bold mt-4 mb-3">Informasi Pemesan</h6>
                            <hr>

                            <div class="row">

                                {{-- Cari User Terdaftar (AJAX Select2) --}}
                                <div class="col-md-12 col-12">
                                    <div class="form-group">
                                        <label class="form-label">
                                            Pemesan (User Terdaftar) <span class="text-danger">*</span>
                                        </label>
                                        <select name="user_id" class="form-control" id="user_select" required>
                                            <option value="">Cari nama atau email pemesan...</option>
                                        </select>
                                        @error('user_id')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">
                                            Ketik nama atau email untuk mencari user terdaftar.
                                        </small>
                                    </div>
                                </div>

                                {{-- No. Telepon (diisi otomatis saat user dipilih) --}}
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="form-label">No. Telepon Pemesan</label>
                                        <input type="text"
                                               id="user_phone"
                                               name="no_hp_guest"
                                               class="form-control"
                                               placeholder="Terisi otomatis setelah memilih pemesan"
                                               readonly>
                                        <small class="text-muted">
                                            Terisi otomatis dari data user yang dipilih.
                                        </small>
                                    </div>
                                </div>

                            </div>{{-- /.row --}}

                            {{-- Submit --}}
                            <div class="row mt-3">
                                <div class="col-12 d-flex justify-content-end gap-2">
                                    <button type="reset" class="btn btn-light-secondary">Reset</button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save me-1"></i> Simpan Reservasi
                                    </button>
                                </div>
                            </div>

                        </form>
                    </div>{{-- /.card-body --}}
                </div>{{-- /.card --}}
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {

    // ─────────────────────────────────────────────────────────────────────────
    // 1. SELECT2 — KENDARAAN (AJAX, difilter berdasarkan tipe kendaraan)
    // ─────────────────────────────────────────────────────────────────────────
    function initVehicleSelect2() {
        if ($("#jenis_kendaraan").data('select2')) {
            $("#jenis_kendaraan").select2('destroy');
        }
        $("#jenis_kendaraan").select2({
            theme: "bootstrap4",
            placeholder: "Cari kendaraan...",
            ajax: {
                url: "{{ route('admin.reservations.search-vehicle') }}",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q:            params.term,
                        vehicle_type: $('#tipe_kendaraan').val(),
                        _token:       "{{ csrf_token() }}"
                    };
                },
                processResults: function (data) {
                    return { results: data.items };
                },
                cache: true
            }
        });
    }
    initVehicleSelect2();

    // ─────────────────────────────────────────────────────────────────────────
    // 2. SELECT2 — USER (AJAX, cari berdasarkan nama / email)
    // ─────────────────────────────────────────────────────────────────────────
    $("#user_select").select2({
        theme: "bootstrap4",
        placeholder: "Cari nama atau email...",
        minimumInputLength: 1,
        ajax: {
            url: "{{ route('admin.reservations.search-user') }}",
            dataType: 'json',
            delay: 300,
            data:           function (params) { return { q: params.term, _token: "{{ csrf_token() }}" }; },
            processResults: function (data)   { return { results: data.items }; },
            cache: true
        }
    });

    // Auto-isi nomor telepon saat user dipilih
    $('#user_select').on('select2:select', function (e) {
        $('#user_phone').val(e.params.data.phone || '');
    });

    // Kosongkan telepon jika user di-clear
    $('#user_select').on('select2:unselect', function () {
        $('#user_phone').val('');
    });

    // ─────────────────────────────────────────────────────────────────────────
    // 3. TOGGLE LAYANAN — hanya tampil untuk tipe Mobil
    // ─────────────────────────────────────────────────────────────────────────
    function toggleService() {
        if ($('#tipe_kendaraan').val() === 'car') {
            $('#service-group').show();
            $('#service').prop('required', true);
        } else {
            $('#service-group').hide();
            $('#service').val('').prop('required', false);
        }
    }
    toggleService(); // jalankan sekali saat load (untuk old() value)

    $('#tipe_kendaraan').on('change', function () {
        toggleService();
        // Reset pilihan kendaraan dan reinit Select2 agar hasil AJAX ikut tipe baru
        $("#jenis_kendaraan").val(null).trigger('change');
        initVehicleSelect2();
        updateEstimasi();
    });

    // ─────────────────────────────────────────────────────────────────────────
    // 4. KALKULASI ESTIMASI SELESAI
    //    Logika sama persis dengan frontend:
    //    end_datetime = (start_rent + time_pickup) + duration_hours dari paket
    // ─────────────────────────────────────────────────────────────────────────
    function updateEstimasi() {
        var startDate = $('#start_date').val();
        var timePick  = $('#time_pickup').val();
        var durHours  = parseInt($('#paket_sewa option:selected').data('duration-hours'));

        if (!startDate || !timePick || !durHours) {
            $('#end_date').val('');
            $('#end_time').val('');
            return;
        }

        var startDT = new Date(startDate + 'T' + timePick + ':00');
        var endDT   = new Date(startDT.getTime() + durHours * 3600 * 1000);

        var endDateStr =
            endDT.getFullYear() + '-' +
            String(endDT.getMonth() + 1).padStart(2, '0') + '-' +
            String(endDT.getDate()).padStart(2, '0');

        var endTimeStr =
            String(endDT.getHours()).padStart(2, '0') + ':' +
            String(endDT.getMinutes()).padStart(2, '0');

        $('#end_date').val(endDateStr);
        $('#end_time').val(endTimeStr);
    }

    $('#start_date, #time_pickup, #paket_sewa').on('change', updateEstimasi);

    // ─────────────────────────────────────────────────────────────────────────
    // 5. SESSION FLASH
    // ─────────────────────────────────────────────────────────────────────────
    @if (session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: "{{ session('success') }}",
            timer: 2500,
            showConfirmButton: false
        });
    @endif

    @if (session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: "{{ session('error') }}"
        });
    @endif
});
</script>
{{--
    Leaflet JS — harus sebelum mymaps.js karena mymaps.js butuh global `L`.
    simple-datatables crash karena admin layout mencoba init DataTable di semua
    halaman, padahal di sini tidak ada tabel. Tabel dummy hidden di bawah
    mencegah error itu tanpa mempengaruhi tampilan.
--}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="{{ asset('frontend/js/mymaps.js') }}"></script>

{{-- Dummy table: mencegah simple-datatables crash karena tidak ada #table1 --}}
<table id="table1" style="display:none;"><thead><tr><th></th></tr></thead><tbody></tbody></table>
@endpush