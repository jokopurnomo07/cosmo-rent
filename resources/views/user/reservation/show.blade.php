@php use Illuminate\Support\Facades\Storage; @endphp

<table class="table table-bordered">
    <tbody>
        <tr>
            <td style="width: 40%;">Nama Penyewa</td>
            <td>{{ ucwords($rentals->user->name ?? '-') }}</td>
        </tr>
        <tr>
            <td>Email Penyewa</td>
            <td>{{ $rentals->user->email ?? '-' }}</td>
        </tr>
        <tr>
            <td>No HP Penyewa</td>
            <td>
                {{ $rentals->user->phone ?? '' }}
                @if (!$rentals->user->phone)
                    <span class="text-muted fst-italic">Belum diisi</span>
                @endif
            </td>
        </tr>
        <tr>
            <td>Alamat Penjemputan</td>
            <td>{{ $rentals->address_pickup ?? '-' }}</td>
        </tr>
        <tr>
            <td>Tanggal Sewa</td>
            <td>{{ $rentals->start_date ? date('d-m-Y', strtotime($rentals->start_date)) : '-' }}</td>
        </tr>
        <tr>
            <td>Tanggal Selesai</td>
            <td>{{ $rentals->end_date ? date('d-m-Y', strtotime($rentals->end_date)) : '-' }}</td>
        </tr>
        <tr>
            <td>Waktu Pengambilan</td>
            <td>{{ $rentals->time_pickup ?? '-' }}</td>
        </tr>
        <tr>
            <td>Nama Kendaraan</td>
            <td>{{ $rentals->vehicle->name ?? '-' }}</td>
        </tr>

        @if ($rentals->vehicle && $rentals->vehicle->type === 'car')
            @php $service = $rentals->services->first(); @endphp
            <tr>
                <td>Layanan</td>
                <td>{{ $service->name ?? '-' }}</td>
            </tr>
        @endif

        <tr>
            <td>Paket Sewa</td>
            <td>{{ $rentals->rental_package->name ?? '-' }}</td>
        </tr>
        <tr>
            <td>Total Harga</td>
            <td>Rp. {{ number_format($rentals->total_price ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Status</td>
            <td>
                @switch($rentals->status)
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
        </tr>

        {{-- Alasan hanya tampil jika dibatalkan atau ditolak --}}
        @if (in_array($rentals->status, ['canceled', 'rejected']))
            <tr>
                <td colspan="2" style="vertical-align: top;">
                    <strong>Alasan {{ $rentals->status === 'canceled' ? 'Dibatalkan' : 'Ditolak' }}</strong><br>
                    {{ $rentals->reason_canceled ?? 'Tidak ada keterangan.' }}
                </td>
            </tr>
        @endif

        {{-- Gambar Kendaraan --}}
        <tr>
            <td colspan="2" style="vertical-align: top;">
                <strong>Gambar Kendaraan</strong><br>
                @php
                    $image    = $rentals->vehicle->vehicle_images ?? null;
                    $hasImage = $image && Storage::disk('public')->exists($image);
                @endphp
                <div class="row mt-2">
                    @if ($hasImage)
                        <div class="col-md-4">
                            <a class="example-image-link"
                               href="{{ asset('storage/' . $image) }}"
                               data-lightbox="{{ $image }}">
                                <img class="example-image img-fluid rounded"
                                     src="{{ asset('storage/' . $image) }}"
                                     alt="Foto Kendaraan {{ $rentals->vehicle->name }}"
                                     style="max-width: 100%; height: auto; object-fit: cover;">
                            </a>
                        </div>
                    @else
                        <div class="col-12 text-center text-muted">
                            <i class="bi bi-image" style="font-size: 2rem;"></i><br>
                            Belum Ada Gambar yang Diupload
                        </div>
                    @endif
                </div>
            </td>
        </tr>

    </tbody>
</table>