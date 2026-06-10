@extends('layouts.admin.app')

@section('title', 'Ajukan Perpanjangan')

@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>Ajukan Perpanjangan</h3>
                    <p class="text-subtitle text-muted">Perpanjang masa sewa kendaraan Anda</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('user.rentals.index') }}">Penyewaan</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Ajukan Perpanjangan</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <section class="section">
            <div class="card">
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <strong>Terdapat kesalahan:</strong>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-4">
                        <h5>Informasi Penyewaan</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1 text-muted">Kendaraan</p>
                                <p class="fw-semibold">{{ $rental->vehicle->name }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1 text-muted">Waktu Berakhir</p>
                                <p class="fw-semibold">{{ $rental->end_date->format('d M Y, H:i') }}</p>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('user.extensions.store', $rental->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Durasi Perpanjangan (Hari)</label>
                            <div class="d-flex gap-2 flex-wrap">
                                @for ($i = 1; $i <= 7; $i++)
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="extension_days" id="ext{{ $i }}" value="{{ $i }}" required>
                                        <label class="form-check-label" for="ext{{ $i }}">{{ $i }} hari</label>
                                    </div>
                                @endfor
                            </div>
                            @error('extension_days')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <h6>Estimasi Biaya</h6>
                            <div class="d-flex justify-content-between">
                                <div>Harga Per 24 Jam</div>
                                <div>Rp {{ number_format($vehiclePrice->price_24_hours, 0, ',', '.') }}</div>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold">
                                <div>Biaya Perpanjangan</div>
                                <div>Rp <span id="totalPrice">0</span></div>
                            </div>
                        </div>

                        <div class="alert alert-warning">Perpanjangan akan dikirimkan ke admin untuk persetujuan. Anda akan menerima link pembayaran setelah admin menyetujui.</div>

                        <div class="d-flex gap-2">
                            <a href="{{ route('user.rentals.index') }}" class="btn btn-light">Batal</a>
                            <button type="submit" class="btn btn-primary">Ajukan Perpanjangan</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        const pricePerDay = {{ $vehiclePrice->price_24_hours }};
        document.querySelectorAll('input[name="extension_days"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const totalPrice = pricePerDay * this.value;
                document.getElementById('totalPrice').textContent = new Intl.NumberFormat('id-ID').format(totalPrice);
            });
        });
    </script>
@endpush
