@extends('layouts.frontend.app')
@section('title', 'Pemesanan')

@section('content')


    <section class="hero-wrap hero-wrap-2 js-fullheight"
        style="background-image: url('{{ asset('frontend/images/bg_3.jpg') }}');" data-stellar-background-ratio="0.5">
        <div class="overlay"></div>
        <div class="container">
            <div class="row no-gutters slider-text js-fullheight align-items-end justify-content-start">
                <div class="col-md-9 ftco-animate pb-5">
                    <p class="breadcrumbs"><span class="mr-2"><a href="{{ route('home') }}">Home <i
                                    class="ion-ios-arrow-forward"></i></a></span> <span>Pemesanan <i
                                class="ion-ios-arrow-forward"></i></span></p>
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
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tipe Kendaraan</label><br>
                                    <select name="type" class="form-control" id="tipe_kendaraan">
                                        <option value="">Pilih Jenis Kendaraan</option>
                                        <option value="motorcycle" {{ !empty($vehicle) && $vehicle->type == 'motorcycle' ? "selected" : "" }}>Motor</option>
                                        <option value="car" {{ !empty($vehicle) && $vehicle->type == 'car' ? "selected" : "" }}>Mobil</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6" id="service-group">
                                <div class="form-group">
                                    <label>Layanan</label><br>
                                    <select name="service_id" class="form-control" id="service">
                                        <option value="">Pilih Layanan</option>
                                        @foreach ($services as $item)
                                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Masa Sewa</label><br>
                                    <select name="masa_sewa" class="form-control" id="masa_sewa">
                                        <option value="">Pilih Masa Sewa</option>
                                        @for ($i = 1; $i <= 30; $i++)
                                            <option value="{{ $i }}">{{ $i }} Hari</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Paket Sewa</label><br>
                                    <select name="rental_package_id" class="form-control" id="paket_sewa">
                                        <option value="">Pilih Paket Sewa</option>
                                        @foreach ($rentalPackages as $item)
                                            <option value="{{ $item->id }}">{{ $item->name }} / Hari</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Mulai</label><br>
                                    <input type="date" name="start_rent" id="start_date" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Selesai</label><br>
                                    <input type="date" name="end_rent" id="end_date" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Waktu Pengambilan</label><br>
                                    <input type="time" name="time_pickup" id="time_pickup" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Jenis Kendaraan</label><br>
                                    <select name="vehicle_id" class="form-control select2" id="jenis_kendaraan">
                                        <option value="">Pilih Jenis Kendaraan</option>
                                        @foreach ($vehicles as $item)
                                            <option value="{{ $item->id }}" {{ !empty($vehicle) && $vehicle->id == $item->id ? "selected" : "" }}>{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row justify-content-center">
                            <div class="col-md-12">
                                <div id="map" class="bg-white"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Alamat Penjemputan</label><br>
                            <textarea name="address_pickup" id="alamat" class="form-control" cols="30" rows="5"></textarea>
                            <small>Jika alamat belum sesuai, mohon sesuaikan alamat tersebut.</small>
                        </div>
                        <input type="hidden" id="lat" name="latitude">
                        <input type="hidden" id="lng" name="longitude">

                        <h1>INFORMASI PEMESAN</h1>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email</label><br>
                                    <input type="email" name="email_guest" id="email" class="form-control"
                                        placeholder="Masukkan Email Anda" value="{{ Auth::check() ? Auth::user()->email : '' }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama Lengkap</label><br>
                                    <input type="text" name="nama_guest" id="nama" class="form-control"
                                        placeholder="Masukkan Nama Lengkap Anda" value="{{ Auth::check() ? Auth::user()->name : '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Nomor Telepon</label><br>
                            <input type="text" name="no_hp_guest" id="no_hp_guest" class="form-control"
                                placeholder="Masukkan Nomor Telepon" value="{{ Auth::check() ? Auth::user()->phone : '' }}">
                        </div>


                        <div class="form-group">
                            <button class="form-control btn btn-primary">Pesan Sekarang</button>
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
        $(document).ready(function() {
            $("#jenis_kendaraan").select2({
                theme: "bootstrap4",
                ajax: {
                    url: "{{ route('reservations.search-vehicle') }}", // Update this with your actual route
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term, // Search query (user input)
                            vehicle_type: $('#tipe_kendaraan').val(), // Pass the selected vehicle type
                            _token: "{{ csrf_token() }}" // Include CSRF token if necessary
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data.items // Ensure your response returns data in this format
                        };
                    },
                    cache: true
                },
            });

            function toggleServiceField() {
                if ($('#tipe_kendaraan').val() == 'motorcycle') {
                    $('#service-group').hide(); // Hide the "Layanan" field
                } else {
                    $('#service-group').show(); // Show the "Layanan" field
                }
            }

            toggleServiceField();

            $('#tipe_kendaraan').change(function() {
                toggleServiceField();
                $("#jenis_kendaraan").val(null).trigger('change');
            });

            function updateEndRentDate() {
                var masaSewa = parseInt($('#masa_sewa').val());
                var startDate = $('#start_date').val();

                if (masaSewa && startDate) {
                    var startRentDate = new Date(startDate);
                    startRentDate.setDate(startRentDate.getDate() + masaSewa);
                    
                    var endRentDate = startRentDate.toISOString().split('T')[0];
                    $('#end_date').val(endRentDate);
                } else {
                    $('#end_date').val(''); // Clear the end date if inputs are not valid
                }
            }

            $('#masa_sewa, #start_date').change(function() {
                updateEndRentDate();
            });

            let a = "{{ session('success') }}"
            if (a) {
                var timerInterval;
                Swal.fire({
                    icon: 'success',
                    title: '🥳 Berhasil Melakukan Pemesanan',
                    text: '🥳 Silahkan cek pada dashboard anda untuk mengetahui update mengenai reservasi anda.',
                    html: 'Tertutup otomatis dalam <b></b> milliseconds.',
                    timer: 2000,
                    timerProgressBar: true,
                    didOpen: () => {
                        Swal.showLoading();
                        timerInterval = setInterval(() => {
                            const content = Swal.getHtmlContainer();
                            if (content) {
                                const b = content.querySelector('b');
                                if (b) {
                                    b.textContent = Swal.getTimerLeft();
                                }
                            }
                        }, 100);
                    },
                    willClose: () => {
                        clearInterval(timerInterval);
                    }
                }).then(result => {
                    if (result.dismiss === Swal.DismissReason.timer) {}
                });
            }
        });
    </script>
    <script src="{{ asset('frontend') }}/js/mymaps.js"></script>
@endpush
