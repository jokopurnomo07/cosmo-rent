@extends('layouts.admin.app')
@section('title', 'Data Reservasi')

@section('content')
    @php
        $status = request('status');
        $titleMap = [
            'pending'  => 'Pending',
            'canceled' => 'Dibatalkan / Ditolak',
        ];
        $title = $titleMap[$status] ?? 'Semua Reservasi';
        $colCount = $status === 'canceled' ? 8 : 7;
    @endphp

    <section class="section">

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i>{{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">

    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last"></div>
                <div class="col-12 col-md-6 order-md-2 order-first">
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
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="card-title mb-0">Reservasiku — {{ $title }}</h5>

                    {{-- Tab navigasi status --}}
                    <div class="btn-group btn-group-sm">
                        <a href="{{ url()->current() }}"
                           class="btn {{ !$status ? 'btn-primary' : 'btn-outline-primary' }}">
                            Semua
                        </a>
                        <a href="{{ url()->current() . '?status=pending' }}"
                           class="btn {{ $status === 'pending' ? 'btn-warning' : 'btn-outline-warning' }}">
                            Pending
                        </a>
                        <a href="{{ url()->current() . '?status=canceled' }}"
                           class="btn {{ $status === 'canceled' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                            Dibatalkan
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="table1">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Trx ID</th>
                                    <th>Nama Pemesan</th>
                                    <th class="d-none d-md-table-cell">Tanggal Mulai</th>
                                    <th class="d-none d-md-table-cell">Tanggal Selesai</th>
                                    @if ($status === 'canceled')
                                        <th class="d-none d-lg-table-cell">Alasan</th>
                                    @endif
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($reservations as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->trx_id }}</td>
                                        <td>{{ ucwords($item->user->name ?? '-') }}</td>
                                        <td class="d-none d-md-table-cell">
                                            {{ $item->start_date ? date('d-m-Y', strtotime($item->start_date)) : '-' }}
                                        </td>
                                        <td class="d-none d-md-table-cell">
                                            {{ $item->end_date ? date('d-m-Y', strtotime($item->end_date)) : '-' }}
                                        </td>

                                        @if ($status === 'canceled')
                                            <td class="d-none d-lg-table-cell">{{ $item->reason_canceled ?? '-' }}</td>
                                        @endif

                                        <td>
                                            @switch($item->status)
                                                @case('paid')
                                                    <span class="badge bg-success">Lunas</span>
                                                    @break
                                                @case('confirmed')
                                                    <span class="badge bg-primary">Menunggu Pembayaran</span>
                                                    @break
                                                @case('failed')
                                                    <span class="badge bg-danger">Gagal</span>
                                                    @break
                                                @case('pending')
                                                    <span class="badge bg-warning text-dark">Menunggu Konfirmasi</span>
                                                    @break
                                                @case('canceled')
                                                    <span class="badge bg-secondary">Dibatalkan</span>
                                                    @break
                                                @case('rejected')
                                                    <span class="badge bg-danger">Ditolak</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">Tidak Diketahui</span>
                                            @endswitch
                                        </td>

                                        <td class="text-center">
                                            @if ($item->status === 'confirmed' && $item->payment_url)
                                                <a href="{{ $item->payment_url }}"
                                                class="btn btn-success btn-sm">
                                                    Pay Now
                                                </a>
                                            @endif

                                            <button type="button"
                                                    class="btn btn-outline-primary btn-sm"
                                                    onclick="detail({{ $item->id }})">
                                                <i class="bi bi-info-circle-fill"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $colCount }}" class="text-center text-muted py-4">
                                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                            Tidak ada data reservasi.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $reservations->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- Detail Modal --}}
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Penyewaan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="contentModal">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        @if (session('success') || session('info'))
            setTimeout(function () {
                window.location.reload();
            }, 3000);
        @endif

        function detail(id) {
            $('#contentModal').html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `);
            $('#detailModal').modal('show');

            $.ajax({
                type: "GET",
                url: "/user/reservations/" + id,
                success: function (response) {
                    $('#contentModal').html(response);
                },
                error: function (xhr) {
                    let msg = 'Gagal memuat detail. Silakan coba lagi.';
                    if (xhr.status === 403 || xhr.status === 404) {
                        msg = 'Data tidak ditemukan atau akses ditolak.';
                    }
                    $('#contentModal').html(
                        `<div class="alert alert-danger">${msg}</div>`
                    );
                }
            });
        }
    </script>
@endpush