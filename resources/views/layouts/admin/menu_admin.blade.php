<ul class="menu">

    <li class="sidebar-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <a href="{{ route('admin.dashboard') }}" class='sidebar-link'>
            <i class="bi bi-grid-fill"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <li class="sidebar-title">Manajemen</li>

    <li class="sidebar-item {{ request()->is('admin/vehicles*') ? 'active' : '' }}">
        <a href="{{ route('admin.vehicles.index') }}" class='sidebar-link'>
            <i class="bi bi-car-front"></i>
            <span>Kendaraan</span>
        </a>
    </li>
    <li class="sidebar-item {{ request()->is('admin/reservations*') ? 'active' : '' }} has-sub">
        <a href="#" class='sidebar-link'>
            <i class="bi bi-calendar-check"></i>
            <span>Reservasi</span>
        </a>

        <ul class="submenu">
            <li class="submenu-item {{ (Route::is('admin.reservations.index') && request('status') == 'pending') ? 'active' : '' }}">
                <a href="{{ route('admin.reservations.index', ['status' => 'pending']) }}" class="submenu-link">Baru</a>
            </li>
            <li class="submenu-item {{ (Route::is('admin.reservations.index') && request('status') == 'canceled') ? 'active' : '' }}">
                <a href="{{ route('admin.reservations.index', ['status' => 'canceled']) }}" class="submenu-link">Dibatalkan / Ditolak</a>
            </li>
            <li class="submenu-item {{ (Route::is('admin.reservations.index') && request('status') == 'confirmed') ? 'active' : '' }}">
                <a href="{{ route('admin.reservations.index', ['status' => 'confirmed']) }}" class="submenu-link">Dikonfirmasi</a>

            </li>
        </ul>
    </li>
    <li class="sidebar-item {{ request()->is('admin/rentals*') ? 'active' : '' }} has-sub">
        <a href="#" class='sidebar-link'>
            <i class="bi bi-calendar"></i>
            <span>Penyewaan</span>
        </a>

        <ul class="submenu">
            <li class="submenu-item {{ (Route::is('admin.rentals.index') && request('status') == 'paid') ? 'active' : '' }}">
                <a href="{{ route('admin.rentals.index', ['status' => 'paid']) }}" class="submenu-link">Baru</a>
            </li>
            <li class="submenu-item {{ (Route::is('admin.rentals.index') && request('status') == 'ongoing') ? 'active' : '' }}">
                <a href="{{ route('admin.rentals.index', ['status' => 'ongoing']) }}" class="submenu-link">Berlangsung</a>
            </li>
            <li class="submenu-item {{ (Route::is('admin.rentals.index') && request('status') == 'returned') ? 'active' : '' }}">
                <a href="{{ route('admin.rentals.index', ['status' => 'returned']) }}" class="submenu-link">Selesai</a>

            </li>
        </ul>
    </li>
    <li class="sidebar-item {{ request()->is('admin/users*') ? 'active' : '' }}">
        <a href="{{ route('admin.users.index') }}" class='sidebar-link'>
            <i class="bi bi-person"></i>
            <span>Pengguna</span>
        </a>
    </li>

    {{-- <li class="sidebar-title">Laporan</li>
    <li class="sidebar-item {{ request()->is('admin/reports*') ? 'active' : '' }}">
        <a href="{{ route('admin.reports.index') }}" class='sidebar-link'>
            <i class="bi bi-file-earmark-text"></i>
            <span>Laporan</span>
        </a>
    </li> --}}

</ul>
