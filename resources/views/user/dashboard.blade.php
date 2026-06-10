@extends('layouts.admin.app')
@section('title', 'Dashboard')

@section('content')
    <div class="page-heading">
        <h3>Dashboard</h3>
    </div>
    <div class="page-content">
        <section class="row">
            <div class="col-12 col-lg-12">

                {{-- Stats Cards --}}
                <div class="row">

                    {{-- Total Penyewaan Selesai --}}
                    <div class="col-12 col-lg-3 col-md-6">
                        <div class="card">
                            <div class="card-body px-4 py-4-5">
                                <div class="row">
                                    <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                        <div class="stats-icon blue mb-2">
                                            <i class="iconly-boldProfile"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                        <h6 class="text-muted font-semibold">Penyewaan Selesai</h6>
                                        <h6 class="font-extrabold mb-0">{{ $totalRent }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Total Reservasi Confirmed --}}
                    <div class="col-12 col-lg-3 col-md-6">
                        <div class="card">
                            <div class="card-body px-4 py-4-5">
                                <div class="row">
                                    <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                        <div class="stats-icon green mb-2">
                                            <i class="iconly-boldAdd-User"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                        <h6 class="text-muted font-semibold">Reservasi Dikonfirmasi</h6>
                                        <h6 class="font-extrabold mb-0">{{ $totalReservation }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Kendaraan Sedang Disewa (conditional) --}}
                    <div class="col-12 col-lg-3 col-md-6">
                        <div class="card {{ ($activeCount ?? 0) > 0 ? 'border-warning' : '' }}">
                            <div class="card-body px-4 py-4-5">
                                <div class="row">
                                    <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                        <div class="stats-icon {{ $activeRentals ? 'orange' : 'purple' }} mb-2">
                                            <i class="iconly-boldShow"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                        <h6 class="text-muted font-semibold">Sedang Disewa</h6>
                                        <h6 class="font-extrabold mb-0">
                                            @if(($activeCount ?? 0) > 0)
                                                {{ $activeCount }} aktif
                                            @else
                                                <span class="text-muted" style="font-size: 0.85rem; font-weight: 500;">Tidak ada</span>
                                            @endif
                                        </h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Active Rental Detail (jika ada) --}}
                @if(($activeCount ?? 0) > 0)
                <div class="row mt-1">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Penyewaan Aktif ({{ $activeCount }})</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-group">
                                    @foreach($activeRentals as $ar)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="fw-semibold">{{ $ar->vehicle->name ?? '-' }}</div>
                                                <small class="text-muted">Kembali: {{ \Carbon\Carbon::parse($ar->end_date)->format('d M Y, H:i') }}</small>
                                            </div>
                                            <div>
                                                <span class="badge
                                                    @switch($ar->status)
                                                        @case('ongoing') bg-primary @break
                                                        @case('awaiting_confirmation') bg-warning text-dark @break
                                                        @case('paid') bg-info @break
                                                        @default bg-secondary
                                                    @endswitch">
                                                    {{ ucfirst(str_replace('_', ' ', $ar->status)) }}
                                                </span>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Extension Summary --}}
                <div class="row mt-3">
                    <div class="col-12 col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="text-muted">Permintaan Perpanjangan</h6>
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h4 class="mb-0">{{ $extensionCounts['pending'] ?? 0 }}</h4>
                                        <small class="text-muted">Menunggu Persetujuan</small>
                                    </div>
                                    <div>
                                        <a href="{{ route('user.extensions.index') }}" class="btn btn-sm btn-primary">Lihat Semua</a>
                                    </div>
                                </div>
                                @if(($pendingExtensions ?? collect())->isNotEmpty())
                                    <hr>
                                    <ul class="list-group list-group-flush">
                                        @foreach($pendingExtensions as $pe)
                                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                                <div>
                                                    <div class="fw-semibold">{{ $pe->rental->vehicle->name ?? '-' }}</div>
                                                    <small class="text-muted">Diminta sampai: {{ $pe->extended_until?->format('d M Y') }}</small>
                                                </div>
                                                <span class="badge bg-warning text-dark">Menunggu</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Riwayat Penyewaan Terbaru --}}
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Riwayat Penyewaan Terbaru</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-striped" id="table-rentals">
                                    <thead>
                                        <tr>
                                            <th>Kendaraan</th>
                                            <th>Tanggal Sewa</th>
                                            <th>Tanggal Kembali</th>
                                            <th>Total Harga</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($recentRentals as $rental)
                                            <tr>
                                                <td>{{ $rental->vehicle->name ?? '-' }}</td>
                                                <td>{{ \Carbon\Carbon::parse($rental->start_date)->format('d M Y') }}</td>
                                                <td>{{ \Carbon\Carbon::parse($rental->end_date)->format('d M Y') }}</td>
                                                <td>Rp {{ number_format($rental->total_price, 0, ',', '.') }}</td>
                                                <td>
                                                    <span class="badge
                                                        @switch($rental->status)
                                                            @case('returned')
                                                            @case('completed')
                                                                bg-success
                                                                @break
                                                            @case('ongoing')
                                                                bg-primary
                                                                @break
                                                            @case('awaiting_confirmation')
                                                                bg-warning text-dark
                                                                @break
                                                            @case('payment_failed')
                                                                bg-danger
                                                                @break
                                                            @case('paid')
                                                                bg-info
                                                                @break
                                                            @default
                                                                bg-secondary
                                                        @endswitch">
                                                        {{ ucfirst(str_replace('_', ' ', $rental->status)) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">
                                                    Belum ada riwayat penyewaan.
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
@endsection