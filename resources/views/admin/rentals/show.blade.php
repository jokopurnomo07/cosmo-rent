<table class="table table-bordered">
    <tbody>
        <tr>
            <td style="width: 35%;">Nama Penyewa</td>
            <td>{{ ucwords($rentals->user->name) }}</td>
        </tr>
        <tr>
            <td>Email Penyewa</td>
            <td>{{ $rentals->user->email }}</td>
        </tr>
        <tr>
            <td>No HP Penyewa</td>
            <td>{{ $rentals->user->phone }}</td>
        </tr>
        <tr>
            <td>Alamat Penjemputan</td>
            <td>{{ $rentals->address_pickup }}</td>
        </tr>
        <tr>
            <td>Mulai Sewa</td>
            {{--
                start_date bertipe dateTime — tampilkan tanggal + jam sekaligus.
                Jam di start_date = time_pickup yang sudah digabung saat store().
            --}}
            <td>{{ \Carbon\Carbon::parse($rentals->start_date)->format('d-m-Y H:i') }}</td>
        </tr>
        <tr>
            <td>Selesai Sewa</td>
            {{--
                end_date bertipe dateTime — dihitung dari start_date + duration_hours paket.
                Berlaku sama untuk mobil maupun motor.
            --}}
            <td>{{ \Carbon\Carbon::parse($rentals->end_date)->format('d-m-Y H:i') }}</td>
        </tr>
        <tr>
            <td>Nama Kendaraan</td>
            <td>{{ $rentals->vehicle->name }}</td>
        </tr>
        <tr>
            <td>Tipe Kendaraan</td>
            <td>{{ $rentals->vehicle->type == 'car' ? 'Mobil' : 'Motor' }}</td>
        </tr>
        {{-- Layanan hanya ada untuk mobil --}}
        @if ($rentals->vehicle->type == 'car')
            <tr>
                <td>Layanan</td>
                <td>{{ $rentals->services->first()?->name ?? '-' }}</td>
            </tr>
        @endif
        <tr>
            <td>Paket Sewa</td>
            <td>
                {{ $rentals->rental_package->name }}
                <small class="text-muted">({{ $rentals->rental_package->duration_hours }} jam)</small>
            </td>
        </tr>
        <tr>
            <td>Total Harga</td>
            <td>Rp. {{ number_format($rentals->total_price ?? 0, 0, ',', '.') }}</td>
        </tr>
        @if ($rentals->status == 'canceled' || $rentals->status == 'rejected')
            <tr>
                <td colspan="2">
                    <strong>Alasan {{ $rentals->status == 'canceled' ? 'Dibatalkan' : 'Ditolak' }}</strong><br>
                    {{ $rentals->reason_canceled }}
                </td>
            </tr>
        @endif
        <tr>
            <td colspan="2">
                <strong>Gambar Kendaraan</strong><br>
                @php $images = $rentals->vehicle->vehicle_images; @endphp
                <div class="row mt-2">
                    @if ($images)
                        <div class="col-md-4">
                            <a class="example-image-link"
                               href="{{ asset('storage/' . $images) }}"
                               data-lightbox="{{ $images }}">
                                <img class="example-image"
                                     src="{{ asset('storage/' . $images) }}"
                                     alt="Foto Kendaraan {{ $rentals->vehicle->name }}"
                                     style="max-width: 100%; height: auto;">
                            </a>
                        </div>
                    @else
                        <div class="col-md-12 text-center text-muted">
                            Belum Ada Gambar yang Diupload
                        </div>
                    @endif
                </div>
            </td>
        </tr>
    </tbody>
</table>