@extends('layouts.admin.app')
@section('title', 'Tambah Data Kendaraan')

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
                                Data Reservasi
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Reservasi</li>
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
                                Reservasi
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
                                            <div class="form-group">
                                                <label for="service" class="form-label">Layanan</label>
                                                <select class="form-control" name="service" id="service">
                                                    <option value="">Pilih Layanan</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-12">
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
                                        <div class="col-md-6 col-12">
                                            <div class="form-group">
                                                <label>Paket Sewa</label><br>
                                                <select name="masa_sewa" class="form-control" id="masa_sewa">
                                                    <option value="">Pilih Masa Sewa</option>
                                                    @for ($i = 1; $i <= 30; $i++)
                                                        <option value="{{ $i }}">{{ $i }} Hari</option>
                                                    @endfor
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <div class="form-group">
                                                <label>Tanggal Mulai</label><br>
                                                <input type="date" name="start_rent" id="start_rent" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <div class="form-group">
                                                <label>Tanggal Selesai</label><br>
                                                <input type="date" name="end_rent" id="end_rent" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <div class="form-group">
                                                <label>Waktu Pengambilan</label><br>
                                                <input type="time" name="end_rent" id="end_rent" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <div class="form-group">
                                                <label for="model_vehicle" class="form-label">Jenis Kendaraan</label>
                                                <select class="form-control" name="model_vehicle" id="model_vehicle">
                                                    <option value="">Pilih Jenis Kendaraan</option>
                                                    <option value="car">Mobil</option>
                                                    <option value="motorcycle">Motor</option>
                                                </select>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="row">
                                        <div class="col-md-12 col-12">
                                            <div class="form-group">
                                                <label for="description" class="form-label">Alamat</label>
                                                <textarea name="description" id="description" cols= "30" rows="3" class="form-control"></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    
                                    <div class="row mt-3">
                                        <h1>INFORMASI PEMESAN</h1>
                                        <hr>
                                        <div class="col-md-6 col-12">
                                            <div class="form-group">
                                                <label>Email</label><br>
                                                <input type="email" name="email" id="email" class="form-control"
                                                    placeholder="Masukkan Email Anda">
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <div class="form-group">
                                                <label>Nama Lengkap</label><br>
                                                <input type="text" name="email" id="email" class="form-control"
                                                    placeholder="Masukkan Nama Lengkap Anda">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-12">
                                        <label>Nomor Telepon</label><br>
                                        <input type="text" name="email" id="email" class="form-control"
                                            placeholder="Masukkan Nomor Telepon">
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