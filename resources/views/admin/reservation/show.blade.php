{{--
    Partial view — returned as plain HTML for AJAX modal injection.
    No @extends / @section needed.
--}}
<table class="table table-bordered table-sm">
    <tbody>
        <tr>
            <td class="fw-semibold" style="width:40%;">Nama Penyewa</td>
            <td>{{ $reservation->user ? ucwords($reservation->user->name) : '—' }}</td>
        </tr>
        <tr>
            <td class="fw-semibold">Email Penyewa</td>
            <td>{{ $reservation->user?->email ?? '—' }}</td>
        </tr>
        <tr>
            <td class="fw-semibold">No HP Penyewa</td>
            <td>{{ $reservation->user?->phone ?? '—' }}</td>
        </tr>
        <tr>
            <td class="fw-semibold">Alamat Penjemputan</td>
            <td>{{ $reservation->address_pickup ?? '—' }}</td>
        </tr>
        <tr>
            <td class="fw-semibold">Tanggal Mulai Sewa</td>
            <td>{{ date('d-m-Y H:i', strtotime($reservation->start_date)) }}</td>
        </tr>
        <tr>
            <td class="fw-semibold">Tanggal Selesai Sewa</td>
            <td>{{ date('d-m-Y H:i', strtotime($reservation->end_date)) }}</td>
        </tr>
        <tr>
            <td class="fw-semibold">Waktu Pengambilan</td>
            <td>{{ $reservation->time_pickup ?? '—' }}</td>
        </tr>
        <tr>
            <td class="fw-semibold">Nama Kendaraan</td>
            <td>{{ $reservation->vehicle?->name ?? '—' }}</td>
        </tr>
        @if ($reservation->vehicle?->type === 'car')
            <tr>
                <td class="fw-semibold">Layanan</td>
                <td>{{ $reservation->services->first()?->name ?? '—' }}</td>
            </tr>
        @endif
        <tr>
            <td class="fw-semibold">Paket Sewa</td>
            <td>{{ $reservation->rental_package?->name ?? '—' }}</td>
        </tr>
        <tr>
            <td class="fw-semibold">Total Harga</td>
            <td>Rp {{ number_format($reservation->total_price ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="fw-semibold">Status</td>
            <td>
                @php
                    $badge = match($reservation->status) {
                        'pending'   => 'bg-secondary',
                        'confirmed' => 'bg-success',
                        'paid'      => 'bg-primary',
                        'canceled'  => 'bg-warning text-dark',
                        'rejected'  => 'bg-danger',
                        'expired'   => 'bg-dark',
                        default     => 'bg-secondary',
                    };
                    $label = match($reservation->status) {
                        'pending'   => 'Menunggu Konfirmasi',
                        'confirmed' => 'Dikonfirmasi',
                        'paid'      => 'Sudah Dibayar',
                        'canceled'  => 'Dibatalkan',
                        'rejected'  => 'Ditolak',
                        'expired'   => 'Kadaluarsa',
                        default     => ucfirst($reservation->status),
                    };
                @endphp
                <span class="badge {{ $badge }}">{{ $label }}</span>
            </td>
        </tr>

        @if (in_array($reservation->status, ['canceled', 'rejected']))
            <tr>
                <td class="fw-semibold">
                    Alasan {{ $reservation->status === 'canceled' ? 'Pembatalan' : 'Penolakan' }}
                </td>
                <td>{{ $reservation->reason_canceled ?? '—' }}</td>
            </tr>
        @endif

        @if ($reservation->payment_url)
            <tr>
                <td class="fw-semibold">Link Pembayaran</td>
                <td>
                    <a href="{{ $reservation->payment_url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-credit-card me-1"></i> Buka Link Pembayaran
                    </a>
                </td>
            </tr>
        @endif

        {{-- Vehicle image --}}
        <tr>
            <td colspan="2">
                <strong>Foto Kendaraan</strong>
                <div class="row mt-2">
                    @php $img = $reservation->vehicle?->vehicle_images; @endphp
                    @if ($img)
                        <div class="col-md-4">
                            <a class="example-image-link"
                               href="{{ asset('storage/' . $img) }}"
                               data-lightbox="{{ $img }}">
                                <img src="{{ asset('storage/' . $img) }}"
                                     alt="Foto {{ $reservation->vehicle?->name }}"
                                     class="img-fluid rounded">
                            </a>
                        </div>
                    @else
                        <div class="col-12 text-muted">Belum ada gambar yang diupload.</div>
                    @endif
                </div>
            </td>
        </tr>
    </tbody>
</table>