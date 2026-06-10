@extends('layouts.app')

@extends('layouts.admin.app')
@section('title', 'Permintaan Perpanjangan')
@section('content')
    <div class="page-heading">
        <h3>Permintaan Perpanjangan Saya</h3>
    </div>
    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                        <th>#</th>
                        <th>Kendaraan</th>
                        <th>Diminta Sampai</th>
                        <th>Biaya</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($extensions as $ext)
                        <tr class="border-t">
                            <td class="py-2">{{ $loop->iteration }}</td>
                            <td class="py-2">{{ $ext->rental->vehicle?->name ?? '-' }}</td>
                            <td class="py-2">{{ $ext->extended_until?->format('d M Y, H:i') ?? '-' }}</td>
                            <td class="py-2">Rp {{ number_format($ext->additional_price,0,',','.') }}</td>
                            <td class="py-2">{{ ucfirst($ext->status) }}</td>
                            <td class="py-2">
                                <a href="/user/rentals/{{ $ext->rental->id }}" class="text-blue-600">Lihat Rental</a>
                                @if($ext->status === 'approved')
                                    <form action="{{ route('user.extensions.pay', $ext->id) }}" method="POST" class="d-inline ml-2">
                                        @csrf
                                        <button class="ml-2 inline-block px-3 py-1 bg-blue-600 text-white rounded">Bayar</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-4 text-center text-gray-500">Belum ada permintaan perpanjangan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-4">
                    </div>

                    <div class="mt-3">
                        {{ $extensions->links() }}
                    </div>
                {{ $extensions->links() }}
            </div>
        </section>
    </div>
@endsection
