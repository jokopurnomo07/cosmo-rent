@extends('layouts.admin.app')
@section('title', 'Status Pembayaran Perpanjangan')
@section('content')
    <div class="page-heading">
        <h3>Status Pembayaran Perpanjangan</h3>
    </div>
    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-body">
        <!-- Success State -->
        @if (in_array($transactionStatus, ['capture', 'settlement']))
            <div class="text-center">
                <h4 class="text-success">Pembayaran Berhasil!</h4>
                <p class="mb-3">Perpanjangan rental Anda telah berhasil diproses.</p>

                <div class="alert alert-success text-start">
                    <ul class="mb-0">
                        <li>Pembayaran diterima dan diproses</li>
                        <li>Durasi sewa telah diperpanjang</li>
                        <li>Anda dapat terus menggunakan kendaraan</li>
                    </ul>
                </div>

                <a href="{{ route('user.rentals.index') }}" class="btn btn-primary">Kembali ke Penyewaan Saya</a>
            </div>
        @elseif ($transactionStatus === 'pending')
            <div class="text-center">
                <h4 class="text-warning">Pembayaran Sedang Diproses</h4>
                <p class="mb-3">Kami sedang memproses pembayaran Anda. Ini mungkin memakan waktu beberapa saat.</p>
                <p class="text-muted small">Jangan tutup halaman ini sampai proses selesai.</p>
                <a href="{{ route('user.rentals.index') }}" class="btn btn-light mt-3">Kembali ke Penyewaan</a>
            </div>
        @else
            <div class="text-center">
                <h4 class="text-danger">Pembayaran Gagal</h4>
                <p class="mb-3">Pembayaran Anda gagal atau dibatalkan. Silakan coba lagi.</p>
                <a href="{{ route('user.rentals.index') }}" class="btn btn-primary">Kembali ke Penyewaan</a>
            </div>
        @endif
                </div>
            </div>
        </section>
    </div>
@endsection
