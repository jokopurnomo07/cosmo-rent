@extends('layouts.frontend.app')
@section('title', 'Kontak Kami')

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

    <section class="ftco-section contact-section">
        <div class="container">
            <div class="row d-flex mb-5 contact-info">
                <div class="col-md-12 block-9 mb-md-5">
                    <form action="#" class="bg-light p-5 contact-form">
                        <h3>DETAIL PESANAN</h3>
                        <hr>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tipe Kendaraan</label><br>
                                    <select name="tipe_kendaraan" class="form-control" id="tipe_kendaraan">
                                        <option value="">Pilih Jenis Kendaraan</option>
                                        <option value="motor">Motor</option>
                                        <option value="car">Mobil</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Layanan</label><br>
                                    <select name="service" class="form-control" id="service">
                                        <option value=""></option>
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
                                    <label>Tanggal Mulai</label><br>
                                    <input type="date" name="start_rent" id="start_rent" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Selesai</label><br>
                                    <input type="date" name="end_rent" id="end_rent" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Waktu Pengambilan</label><br>
                                    <input type="time" name="end_rent" id="end_rent" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Jenis Kendaraan</label><br>
                            <select name="jenis_kendaraan" class="form-control select2" id="jenis_kendaraan">
                                <option value="">Pilih Jenis Kendaraan</option>
                                <option value="motor">Motor</option>
                                <option value="car">Mobil</option>
                            </select>
                        </div>

                        <div class="row justify-content-center">
                            <div class="col-md-12">
                                <div id="map" class="bg-white"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Alamat Penjemputan</label><br>
                            <textarea name="alaamt" id="alamat" class="form-control" cols="30" rows="5"></textarea>
                        </div>

                        <h1>INFORMASI PEMESAN</h1>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email</label><br>
                                    <input type="email" name="email" id="email" class="form-control"
                                        placeholder="Masukkan Email Anda">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama Lengkap</label><br>
                                    <input type="text" name="email" id="email" class="form-control"
                                        placeholder="Masukkan Nama Lengkap Anda">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Nomor Telepon</label><br>
                            <input type="text" name="email" id="email" class="form-control"
                                placeholder="Masukkan Nomor Telepon">
                        </div>


                        <div class="form-group">
                            <button type="submit" class="form-control btn btn-primary">Pesan Sekarang</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </section>

@endsection
@push('scripts')
    <script>
        $(document).ready(function() {
            $("#jenis_kendaraan").select2({
                theme: "bootstrap4"
            });
        });
    </script>
@endpush
