<ul class="menu">

    <li class="sidebar-item {{ request()->routeIs('user.dashboard') ? 'active' : '' }}">
        <a href="{{ route('user.dashboard') }}" class='sidebar-link'>
            <i class="bi bi-grid-fill"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <li class="sidebar-title">Manajemen</li>
    <li class="sidebar-item {{ request()->is('user/reservations*') ? 'active' : '' }}">
        <a href="{{ route('user.reservations.index') }}" class='sidebar-link'>
            <i class="bi bi-calendar-check"></i>
            <span>Reservasiku</span>
        </a>
    </li>

    <li class="sidebar-item {{ request()->is('user/rentals*') ? 'active' : '' }}">
        <a href="{{ route('user.rentals.index') }}" class='sidebar-link'>
            <i class="bi bi-calendar"></i>
            <span>Sewaku</span>
        </a>
    </li>

    <li class="sidebar-item {{ request()->routeIs('user.history.index') ? 'active' : '' }}">
        <a href="{{ route('user.history.index') }}" class='sidebar-link'>
            <i class="bi bi-clock-history"></i>
            <span>Riwayat</span>
        </a>
    </li>

</ul>