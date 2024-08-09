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
    <li class="sidebar-item {{ request()->is('admin/reservations*') ? 'active' : '' }}">
        <a href="{{ route('admin.reservations.index') }}" class='sidebar-link'>
            <i class="bi bi-calendar-check"></i>
            <span>Reservasi</span>
        </a>
    </li>
    <li class="sidebar-item {{ request()->is('admin/rentals*') ? 'active' : '' }}">
        <a href="{{ route('admin.rentals.index') }}" class='sidebar-link'>
            <i class="bi bi-calendar"></i>
            <span>Penyewaan</span>
        </a>
    </li>
    <li class="sidebar-item {{ request()->is('admin/users*') ? 'active' : '' }}">
        <a href="{{ route('admin.users.index') }}" class='sidebar-link'>
            <i class="bi bi-person"></i>
            <span>Pengguna</span>
        </a>
    </li>

    <li class="sidebar-title">Laporan</li>
    <li class="sidebar-item {{ request()->is('admin/reports*') ? 'active' : '' }}">
        <a href="{{ route('admin.reports.index') }}" class='sidebar-link'>
            <i class="bi bi-file-earmark-text"></i>
            <span>Laporan</span>
        </a>
    </li>

</ul>
