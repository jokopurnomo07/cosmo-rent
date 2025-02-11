@extends('layouts.admin.app')
@section('title', 'Data Penyewaan')

@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last"></div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Data Penyewaan</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <section class="section">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">
                        Data Penyewaan
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped" id="table1">
                        <thead>
                            <tr>
                                <th style="width: 5%;">No</th>
                                <th style="width: 25%;" class="text-truncate">Trx ID</th>
                                <th style="width: 25%;" class="text-truncate">Nama Pemesan</th>
                                <th class="d-none d-md-table-cell" style="width: 15%;">Tanggal Sewa</th>
                                <th class="d-none d-md-table-cell" style="width: 15%;">Tanggal Selesai</th>
                                <th style="width: 10%;">Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rentals as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->trx_id }}</td>
                                        <td class="text-truncate" style="max-width: 100px;">
                                            {{ $item->user_id != null ? $item->user->name : $item->nama_guest }}
                                        </td>
                                        <td class="d-none d-md-table-cell">{{ date('d-m-Y', strtotime($item->start_date)) }}</td>
                                        <td class="d-none d-md-table-cell">{{ date('d-m-Y', strtotime($item->end_date)) }}</td>
                                        <td>
                                            @switch($item->status)
                                                @case('paid')
                                                    <span class="badge bg-warning">Aktif</span>
                                                    @break
                                                @case('ongoing')
                                                    <span class="badge bg-primary">Berlangsung</span>
                                                    @break
                                                @case('returned')
                                                    <span class="badge bg-success">Dikembalikan</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">Status Tidak Diketahui</span>
                                            @endswitch
                                        </td>
                                        <td class="text-center">
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
                url: "/user/rentals/" + id,
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