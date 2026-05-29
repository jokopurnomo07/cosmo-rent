<table class="table table-bordered">
    <tbody>
        <tr>
            <td>Nama Kendaraan</td>
            <td>{{ ucwords($vehicle->name) }}</td>
        </tr>
        <tr>
            <td>Tipe Kendaraan</td>
            {{-- BUG FIX: $vehicle->vehicle_type tidak ada, kolom di DB adalah 'type' --}}
            <td>{{ $vehicle->type == 'car' ? 'Mobil' : 'Motor' }}</td>
        </tr>
        <tr>
            <td>Transmisi</td>
            <td>
                @if ($vehicle->transmission == 'manual')
                    Manual
                @elseif ($vehicle->transmission == 'automatic' || $vehicle->transmission == 'otomatic')
                    Otomatis / Matic
                @else
                    Manual & Otomatis
                @endif
            </td>
        </tr>
        <tr>
            <td>Bahan Bakar</td>
            <td>{{ ucwords($vehicle->fuel) }}</td>
        </tr>
        <tr>
            <td>Plat Nomor Kendaraan</td>
            <td>{{ strtoupper($vehicle->registration_number) }}</td>
        </tr>
        <tr>
            <td>Kapasitas Penumpang</td>
            <td>{{ $vehicle->capacity }} Orang</td>
        </tr>
        <tr>
            <td colspan="2" style="vertical-align: top;">
                <strong>Fitur</strong><br>
                @php
                    $features = $vehicle->features->toArray();
                    $columns  = $features
                        ? array_chunk($features, ceil(count($features) / 3))
                        : [];
                @endphp
                <div class="row">
                    @if ($columns)
                        @foreach ($columns as $column)
                            <div class="col-md-4">
                                <ul style="list-style: none; padding-left: 0;">
                                    @foreach ($column as $feature)
                                        @php $name = str_replace('_', ' ', $feature['name']); @endphp
                                        <li><i class="fas fa-check text-success me-1"></i>{{ ucwords($name) }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    @else
                        <div class="col-12 text-center text-muted">Belum ada fitur yang dipilih</div>
                    @endif
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="vertical-align: top;">
                <strong>Deskripsi</strong><br>
                {{ $vehicle->description }}
            </td>
        </tr>
        <tr>
            <td colspan="2" style="vertical-align: top;">
                <strong>Gambar Kendaraan</strong><br>
                <div class="row mt-2">
                    @if ($vehicle->vehicle_images && Storage::disk('public')->exists($vehicle->vehicle_images))
                        <div class="col-md-4">
                            <a class="example-image-link"
                               href="{{ asset('storage/' . $vehicle->vehicle_images) }}"
                               data-lightbox="{{ $vehicle->vehicle_images }}">
                                <img class="example-image img-fluid rounded"
                                     src="{{ asset('storage/' . $vehicle->vehicle_images) }}"
                                     alt="Foto Kendaraan {{ $vehicle->name }}"
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