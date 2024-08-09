<nav class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light" id="ftco-navbar">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">Cosmo<span>Rent</span></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav" aria-controls="ftco-nav"
            aria-expanded="false" aria-label="Toggle navigation">
            <span class="oi oi-menu"></span> Menu
        </button>

        <div class="collapse navbar-collapse" id="ftco-nav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item {{ request()->routeIs('home') ? 'active' : '' }}"><a href="{{ route('home') }}" class="nav-link">Home</a></li>
                <li class="nav-item {{ request()->routeIs('about') ? 'active' : '' }}"><a href="{{ route('about') }}" class="nav-link">Tentang Kami</a></li>
                <li class="nav-item {{ request()->is('vehicle*') ? 'active' : '' }}"><a href="{{ route('vehicles.index') }}" class="nav-link">Armada Kami</a></li>
                <li class="nav-item {{ request()->is('reservation*') ? 'active' : '' }}"><a href="{{ route('reservations.create') }}" class="nav-link">Pemesanan</a></li>
                <li class="nav-item {{ request()->routeIs('contact') ? 'active' : '' }}"><a href="{{ route('contact') }}" class="nav-link">Kontak</a></li>
                <li class="nav-item">
                    <a href="{{ route('login') }}" class="nav-link btn btn-success btn-sm">Login</a>
                </li>
            </ul>
        </div>        
    </div>
</nav>
