@extends('layouts.admin.app')
@section('title', 'Permintaan Perpanjangan')

@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>Permintaan Perpanjangan</h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Permintaan Perpanjangan</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <section class="section">
            <div class="card">
                <div class="card-body">
                    <table class="table table-striped" id="extensionsTable">
                        <thead>
                            <tr>
                                <th style="width:5%">No</th>
                                <th>Rental</th>
                                <th>Pemesan</th>
                                <th>Perpanjang Sampai</th>
                                <th>Harga Tambahan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($extensions as $extension)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $extension->rental?->trx_id ?? '-' }}</td>
                                    <td>{{ $extension->rental?->user?->name ?? ($extension->rental?->nama_guest ?? '-') }}</td>
                                    <td>{{ $extension->extended_until?->format('d-m-Y H:i') ?? '-' }}</td>
                                    <td>Rp {{ number_format($extension->additional_price ?? 0,0,',','.') }}</td>
                                    <td>{{ ucfirst($extension->status) }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-outline-primary" onclick="openExtension({{ $extension->id }})">Lihat</button>

                                            @if($extension->status === 'pending')
                                                <button type="button" class="btn btn-success" onclick="approveExtension({{ $extension->id }})">Setujui</button>
                                                <button type="button" class="btn btn-danger" onclick="rejectExtension({{ $extension->id }})">Tolak</button>
                                            @endif

                                            @if($extension->status === 'approved' && $extension->payment_url)
                                                <a href="{{ $extension->payment_url }}" target="_blank" class="btn btn-warning">Bayar</a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-end mt-3">
                        {{ $extensions->links() }}
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- Detail Modal --}}
    <div class="modal fade" id="extensionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Perpanjangan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="extensionModalBody"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function openExtension(id) {
        fetch(`/admin/extensions/${id}`)
            .then(r => r.text())
            .then(html => {
                document.getElementById('extensionModalBody').innerHTML = html;
                const myModal = new bootstrap.Modal(document.getElementById('extensionModal'));
                myModal.show();
            }).catch(err => {
                console.error(err);
                Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal memuat detail perpanjangan.' });
            });
    }

    function approveExtension(id) {
        Swal.fire({
            title: 'Setujui perpanjangan?',
            text: 'Setujui perpanjangan ini dan buat link pembayaran?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, setujui',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (!result.isConfirmed) return;
            Swal.fire({ title: 'Please wait', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            fetch(`/admin/extensions/${id}/approve`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ notes: 'Disetujui oleh admin' })
            }).then(r => r.json()).then(j => {
                Swal.close();
                if (j.success) {
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: j.message }).then(() => location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: j.message || 'Gagal menyetujui.' });
                }
            }).catch(e => { console.error(e); Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan.' }); });
        });
    }

    function rejectExtension(id) {
        Swal.fire({
            title: 'Tolak perpanjangan',
            input: 'textarea',
            inputLabel: 'Alasan penolakan',
            inputPlaceholder: 'Masukkan alasan penolakan (min 5 karakter)',
            showCancelButton: true,
            confirmButtonText: 'Tolak',
            preConfirm: (value) => {
                if (!value || value.length < 5) {
                    Swal.showValidationMessage('Alasan harus minimal 5 karakter.');
                }
                return value;
            }
        }).then(result => {
            if (!result.isConfirmed) return;
            const reason = result.value;
            Swal.fire({ title: 'Please wait', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            fetch(`/admin/extensions/${id}/reject`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ reason })
            }).then(r => r.json()).then(j => {
                Swal.close();
                if (j.success) {
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: j.message }).then(() => location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: j.message || 'Gagal menolak.' });
                }
            }).catch(e => { console.error(e); Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan.' }); });
        });
    }

    function simulatePayment(id) {
        Swal.fire({
            title: 'Simulate payment?'
        , text: 'This will mark extension as paid and extend rental. For testing only.'
        , icon: 'warning'
        , showCancelButton: true
        , confirmButtonText: 'Simulate'
        }).then((result) => {
            if (!result.isConfirmed) return;
            Swal.fire({ title: 'Please wait', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            fetch(`/admin/extensions/${id}/simulate-payment`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken }
            }).then(r => r.json()).then(j => {
                Swal.close();
                if (j.success) {
                    Swal.fire({ icon: 'success', title: 'Simulated', text: j.message }).then(() => location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Failed', text: j.message || 'Simulation failed.' });
                }
            }).catch(e => { console.error(e); Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan.' }); });
        });
    }
</script>
@endpush
