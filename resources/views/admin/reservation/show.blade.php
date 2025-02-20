<table class="table table-bordered">
    <tbody>
        <tr>
            <td>Nama Penyewa</td>
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
            <td>Alamat Penyewa</td>
            <td>{{ $rentals->address_pickup }}</td>
        </tr>
        <tr>
            <td>Tanggal Sewa</td>
            <td>{{ date('d-m-Y', strtotime($rentals->start_date)) }}</td>
        </tr>
        <tr>
            <td>Tanggal Selesai Sewa</td>
            <td>{{ date('d-m-Y', strtotime($rentals->end_date)) }}</td>
        </tr>
        <tr>
            <td>Waktu Pengambilan</td>
            <td>{{ $rentals->time_pickup }}</td>
        </tr>
        <tr>
            <td>Nama Kendaraan</td>
            <td>{{ $rentals->vehicle->name }}</td>
        </tr>
        @if ($rentals->vehicle->type == "car")
        <tr>
            <td>Layanan</td>
            <td>{{ $rentals->services[0]->name }}</td>
        </tr>
        @endif
        <tr>
            <td>Paket Sewa</td>
            <td>{{ $rentals->rental_package->name }}</td>
        </tr>
        <tr>
            <td>Total Harga</td>
            <td>Rp. {{ number_format($rentals->total_price ?? 0, 0, ",", ".") }}</td>
        </tr>
        {{-- <tr>
            <td colspan="2" style="vertical-align: top;">
                <strong>Fitur</strong><br>
                @php
                    $features = $vehicle->features->toArray();
                    if( $features != [] ){
                        $columns = array_chunk($features, ceil(count($features) / 3)); // Split into 3 columns
                    }else{
                        $columns = [];
                    }
                @endphp
                <div class="row">
                    @if ($columns != [])
                        @foreach ($columns as $column)
                            <div class="col-md-4">
                                <ul class="features">
                                    @foreach ($column as $feature)
                                    @php
                                        $name = str_replace('_', ' ', $feature['name']);
                                    @endphp
                                        <li class="check" style="list-style: none;">
                                            <i class="fas fa-check"></i>
                                            {{ ucwords($name) }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    @else
                    <div class="col-12 text-center">
                        Belum ada fitur yang dipilih
                    </div>
                    @endif
                </div>
            </td>
        </tr> --}}
        @if ($rentals->status == "canceled" || $rentals->status == "rejected")
            <tr>
                <td colspan="2" style="vertical-align: top;">
                    <strong>Alasan {{ $rentals->status == "canceled" ? "Dibatalkan" : "Ditolak" }}</strong><br>
                    {{ $rentals->reason_canceled }}
                </td>
            </tr>
        @endif
        <tr>
            <td colspan="2" style="vertical-align: top;">
                <strong>Gambar Kendaraan</strong><br>
                @php
                    $images = $rentals->vehicle->vehicle_images;
                @endphp
                <div class="row">
                    @if ($images != null)
                        <div class="col-md-4">
                            <a class="example-image-link" href="{{ asset('storage/' . $images) }}" data-lightbox="{{ $images }}">
                                <img class="example-image" src="{{ asset('storage/' . $images) }}" alt="Foto Kendaraan {{ $rentals->vehicle->name }}" style="max-width: 100%; height: auto;">
                            </a>
                        </div>
                    @else
                        <div class="col-md-12 text-center">
                            Belum Ada Gambar yang Diupload
                        </div>
                    @endif
                </div>
            </td>
        </tr>
    </tbody>
</table>
