@extends('layouts.admin.app')
@section('title', 'Data Reservasi')

@section('content')
    @php
        $titleMap = [
            'pending' => 'Pending',
            'canceled' => 'Dibatalkan / Ditolak',
            'default' => 'Dikonfirmasi'
        ];
        $title = $titleMap[request('status')] ?? $titleMap['default'];
    @endphp

    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-md-6 order-last"></div>
                <div class="col-md-6 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Data Reservasi {{ $title }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        
        <section class="section">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Reservasiku {{ $title }}</h5>
                </div>
                
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="table1">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Trx ID</th>
                                    <th>Nama Pemesan</th>
                                    <th class="d-none d-md-table-cell">Tanggal Pemesanan</th>
                                    <th class="d-none d-md-table-cell">Tanggal Selesai</th>
                                    @if(request('status') == 'canceled' || request('status') == 'rejected')
                                        <th class="d-none d-lg-table-cell">Alasan</th>
                                    @endif
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reservations as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->trx_id }}</td>
                                        <td>{{ $item->user->name }}</td>
                                        <td class="d-none d-md-table-cell">{{ date('d-m-Y', strtotime($item->start_date)) }}</td>
                                        <td class="d-none d-md-table-cell">{{ date('d-m-Y', strtotime($item->end_date)) }}</td>
                                        
                                        @if(request('status') == 'canceled' || request('status') == 'rejected')
                                            <td>{{ $item->reason_canceled }}</td>
                                        @endif
                                        
                                        <td>
                                            @switch($item->status)
                                                @case('paid')
                                                    <span class="badge bg-success">Lunas</span>
                                                    @break
                                                @case('confirmed')
                                                    <a href="{{ $item->payment_url }}" target="_blank" class="btn btn-success">Menunggu Pembayaran</a>
                                                    @break
                                                @case('failed')
                                                    <span class="badge bg-danger">Gagal</span>
                                                    @break
                                                @case('pending')
                                                    <span class="badge bg-warning">Menunggu Dikonfirmasi</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">Tidak Diketahui</span>
                                            @endswitch
                                        </td>
                                        
                                        <td class="text-center">

                                            @switch($item->status)
                                                @case('confirmed')
                                                    <a href="{{ $item->payment_url }}" target="_blank" class="btn btn-success">Pay Now</a>
                                                    @break
                                            @endswitch

                                            <button type="button" class="btn btn-outline-primary" onclick="detail({{ $item->id }})">
                                                <i class="bi bi-info-circle-fill"></i>
                                            </button>
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

    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Penyewaan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="contentModal"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function detail(id) {
            $.ajax({
                type: "GET",
                url: "/user/reservations/" + id,
                success: function(response) {
                    $('#contentModal').html(response);
                    $('#detailModal').modal('show');
                },
                error: function(xhr) {
                    alert('Failed to fetch details. Please try again.');
                }
            });
        }
    </script>
@endpush