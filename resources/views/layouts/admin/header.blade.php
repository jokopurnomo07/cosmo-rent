<header>
    <nav class="navbar navbar-expand navbar-light navbar-top">
        <div class="container-fluid">
            <a href="#" class="burger-btn d-block">
                <i class="bi bi-justify fs-3"></i>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto mb-lg-0">
                    <li class="nav-item dropdown me-3">
                        <a class="nav-link active dropdown-toggle text-gray-600" href="#"
                            data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                            <i class="bi bi-bell bi-sub fs-4"></i>
                            <span class="badge badge-notification bg-danger"
                                  id="notification-count"
                                  style="{{ $notifications->where('is_read', false)->count() === 0 ? 'display:none' : '' }}">
                                {{ $notifications->where('is_read', false)->count() }}
                            </span>
                        </a>

                        <ul class="dropdown-menu dropdown-center dropdown-menu-sm-end notification-dropdown"
                            aria-labelledby="dropdownMenuButton" style="min-width: 320px; max-height: 420px; overflow-y: auto;">
                            <li class="dropdown-header d-flex justify-content-between align-items-center px-3 py-2">
                                <h6 class="mb-0">Notifikasi</h6>
                                @if ($notifications->where('is_read', false)->count() > 0)
                                    <a href="#" id="mark-all-as-read" class="text-sm text-primary">
                                        Tandai semua dibaca
                                    </a>
                                @endif
                            </li>
                            <li><hr class="dropdown-divider m-0"></li>

                            <div id="notification-list">
                                @forelse ($notifications as $notification)
                                    <li class="notification-item {{ $notification->is_read ? 'read' : 'unread' }}"
                                        data-id="{{ $notification->id }}"
                                        style="cursor: pointer;">
                                        <a class="dropdown-item d-flex align-items-start py-2 px-3 {{ !$notification->is_read ? 'bg-light' : '' }}"
                                           href="#"
                                           onclick="markAsRead(event, {{ $notification->id }})">
                                            <div class="notification-icon bg-primary rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0"
                                                 style="width: 36px; height: 36px;">
                                                <i class="bi bi-calendar-check text-white"></i>
                                            </div>
                                            <div class="notification-text">
                                                <p class="mb-0 fw-semibold" style="font-size: 0.85rem;">
                                                    {{ $notification->resolvedTitle }}
                                                </p>
                                                <p class="mb-0 text-muted" style="font-size: 0.78rem;">
                                                    {{ $notification->resolvedMessage }}
                                                </p>
                                                <p class="mb-0 text-muted" style="font-size: 0.75rem;">
                                                    {{ $notification->created_at->diffForHumans() }}
                                                </p>
                                            </div>
                                            @if (!$notification->is_read)
                                                <span class="ms-auto ps-2">
                                                    <span class="badge bg-primary rounded-pill" style="font-size: 0.6rem;">Baru</span>
                                                </span>
                                            @endif
                                        </a>
                                    </li>
                                @empty
                                    <li class="text-center text-muted py-4" id="empty-notification">
                                        <i class="bi bi-bell-slash fs-4 d-block mb-1"></i>
                                        <small>Tidak ada notifikasi</small>
                                    </li>
                                @endforelse
                            </div>
                        </ul>
                    </li>
                </ul>

                {{-- User dropdown --}}
                <div class="dropdown">
                    <a href="#" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="user-menu d-flex">
                            <div class="user-name text-end me-3">
                                <h6 class="mb-0 text-gray-600">{{ Auth::user()->name }}</h6>
                                <p class="mb-0 text-sm text-gray-600">
                                    {{ Auth::user()->hasRole('admin') ? 'Administrator' : 'User' }}
                                </p>
                            </div>
                            <div class="user-img d-flex align-items-center">
                                <div class="avatar avatar-md">
                                    <img src="{{ asset('admin') }}/assets/compiled/jpg/1.jpg">
                                </div>
                            </div>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" style="min-width: 11rem;">
                        <li><h6 class="dropdown-header">Hello, {{ Auth::user()->name }}!</h6></li>
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.index') }}">
                                <i class="icon-mid bi bi-person me-2"></i> My Profile
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="{{ route('home') }}">
                                <i class="icon-mid bi bi-house me-2"></i> Halaman Utama
                            </a>
                        </li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="icon-mid bi bi-box-arrow-left me-2"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
</header>

@push('scripts')
<script src="https://js.pusher.com/7.0/pusher.min.js"></script>
<script>
    // Pusher — aktif untuk semua pengguna terautentikasi agar notifikasi realtime tersedia
    @auth
    Pusher.logToConsole = false;

    var pusher = new Pusher('f98b3d4b520dc87e2e8c', { cluster: 'ap1' });
    var reservationsChannel = pusher.subscribe('reservations');
    var extensionsChannel = pusher.subscribe('extensions');

    reservationsChannel.bind('reservation.created', function (data) {
        addNotificationToList(data.reservation);
    });

    // New extension requested (for admins)
    extensionsChannel.bind('extension.requested', function (data) {
        addExtensionNotificationToList(data.extension);
    });

    // Extension approved (for user)
        // Extension paid (for user)
        extensionsChannel.bind('extension.paid', function (data) {
            addPaidExtensionNotificationToList(data.extension);
        });

    function addExtensionNotificationToList(extension) {
        $('#empty-notification').remove();

        var html = `
            <li class="notification-item unread" data-id="ext-${extension.id}" style="cursor: pointer;">
                <a class="dropdown-item d-flex align-items-start py-2 px-3 bg-light"
                   href="#"
                   onclick="$('#mark-all-as-read').trigger('click')">
                    <div class="notification-icon bg-primary rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0"
                         style="width: 36px; height: 36px;">
                        <i class="bi bi-arrow-clockwise text-white"></i>
                    </div>
                    <div class="notification-text">
                        <p class="mb-0 fw-semibold" style="font-size: 0.85rem;">Permintaan Perpanjangan</p>
                        <p class="mb-0 text-muted" style="font-size: 0.78rem;">${extension.vehicle_name || ''}</p>
                        <p class="mb-0 text-muted" style="font-size: 0.75rem;">Baru saja</p>
                    </div>
                    <span class="ms-auto ps-2">
                        <span class="badge bg-primary rounded-pill" style="font-size: 0.6rem;">Baru</span>
                    </span>
                </a>
            </li>
        `;

        $('#notification-list').prepend(html);
        updateBadgeCount(1);
    }

        function addPaidExtensionNotificationToList(extension) {
            $('#empty-notification').remove();

            var html = `
                <li class="notification-item unread" data-id="ext-paid-${extension.id}" style="cursor: pointer;">
                    <a class="dropdown-item d-flex align-items-start py-2 px-3 bg-light"
                       href="#"
                       onclick="$('#mark-all-as-read').trigger('click')">
                        <div class="notification-icon bg-success rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0"
                             style="width: 36px; height: 36px;">
                            <i class="bi bi-check2-all text-white"></i>
                        </div>
                        <div class="notification-text">
                            <p class="mb-0 fw-semibold" style="font-size: 0.85rem;">Perpanjangan Dibayar</p>
                            <p class="mb-0 text-muted" style="font-size: 0.78rem;">${extension.vehicle_name || ''}</p>
                            <p class="mb-0 text-muted" style="font-size: 0.75rem;">Baru saja</p>
                        </div>
                        <span class="ms-auto ps-2">
                            <span class="badge bg-success rounded-pill" style="font-size: 0.6rem;">Baru</span>
                        </span>
                    </a>
                </li>
            `;

            $('#notification-list').prepend(html);
            updateBadgeCount(1);
        }
    @endauth

    function markAsRead(event, notificationId) {
        event.preventDefault();

        $.ajax({
            url: '{{ route("notifications.markAsRead") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                notification_id: notificationId,
            },
            success: function (response) {
                var $item = $(`[data-id="${notificationId}"]`);
                $item.removeClass('unread').addClass('read');
                $item.find('a').removeClass('bg-light');
                $item.find('.badge').remove();

                updateBadgeCount(-1);

                if (response.url) {
                    window.location.href = response.url;
                }
            },
            error: function (xhr) {
                console.error('Error marking notification as read:', xhr);
            }
        });
    }

    $('#mark-all-as-read').on('click', function (event) {
        event.preventDefault();

        $.ajax({
            url: '{{ route("notifications.markAllAsRead") }}',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function () {
                $('.notification-item').removeClass('unread').addClass('read');
                $('.notification-item a').removeClass('bg-light');
                $('.notification-item .badge').remove();
                $('#notification-count').hide();
                $('#mark-all-as-read').hide();
            },
            error: function (xhr) {
                console.error('Error marking all as read:', xhr);
            }
        });
    });

    function updateBadgeCount(delta) {
        var $badge = $('#notification-count');
        var current = parseInt($badge.text()) || 0;
        var next = Math.max(0, current + delta);

        if (next === 0) {
            $badge.hide();
        } else {
            $badge.text(next).show();
        }
    }
</script>
@endpush