<table class="table table-bordered">
    <tbody>
        <tr>
            <td>Nama Kendaraan</td>
            <td>{{ ucwords($vehicle->name) }}</td>
        </tr>
        <tr>
            <td>Tipe Kendaraan</td>
            <td>{{ $vehicle->vehicle_type == 'car' ? 'Mobil' : 'Motor' }}</td>
        </tr>
        <tr>
            <td>Transmisi</td>
            <td>
                @if ($vehicle->transmission == 'manual')
                    Manual
                @elseif($vehicle->transmission == 'automatic')
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
            <td>{{ ucwords($vehicle->registration_number) }}</td>
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
                @php
                    $images = $vehicle->vehicle_images;
                @endphp
                <div class="row">
                    @if ($images != null)
                        <div class="col-md-4">
                            <a class="example-image-link" href="{{ asset('storage/' . $images) }}" data-lightbox="{{ $images }}">
                                <img class="example-image" src="{{ asset('storage/' . $images) }}" alt="Foto Kendaraan {{ $vehicle->name }}" style="max-width: 100%; height: auto;">
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
