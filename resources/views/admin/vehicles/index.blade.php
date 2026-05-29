@extends('layouts.admin.app')
@section('title', 'Data Kendaraan')

@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last"></div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Data Kendaraan</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <section class="section">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Data Kendaraan</h5>
                    <a href="{{ route('admin.vehicles.create') }}">
                        <button type="button" class="btn btn-primary">Tambah Kendaraan</button>
                    </a>
                </div>
                <div class="card-body">
                    <table class="table table-striped text-center" id="table1">
                        <thead>
                            <tr>
                                <th class="text-center">No</th>
                                <th class="text-center">Foto</th>
                                <th class="text-center">Tipe Kendaraan</th>
                                <th class="text-center">Nama</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($vehicles as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        @if ($item->vehicle_images && Storage::disk('public')->exists($item->vehicle_images))
                                            <img src="{{ asset('storage/' . $item->vehicle_images) }}"
                                                 alt="{{ $item->name }}"
                                                 style="width: 60px; height: 45px; object-fit: cover; border-radius: 4px;">
                                        @else
                                            <div style="width: 60px; height: 45px; background: #f0f0f0; border-radius: 4px; display: inline-flex; align-items: center; justify-content: center;">
                                                <i class="bi bi-image text-muted"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ $item->type == 'car' ? 'Mobil' : 'Motor' }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td>
                                        @if ($item->status == 'available')
                                            <span class="badge bg-success">Tersedia</span>
                                        @elseif($item->status == 'maintenance')
                                            <span class="badge bg-danger">Sedang dalam Perbaikan</span>
                                        @else
                                            <span class="badge bg-info">Sedang di Rental</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group mb-3 btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-primary"
                                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Detail"
                                                    onclick="detail({{ $item->id }}, '{{ addslashes($item->name) }}')">
                                                <i class="bi bi-info-circle-fill"></i>
                                            </button>
                                            <a href="{{ route('admin.vehicles.edit', ['vehicle' => $item->id]) }}"
                                               class="btn btn-outline-primary"
                                               data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-primary"
                                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus"
                                                    onclick="remove({{ $item->id }})">
                                                <i class="bi bi-trash3-fill"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>

    <div class="modal modal-lg fade" id="detailModal" tabindex="-1" role="dialog"
         aria-labelledby="detailModalTitle" aria-hidden="true">
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
                        <span class="d-none d-sm-block">Close</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if(session('success_create'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil Menambahkan Kendaraan',
                    text: 'Data kendaraan baru telah berhasil ditambahkan.',
                    timer: 2000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                });
            @elseif(session('success_update'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil Mengubah Kendaraan',
                    text: 'Data kendaraan telah berhasil diperbarui.',
                    timer: 2000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                });
            @endif
        });

        function detail(id, title) {
            $.ajax({
                type: "GET",
                url: "/admin/vehicles/" + id,
                success: function (response) {
                    $('#detailModalTitle').text('Detail - ' + title);
                    $('#contentModal').html(response);
                    $('#detailModal').modal('show');
                },
                error: function (xhr, status, error) {
                    console.error('Error fetching detail:', error);
                    alert('Failed to fetch detail. Please try again.');
                }
            });
        }

        function remove(id) {
            Swal.fire({
                title: "Apakah anda yakin?",
                text: "Anda akan menghapus data ini secara permanen!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Hapus"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "DELETE",
                        url: "/admin/vehicles/" + id,
                        data: { "_token": "{{ csrf_token() }}" },
                        success: function (resp) {
                            if (resp.success) {
                                Swal.fire({ icon: "success", title: "Berhasil", text: "Menghapus Data Telah Berhasil!" });
                                location.reload();
                            } else {
                                Swal.fire({ icon: "error", title: "Oops...", text: "Something went wrong!" });
                                location.reload();
                            }
                        }
                    });
                }
            });
        }
    </script>
@endpush