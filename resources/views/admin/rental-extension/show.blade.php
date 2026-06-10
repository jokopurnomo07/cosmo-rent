<div>
    <h6>Perpanjangan #{{ $extension->id }}</h6>
    <hr>
    <div class="row">
        <div class="col-md-6">
            <p><strong>Trx ID:</strong> {{ $extension->rental->trx_id ?? '-' }}</p>
            <p><strong>Pemesan:</strong> {{ $extension->rental->user?->name ?? $extension->rental->nama_guest ?? '-' }}</p>
            <p><strong>Email:</strong> {{ $extension->rental->user?->email ?? $extension->rental->email_guest ?? '-' }}</p>
            <p><strong>Telepon:</strong> {{ $extension->rental->user?->phone ?? $extension->rental->no_hp_guest ?? '-' }}</p>
        </div>
        <div class="col-md-6">
            <p><strong>Kendaraan:</strong> {{ $extension->rental->vehicle?->name ?? '-' }}</p>
            <p><strong>Saat ini berakhir:</strong> {{ $extension->rental->end_date?->format('d-m-Y H:i') ?? '-' }}</p>
            <p><strong>Diminta sampai:</strong> {{ $extension->extended_until?->format('d-m-Y H:i') ?? '-' }}</p>
            <p><strong>Biaya Tambahan:</strong> Rp {{ number_format($extension->additional_price,0,',','.') }}</p>
        </div>
    </div>

    @if($extension->admin_notes)
        <hr>
        <p><strong>Catatan Admin:</strong> {{ $extension->admin_notes }}</p>
    @endif

    <hr>
    <p><strong>Status:</strong> {{ ucfirst($extension->status) }}</p>

    @if($extension->status === 'pending')
    <div class="mt-3">
        <button class="btn btn-success" onclick="approveExtension({{ $extension->id }})">Setujui</button>
        <button class="btn btn-danger" onclick="rejectExtension({{ $extension->id }})">Tolak</button>
    </div>
    @endif
</div>
