@extends('layouts.admin.app')
@section('title', 'Data Penyewaan')

@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last"></div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Data Penyewaan</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <section class="section">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">
                        Data Penyewaan
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped" id="table1">
                        <thead>
                            <tr>
                                <th style="width: 5%;">No</th>
                                <th style="width: 25%;" class="text-truncate">Trx ID</th>
                                <th style="width: 25%;" class="text-truncate">Nama Pemesan</th>
                                <th class="d-none d-md-table-cell" style="width: 15%;">Tanggal Sewa</th>
                                <th class="d-none d-md-table-cell" style="width: 15%;">Tanggal Selesai</th>
                                <th class="d-none d-lg-table-cell" style="width: 20%;" class="text-truncate">Email Pemesan</th>
                                <th class="d-none d-lg-table-cell" style="width: 15%;">No HP Pemesan</th>
                                <th style="width: 10%;">Status</th>
                                <th style="width: 10%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rentals as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->trx_id }}</td>
                                        <td class="text-truncate" style="max-width: 100px;">
                                            {{ $item->user_id != null ? $item->user->name : $item->nama_guest }}
                                        </td>
                                        <td class="d-none d-md-table-cell">{{ date('d-m-Y', strtotime($item->start_date)) }}</td>
                                        <td class="d-none d-md-table-cell">{{ date('d-m-Y', strtotime($item->end_date)) }}</td>
                                        <td class="d-none d-lg-table-cell text-truncate" style="max-width: 100px;">
                                            {{ $item->user_id != null ? $item->user->email : $item->email_guest }}
                                        </td>
                                        <td class="d-none d-lg-table-cell">{{ $item->user_id != null ? $item->user->phone : $item->no_hp_guest }}</td>
                                        <td>
                                            @if (request('status') == "paid")
                                                <select class="form-select status-select2" data-reservation-id="{{ $item->id }}">
                                                    <option value="paid" {{ $item->status == 'paid' ? 'selected' : '' }}>Menunggu Konfirmasi</option>
                                                    <option value="ongoing" {{ $item->status == 'ongoing' ? 'selected' : '' }}>Berlangsung</option>
                                                    <option value="returned" {{ $item->status == 'returned' ? 'selected' : '' }}>Dikembalikan</option>
                                                </select>
                                            @elseif( request('status') == "ongoing" )
                                                <select class="form-select status-select2" data-reservation-id="{{ $item->id }}">
                                                    <option value="ongoing" {{ $item->status == 'ongoing' ? 'selected' : '' }}>Berlangsung</option>
                                                    <option value="returned" {{ $item->status == 'returned' ? 'selected' : '' }}>Dikembalikan</option>
                                                </select>
                                            @else
                                                <span class="badge bg-success">Selesai</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-outline-primary" onclick="detail({{ $item->id }})">
                                                <i class="bi bi-info-circle-fill"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </section>
    </div>

    <div class="modal modal-lg fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalTitle"></h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i data-feather="x"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="contentModal"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x d-block d-sm-none"></i>
                        <span class="d-none d-sm-block">Close</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            
            $(".status-select2").select2({
                theme: "bootstrap4",
            });

            $('.status-select2').on('change', function() {
                var status = $(this).val();
                var reservationId = $(this).data('reservation-id');

                updateStatus(reservationId, status, null);
            });

        });
        function detail(id){
            $.ajax({
                type: "GET",
                url: "/admin/rentals/" + id,
                success: function(response) {
                    $('#detailModalTitle').text('Detail Penyewaan')
                    $('#contentModal').html(response);
                    $('#detailModal').modal('show');
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching detail:', error);
                    alert('Failed to fetch detail. Please try again.');
                }
            });
        }

        function updateStatus(reservationId, status, reason) {
            // Show the loading animation before making the AJAX request
            Swal.fire({
                title: "Mohon tunggu",
                text: "Sedang memperbarui status...",
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "{{ route('admin.rentals.update-status') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: reservationId,
                    status: status,
                    reason: reason
                },
                success: function(response) {
                    Swal.close(); // Close the loading animation
                    if (response.success) {
                        Swal.fire({
                            icon: "success",
                            title: "Berhasil",
                            text: "Berhasil Update Status!",
                        }).then(() => {
                            location.reload(); // Reload the page after the alert is closed
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Gagal",
                            text: "Gagal Update Status!",
                        }).then(() => {
                            location.reload(); // Reload the page after the alert is closed
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.close(); // Close the loading animation if an error occurs
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "An error occurred. Please try again."
                    });
                }
            });
        }

    </script>
@endpush