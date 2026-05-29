@extends('layouts.admin.app')
@section('title', 'Riwayat Pesanan')

@section('content')
    <div class="page-heading">

        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last"></div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Riwayat Pesanan</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Riwayat Pesananku</h5>
                </div>
                <div class="card-body">

                    {{-- Tab Navigation --}}
                    <ul class="nav nav-tabs mb-3" id="historyTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="selesai-tab" data-bs-toggle="tab"
                                data-bs-target="#selesai" type="button" role="tab">
                                <i class="bi bi-check-circle me-1"></i>
                                Sewa Selesai
                                <span class="badge bg-success ms-1">{{ $completedRentals->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="dibatalkan-tab" data-bs-toggle="tab"
                                data-bs-target="#dibatalkan" type="button" role="tab">
                                <i class="bi bi-x-circle me-1"></i>
                                Dibatalkan / Gagal
                                <span class="badge bg-danger ms-1">{{ $canceledReservations->count() }}</span>
                            </button>
                        </li>
                    </ul>

                    {{-- Tab Content --}}
                    <div class="tab-content" id="historyTabContent">

                        {{-- Completed Rentals --}}
                        <div class="tab-pane fade show active" id="selesai" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-striped" id="tableSelesai">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Trx ID</th>
                                            <th class="d-none d-md-table-cell">Kendaraan</th>
                                            <th class="d-none d-md-table-cell">Tanggal Sewa</th>
                                            <th class="d-none d-md-table-cell">Tanggal Selesai</th>
                                            <th>Total</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($completedRentals as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->trx_id }}</td>
                                                <td class="d-none d-md-table-cell">{{ $item->vehicle->name ?? '-' }}</td>
                                                <td class="d-none d-md-table-cell">{{ date('d-m-Y', strtotime($item->start_date)) }}</td>
                                                <td class="d-none d-md-table-cell">{{ date('d-m-Y', strtotime($item->end_date)) }}</td>
                                                <td>Rp. {{ number_format($item->total_price ?? 0, 0, ',', '.') }}</td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-outline-primary btn-sm"
                                                        onclick="detailRental({{ $item->id }})">
                                                        <i class="bi bi-info-circle-fill"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-4">
                                                    <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                                    Belum ada riwayat sewa selesai.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Canceled Reservations --}}
                        <div class="tab-pane fade" id="dibatalkan" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-striped" id="tableDibatalkan">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Trx ID</th>
                                            <th class="d-none d-md-table-cell">Kendaraan</th>
                                            <th class="d-none d-md-table-cell">Tanggal Pesan</th>
                                            <th>Status</th>
                                            <th>Alasan</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($canceledReservations as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->trx_id }}</td>
                                                <td class="d-none d-md-table-cell">{{ $item->vehicle->name ?? '-' }}</td>
                                                <td class="d-none d-md-table-cell">{{ date('d-m-Y', strtotime($item->start_date)) }}</td>
                                                <td>
                                                    @if ($item->status == 'canceled')
                                                        <span class="badge bg-warning">Dibatalkan</span>
                                                    @else
                                                        <span class="badge bg-danger">Gagal</span>
                                                    @endif
                                                </td>
                                                <td>{{ $item->reason_canceled ?? '-' }}</td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-outline-primary btn-sm"
                                                        onclick="detailReservation({{ $item->id }})">
                                                        <i class="bi bi-info-circle-fill"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-4">
                                                    <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                                    Tidak ada reservasi yang dibatalkan atau gagal.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="contentModal">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status"></div>
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
        function detailRental(id) {
            $('#contentModal').html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></div>');
            $('#detailModal').modal('show');
            $.ajax({
                type: "GET",
                url: "/user/rentals/" + id,
                success: function(response) {
                    $('#contentModal').html(response);
                },
                error: function() {
                    $('#contentModal').html('<p class="text-center text-danger py-4">Gagal memuat data. Silakan coba lagi.</p>');
                }
            });
        }

        function detailReservation(id) {
            $('#contentModal').html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></div>');
            $('#detailModal').modal('show');
            $.ajax({
                type: "GET",
                url: "/user/reservations/" + id,
                success: function(response) {
                    $('#contentModal').html(response);
                },
                error: function() {
                    $('#contentModal').html('<p class="text-center text-danger py-4">Gagal memuat data. Silakan coba lagi.</p>');
                }
            });
        }
    </script>
@endpush