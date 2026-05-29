@extends('layouts.admin.app')
@section('title', 'Data Reservasi')

@section('content')
    @php
        if (request('status') === 'pending') {
            $title = 'Pending';
        } elseif (request('status') === 'canceled') {
            $title = 'Dibatalkan / Ditolak';
        } else {
            $title = 'Dikonfirmasi';
        }
        $isPending  = request('status') === 'pending';
        $isCanceled = request('status') === 'canceled';
    @endphp

    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last"></div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Data Reservasi {{ $title }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <section class="section">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Data Reservasi {{ $title }}</h5>
                    <a href="{{ route('admin.reservations.create') }}">
                        <button type="button" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Reservasi
                        </button>
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="table1">
                            <thead>
                                <tr>
                                    <th style="width:4%;">No</th>
                                    <th>Trx ID</th>
                                    <th>Nama Pemesan</th>
                                    <th class="d-none d-md-table-cell">Tgl Mulai</th>
                                    <th class="d-none d-md-table-cell">Tgl Selesai</th>
                                    <th class="d-none d-lg-table-cell">Email</th>
                                    <th class="d-none d-lg-table-cell">No HP</th>
                                    {{-- Availability column: hidden for canceled tab --}}
                                    @if (! $isCanceled)
                                        <th class="d-none d-md-table-cell">Ketersediaan</th>
                                    @endif
                                    {{-- Reason column: only for canceled tab --}}
                                    @if ($isCanceled)
                                        <th class="d-none d-lg-table-cell">Alasan</th>
                                    @endif
                                    <th>Status</th>
                                    <th style="width:8%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($reservation as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <small class="text-muted">{{ $item->trx_id }}</small>
                                        </td>
                                        <td class="text-truncate" style="max-width:130px;">
                                            {{ $item->user?->name ?? '—' }}
                                        </td>
                                        <td class="d-none d-md-table-cell">
                                            {{ date('d-m-Y', strtotime($item->start_date)) }}
                                        </td>
                                        <td class="d-none d-md-table-cell">
                                            {{ date('d-m-Y', strtotime($item->end_date)) }}
                                        </td>
                                        <td class="d-none d-lg-table-cell text-truncate" style="max-width:140px;">
                                            {{ $item->user?->email ?? '—' }}
                                        </td>
                                        <td class="d-none d-lg-table-cell">
                                            {{ $item->user?->phone ?? '—' }}
                                        </td>

                                        {{-- ── Availability / Conflict column ─────────────────── --}}
                                        @if (! $isCanceled)
                                            <td class="d-none d-md-table-cell">
                                                @if ($conflictMap[$item->id])
                                                    <span class="badge bg-danger"
                                                          data-bs-toggle="tooltip"
                                                          data-bs-placement="top"
                                                          title="Konflik dengan TRX: {{ $conflictMap[$item->id] }}">
                                                        <i class="bi bi-exclamation-triangle-fill me-1"></i>Ada Konflik
                                                    </span>
                                                @else
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-check-circle-fill me-1"></i>Tersedia
                                                    </span>
                                                @endif
                                            </td>
                                        @endif

                                        {{-- ── Reason column (canceled tab only) ─────────────── --}}
                                        @if ($isCanceled)
                                            <td class="d-none d-lg-table-cell text-truncate" style="max-width:150px;">
                                                {{ $item->reason_canceled ?? '—' }}
                                            </td>
                                        @endif

                                        {{-- ── Status column ──────────────────────────────────── --}}
                                        <td>
                                            @if ($isPending)
                                                <select class="form-select form-select-sm status-select2"
                                                        data-reservation-id="{{ $item->id }}">
                                                    <option value="pending"   {{ $item->status === 'pending'   ? 'selected' : '' }}>Menunggu</option>
                                                    <option value="confirmed" {{ $item->status === 'confirmed' ? 'selected' : '' }}>Konfirmasi</option>
                                                    <option value="rejected"  {{ $item->status === 'rejected'  ? 'selected' : '' }}>Tolak</option>
                                                    <option value="canceled"  {{ $item->status === 'canceled'  ? 'selected' : '' }}>Batalkan</option>
                                                </select>
                                            @elseif ($isCanceled)
                                                <span class="badge {{ $item->status === 'canceled' ? 'bg-warning text-dark' : 'bg-danger' }}">
                                                    {{ $item->status === 'canceled' ? 'Dibatalkan' : 'Ditolak' }}
                                                </span>
                                            @else
                                                <span class="badge bg-success">Dikonfirmasi</span>
                                            @endif
                                        </td>

                                        {{-- ── Action column ──────────────────────────────────── --}}
                                        <td class="text-center">
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-placement="top"
                                                    title="Detail"
                                                    onclick="showDetail({{ $item->id }})">
                                                <i class="bi bi-info-circle-fill"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center text-muted py-4">
                                            Tidak ada data reservasi.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="d-flex justify-content-end mt-3">
                        {{ $reservation->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- ── Detail Modal ─────────────────────────────────────────────── --}}
    <div class="modal modal-lg fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalTitle">Detail Reservasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="contentModal">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Rejection / Cancellation Reason Modal ───────────────────── --}}
    <div class="modal fade" id="rejectionModal" tabindex="-1" aria-labelledby="rejectionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectionModalLabel">Masukkan Alasan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="rejectionForm">
                        <div class="mb-3">
                            <label for="rejectionReason" class="form-label">
                                Alasan <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" id="rejectionReason" rows="3"
                                      placeholder="Tuliskan alasan penolakan / pembatalan..." required></textarea>
                        </div>
                        <input type="hidden" id="reservationId" value="">
                        <input type="hidden" id="pendingStatus" value="">
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger">Simpan & Proses</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
$(document).ready(function () {

    // ── Activate Bootstrap tooltips ────────────────────────────────
    const tooltipEls = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipEls.forEach(el => new bootstrap.Tooltip(el));

    // ── Select2 on status dropdowns ────────────────────────────────
    $(".status-select2").select2({ theme: "bootstrap4", minimumResultsForSearch: Infinity });

    // ── Status change handler ──────────────────────────────────────
    $(document).on('change', '.status-select2', function () {
        const status        = $(this).val();
        const reservationId = $(this).data('reservation-id');

        if (status === 'rejected' || status === 'canceled') {
            // Show reason modal and remember which reservation + status
            $('#reservationId').val(reservationId);
            $('#pendingStatus').val(status);
            $('#rejectionReason').val('');
            $('#rejectionModalLabel').text(
                status === 'rejected' ? 'Alasan Penolakan' : 'Alasan Pembatalan'
            );
            $('#rejectionModal').modal('show');

            // If admin closes modal without submitting, revert select to 'pending'
            $('#rejectionModal').one('hidden.bs.modal', function () {
                if ($('#pendingStatus').val() !== '') {
                    $(`.status-select2[data-reservation-id="${reservationId}"]`).val('pending').trigger('change');
                }
            });
        } else {
            doUpdateStatus(reservationId, status, null);
        }
    });

    // ── Reason form submit ─────────────────────────────────────────
    $('#rejectionForm').on('submit', function (e) {
        e.preventDefault();
        const reservationId = $('#reservationId').val();
        const status        = $('#pendingStatus').val();
        const reason        = $('#rejectionReason').val().trim();

        if (!reason) {
            $('#rejectionReason').addClass('is-invalid');
            return;
        }

        // Clear pending status so modal-close doesn't trigger revert
        $('#pendingStatus').val('');
        $('#rejectionModal').modal('hide');
        doUpdateStatus(reservationId, status, reason);
    });

    $('#rejectionReason').on('input', function () {
        $(this).removeClass('is-invalid');
    });
});

// ── Detail modal ───────────────────────────────────────────────────
function showDetail(id) {
    $('#contentModal').html('<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>');
    $('#detailModal').modal('show');

    $.ajax({
        type: 'GET',
        url: '/admin/reservations/' + id,
        success: function (html) {
            $('#contentModal').html(html);
        },
        error: function () {
            $('#contentModal').html('<div class="alert alert-danger">Gagal memuat detail. Silakan coba lagi.</div>');
        }
    });
}

// ── AJAX status update ─────────────────────────────────────────────
function doUpdateStatus(reservationId, status, reason) {
    Swal.fire({
        title: 'Mohon tunggu...',
        text: 'Sedang memperbarui status reservasi.',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    $.ajax({
        url: "{{ route('admin.reservations.update-status') }}",
        type: 'POST',
        data: {
            _token: "{{ csrf_token() }}",
            id:     reservationId,
            status: status,
            reason: reason
        },
        success: function (response) {
            Swal.close();

            if (response.success) {
                let msg = response.message || 'Status berhasil diperbarui!';

                // Extra info when conflicts were auto-canceled
                if (response.auto_canceled && response.auto_canceled > 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Berhasil — Ada Konflik Ditemukan',
                        html: `<p>${msg}</p>
                               <p class="text-muted small">Reservasi yang otomatis dibatalkan sudah mendapat notifikasi email.</p>`,
                        confirmButtonText: 'OK'
                    }).then(() => location.reload());
                } else {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: msg,
                        timer: 1800,
                        showConfirmButton: false
                    }).then(() => location.reload());
                }
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: response.message || 'Gagal memperbarui status.' })
                    .then(() => location.reload());
            }
        },
        error: function (xhr) {
            Swal.close();
            const msg = xhr.responseJSON?.message || 'Terjadi kesalahan. Silakan coba lagi.';
            Swal.fire({ icon: 'error', title: 'Error', text: msg })
                .then(() => location.reload());
        }
    });
}
</script>
@endpush