@extends('layouts.admin.app')
@section('title', 'Data Reservasi')

@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last"></div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Data Reservasi</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <section class="section">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">
                        Data Reservasi
                    </h5>
                    <a href="{{ route('reservations.create') }}">
                        <button type="button" class="btn btn-primary">
                            Tambah Reservasi
                        </button>
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="table1">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">No</th>
                                    <th style="width: 40%;">Nama Pemesan</th>
                                    <th style="width: 15%;">Tanggal Pemesanan</th>
                                    <th style="width: 15%;">Tanggal Selesai</th>
                                    <th style="width: 20%;">Email Pemesan</th>
                                    <th style="width: 15%;">No HP Pemesan</th>
                                    <th style="width: 25%;">Alamat Penjemputan</th>
                                    <th style="width: 10%;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reservation as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->user_id != null ? $item->user->name : $item->nama_guest }}</td>
                                        <td>{{ $item->start_date }}</td>
                                        <td>{{ $item->end_date }}</td>
                                        <td>{{ $item->user_id != null ? $item->user->email : $item->email_guest }}</td>
                                        <td>{{ $item->user_id != null ? $item->user->phone : $item->no_hp_guest }}</td>
                                        <td>{{ $item->user_id != null ? $item->user->address : $item->address_pickup }}</td>
                                        <td>
                                            @php
                                                $status = "";
                                                if( $item->status == "pending" ){
                                                    $status = "warning";
                                                }elseif ($item->status == "canceled") {
                                                    $status = "danger";
                                                }else{
                                                    $status = "success";
                                                }
                                            @endphp
                                            <span class="badge bg-{{ $status }}">{{ $item->status }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                </div>
            </div>

        </section>
    </div>
@endsection
