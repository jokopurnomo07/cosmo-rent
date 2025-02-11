@extends('layouts.admin.app')

@section('title', 'Laporan Penyewaan')

@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>Laporan Penyewaan</h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Laporan Penyewaan</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h5>Download Laporan</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.reports.export') }}" method="GET">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="start_date">Tanggal Mulai:</label>
                                <input type="date" name="start_date" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label for="end_date">Tanggal Selesai:</label>
                                <input type="date" name="end_date" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-file-earmark-excel"></i> Download Laporan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
@endsection
