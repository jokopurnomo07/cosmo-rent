@extends('layouts.frontend.app')
@section('title', 'Pemesanan')

@section('content')

    <section class="hero-wrap hero-wrap-2 js-fullheight"
             style="background-image: url('{{ asset('frontend/images/bg_3.jpg') }}');"
             data-stellar-background-ratio="0.5">
        <div class="overlay"></div>
        <div class="container">
            <div class="row no-gutters slider-text js-fullheight align-items-end justify-content-start">
                <div class="col-md-9 ftco-animate pb-5">
                    <p class="breadcrumbs">
                        <span class="mr-2">
                            <a href="{{ route('home') }}">Home <i class="ion-ios-arrow-forward"></i></a>
                        </span>
                        <span>Pemesanan <i class="ion-ios-arrow-forward"></i></span>
                    </p>
                    <h1 class="mb-3 bread">Pemesanan</h1>
                </div>
            </div>
        </div>
    </section>

    @auth
    <section class="ftco-section contact-section">
        <div class="container">
            <div class="row d-flex mb-5 contact-info">
                <div class="col-md-12 block-9 mb-md-5">
                    <form action="{{ route('reservations.store') }}" method="POST" class="bg-light p-5 contact-form">
                        @csrf

                        <h3>DETAIL PESANAN</h3>
                        <hr>

                        <div class="row">
                            {{-- Tipe Kendaraan --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tipe Kendaraan <span class="text-danger">*</span></label>
                                    <select name="type" class="form-control" id="tipe_kendaraan" required>
                                        <option value="">Pilih Tipe Kendaraan</option>
                                        <option value="motorcycle" {{ !empty($vehicle) && $vehicle->type == 'motorcycle' ? 'selected' : '' }}>Motor</option>
                                        <option value="car"        {{ !empty($vehicle) && $vehicle->type == 'car'        ? 'selected' : '' }}>Mobil</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Layanan: hanya tampil untuk mobil --}}
                            <div class="col-md-6" id="service-group" style="display: none;">
                                <div class="form-group">
                                    <label>Layanan <span class="text-danger">*</span></label>
                                    <select name="service_id" class="form-control" id="service">
                                        <option value="">Pilih Layanan</option>
                                        @foreach ($services as $item)
                                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Paket Sewa --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Paket Sewa <span class="text-danger">*</span></label>
                                    <select name="rental_package_id" class="form-control" id="paket_sewa" required>
                                        <option value="">Pilih Paket Sewa</option>
                                        @foreach ($rentalPackages as $item)
                                            {{-- data-duration-hours dipakai JS untuk hitung end_date & end_time --}}
                                            <option value="{{ $item->id }}"
                                                    data-duration-hours="{{ $item->duration_hours }}">
                                                {{ $item->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Pilih Kendaraan --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Pilih Kendaraan <span class="text-danger">*</span></label>
                                    <select name="vehicle_id" class="form-control select2" id="jenis_kendaraan" required>
                                        <option value="">Cari kendaraan...</option>
                                        @foreach ($vehicles as $item)
                                            <option value="{{ $item->id }}" {{ !empty($vehicle) && $vehicle->id == $item->id ? 'selected' : '' }}>
                                                {{ $item->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Tanggal Mulai --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Mulai <span class="text-danger">*</span></label>
                                    <input type="date" name="start_rent" id="start_date" class="form-control"
                                           min="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>

                            {{-- Waktu Pengambilan --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Waktu Pengambilan / Pengantaran <span class="text-danger">*</span></label>
                                    <input type="time" name="time_pickup" id="time_pickup" class="form-control" required>
                                    <small class="text-muted">
                                        Waktu pengambilan kendaraan atau pengantaran ke lokasi kamu.
                                    </small>
                                </div>
                            </div>

                            {{-- Estimasi Selesai (readonly, dihitung otomatis) --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Estimasi Tanggal Selesai</label>
                                    <input type="date" name="end_rent" id="end_date" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Estimasi Waktu Selesai</label>
                                    <input type="time" name="end_time" id="end_time" class="form-control" readonly>
                                    <small class="text-muted">
                                        Dihitung otomatis berdasarkan waktu pengambilan dan durasi paket.
                                    </small>
                                </div>
                            </div>
                        </div>

                        {{-- Map --}}
                        <div class="row justify-content-center mb-3">
                            <div class="col-md-12">
                                <div id="map" class="bg-white" style="min-height: 300px; border-radius: 8px;"></div>
                            </div>
                        </div>

                        {{-- Alamat --}}
                        <div class="form-group">
                            <label>Alamat Penjemputan <span class="text-danger">*</span></label>
                            <textarea name="address_pickup" id="alamat" class="form-control"
                                      cols="30" rows="5" required></textarea>
                            <small class="text-muted">Jika alamat belum sesuai, mohon sesuaikan alamat tersebut.</small>
                        </div>
                        <input type="hidden" id="lat" name="latitude">
                        <input type="hidden" id="lng" name="longitude">

                        <h3 class="mt-4">INFORMASI PEMESAN</h3>
                        <hr>

                        {{-- Data diambil dari akun yang sedang login, tidak perlu diisi ulang --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="text" class="form-control" value="{{ Auth::user()->email }}" readonly disabled>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama Lengkap</label>
                                    <input type="text" class="form-control" value="{{ Auth::user()->name }}" readonly disabled>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Nomor Telepon <span class="text-danger">*</span></label>
                            <input type="text" name="no_hp_guest" class="form-control"
                                   placeholder="Masukkan Nomor Telepon"
                                   value="{{ Auth::user()->phone ?? '' }}" required>
                            <small class="text-muted">Nomor telepon aktif untuk konfirmasi reservasi.</small>
                        </div>

                        <div class="form-group mt-3">
                            <button type="submit" class="btn btn-primary btn-block">Pesan Sekarang</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    @else

    <section class="ftco-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 text-center">
                    <div class="alert alert-warning" role="alert">
                        <h3 class="alert-heading">Kamu perlu login!</h3>
                        <p>Silakan login untuk melakukan pemesanan.</p>
                        <a href="{{ route('login') }}" class="btn btn-primary">Login Sekarang</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @endauth

@endsection

@push('scripts')
    <script>
        $(document).ready(function () {

            // ─── Select2 kendaraan (AJAX, filter by tipe & status != rented) ───────────
            function initVehicleSelect2() {
                if ($("#jenis_kendaraan").data('select2')) {
                    $("#jenis_kendaraan").select2('destroy');
                }
                $("#jenis_kendaraan").select2({
                    theme: "bootstrap4",
                    ajax: {
                        url: "{{ route('reservations.search-vehicle') }}",
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

            // ─── Toggle layanan berdasarkan tipe kendaraan ───────────────────────────
            function toggleServiceField() {
                var tipe = $('#tipe_kendaraan').val();
                if (tipe === 'car') {
                    $('#service-group').show();
                    $('#service').prop('required', true);
                } else {
                    // Motor tidak punya layanan
                    $('#service-group').hide();
                    $('#service').val('').prop('required', false);
                }
            }

            toggleServiceField();

            $('#tipe_kendaraan').on('change', function () {
                toggleServiceField();
                // Reset dan reinit select2 kendaraan saat tipe berubah
                $("#jenis_kendaraan").val(null);
                initVehicleSelect2();
                // Recalculate estimasi
                updateEstimasi();
            });

            // ─── Kalkulasi estimasi selesai ──────────────────────────────────────────
            //
            // Logika SAMA untuk mobil dan motor:
            //   start_date (dateTime) = start_rent + time_pickup
            //   end_date   (dateTime) = start_date + duration_hours dari paket
            //
            // Perbedaan mobil vs motor hanya di ada/tidaknya layanan.
            // ─────────────────────────────────────────────────────────────────────────
            function updateEstimasi() {
                var startDate = $('#start_date').val();
                var timePick  = $('#time_pickup').val();
                var paketOpt  = $('#paket_sewa option:selected');
                var durHours  = parseInt(paketOpt.data('duration-hours'));

                // Butuh ketiganya untuk menghitung
                if (!startDate || !timePick || !durHours) {
                    $('#end_date').val('');
                    $('#end_time').val('');
                    return;
                }

                // Gabungkan tanggal + waktu → hitung end dateTime
                var startDateTime = new Date(startDate + 'T' + timePick + ':00');
                var endDateTime   = new Date(startDateTime.getTime() + durHours * 60 * 60 * 1000);

                // Format end_date: YYYY-MM-DD
                var endDateStr =
                    endDateTime.getFullYear() + '-' +
                    String(endDateTime.getMonth() + 1).padStart(2, '0') + '-' +
                    String(endDateTime.getDate()).padStart(2, '0');

                // Format end_time: HH:mm
                var endTimeStr =
                    String(endDateTime.getHours()).padStart(2, '0') + ':' +
                    String(endDateTime.getMinutes()).padStart(2, '0');

                $('#end_date').val(endDateStr);
                $('#end_time').val(endTimeStr);
            }

            // Trigger kalkulasi saat field relevan berubah
            $('#start_date, #time_pickup, #paket_sewa').on('change', function () {
                updateEstimasi();
            });

            // ─── Session success notification ─────────────────────────────────────────
            let successMsg = "{{ session('success') }}";
            if (successMsg) {
                var timerInterval;
                Swal.fire({
                    icon: 'success',
                    title: '🥳 Berhasil Melakukan Pemesanan',
                    html: 'Silakan cek dashboard Anda untuk update reservasi.<br>Tertutup dalam <b></b> detik.',
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: () => {
                        Swal.showLoading();
                        timerInterval = setInterval(() => {
                            const b = Swal.getHtmlContainer()?.querySelector('b');
                            if (b) b.textContent = Math.ceil(Swal.getTimerLeft() / 1000);
                        }, 100);
                    },
                    willClose: () => clearInterval(timerInterval)
                });
            }
        });
    </script>
    <script src="{{ asset('frontend/js/mymaps.js') }}"></script>
@endpush