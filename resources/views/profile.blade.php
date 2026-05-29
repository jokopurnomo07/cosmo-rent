@extends('layouts.admin.app')
@section('title', 'My Profile')

@section('content')
    <div class="page-heading">
        <h3>My Profile</h3>
    </div>
    <div class="page-content">
        <section class="row">
            <div class="col-12 col-lg-4">

                {{-- Profile Card --}}
                <div class="card">
                    <div class="card-body py-4 d-flex flex-column align-items-center text-center">
                        <div class="avatar avatar-xl mb-3">
                            <img
                                src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=random&size=128"
                                alt="Avatar"
                                class="rounded-circle"
                                style="width: 90px; height: 90px; object-fit: cover;"
                            >
                        </div>
                        <h5 class="font-extrabold mb-0">{{ auth()->user()->name }}</h5>
                        <p class="text-muted mb-1">{{ auth()->user()->email }}</p>
                        <span class="badge bg-light-primary mt-1">
                            {{ ucwords(auth()->user()->getRoleNames()->first() ?? 'User') }}
                        </span>
                        <div class="mt-3">
                            <h6 class="font-extrabold mb-0">{{ auth()->user()->created_at->format('d M Y') }}</h6>
                            <small class="text-muted">Bergabung sejak</small>
                        </div>
                    </div>
                </div>

                {{-- Change Password Card --}}
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title">Ubah Password</h5>
                    </div>
                    <div class="card-body">
                        @if (session('password_success'))
                            <div class="alert alert-success">{{ session('password_success') }}</div>
                        @endif

                        <form action="{{ route('profile.password') }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="form-group mb-3">
                                <label class="form-label">Password Lama</label>
                                <input
                                    type="password"
                                    name="current_password"
                                    class="form-control @error('current_password') is-invalid @enderror"
                                    placeholder="Masukkan password lama"
                                >
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Password Baru</label>
                                <input
                                    type="password"
                                    name="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    placeholder="Masukkan password baru"
                                >
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Konfirmasi Password Baru</label>
                                <input
                                    type="password"
                                    name="password_confirmation"
                                    class="form-control"
                                    placeholder="Ulangi password baru"
                                >
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-lock me-1"></i> Simpan Password
                            </button>
                        </form>
                    </div>
                </div>

            </div>

            <div class="col-12 col-lg-8">

                {{-- Edit Profile Card --}}
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Informasi Profil</h5>
                    </div>
                    <div class="card-body">
                        @if (session('profile_success'))
                            <div class="alert alert-success">{{ session('profile_success') }}</div>
                        @endif

                        <form action="{{ route('profile.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Nama Lengkap</label>
                                        <input
                                            type="text"
                                            name="name"
                                            class="form-control @error('name') is-invalid @enderror"
                                            value="{{ old('name', auth()->user()->name) }}"
                                            placeholder="Nama lengkap"
                                        >
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Email</label>
                                        <input
                                            type="email"
                                            name="email"
                                            class="form-control @error('email') is-invalid @enderror"
                                            value="{{ old('email', auth()->user()->email) }}"
                                            placeholder="Email"
                                        >
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">No. Telepon</label>
                                        <input
                                            type="text"
                                            name="phone"
                                            class="form-control @error('phone') is-invalid @enderror"
                                            value="{{ old('phone', auth()->user()->phone ?? '') }}"
                                            placeholder="No. telepon"
                                        >
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Alamat</label>
                                        <textarea
                                            name="address"
                                            class="form-control @error('address') is-invalid @enderror"
                                            rows="3"
                                            placeholder="Alamat lengkap"
                                        >{{ old('address', auth()->user()->address ?? '') }}</textarea>
                                        @error('address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-1"></i> Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Activity Log Card --}}
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title">Log Aktivitas Saya</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped" id="table-activity">
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>Deskripsi</th>
                                    <th>Waktu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($activityLog as $item)
                                    <tr>
                                        <td>{{ ucwords($item->event) }}</td>
                                        <td>{{ $item->description }}</td>
                                        <td>{{ $item->created_at->format('d M Y, H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">Belum ada aktivitas.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </section>
    </div>
@endsection