<header>
    <nav class="navbar navbar-expand navbar-light navbar-top">
        <div class="container-fluid">
            <a href="#" class="burger-btn d-block">
                <i class="bi bi-justify fs-3"></i>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto mb-lg-0">
                    <li class="nav-item dropdown me-3">
                        <a class="nav-link active dropdown-toggle text-gray-600" href="#"
                            data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                            <i class='bi bi-bell bi-sub fs-4'></i>
                            <span class="badge badge-notification bg-danger" id="notification-count">0</span>
                        </a>
                        <ul class="dropdown-menu dropdown-center  dropdown-menu-sm-end notification-dropdown"
                            aria-labelledby="dropdownMenuButton">
                            <li class="dropdown-header">
                                <h6>Notifications</h6>
                            </li>
                            <div id="notification-list">
                                @foreach($notifications as $notification)
                                <li class="dropdown-item notification-item" data-id="{{ $notification->id }}" onclick="markAsRead({{ $notification->id }})">
                                    <a class="d-flex align-items-center" href="#">
                                        <div class="notification-icon bg-primary">
                                            <i class="bi bi-calendar-check"></i>
                                        </div>
                                        @can('isAdmin')
                                        <div class="notification-text ms-4">
                                            <p class="notification-title font-bold">Reservasi Baru</p>
                                            <p class="notification-subtitle font-thin text-sm">Ada reservasi baru dengan nomor transaksi berikut {{ $notification->data['trx_id'] }}</p>
                                        </div>
                                        @else
                                        <div class="notification-text ms-4">
                                            <p class="notification-title font-bold">Reservasi Baru</p>
                                            <p class="notification-subtitle font-thin text-sm">Anda melakukan reservasi baru dengan nomor transaksi berikut {{ $notification->data['trx_id'] }}</p>
                                        </div>
                                        @endcan
                                    </a>
                                </li>
                                @endforeach
                            </div>
                            <li>
                                <p class="text-center py-2 mb-0" id="mark-all-as-read"><a href="#">Tandai Semua Sudah Dibaca</a></p>
                            </li>
                        </ul>
                    </li>
                </ul>
                <div class="dropdown">
                    <a href="#" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="user-menu d-flex">
                            <div class="user-name text-end me-3">
                                <h6 class="mb-0 text-gray-600">{{ Auth::user()->name }}</h6>
                                <p class="mb-0 text-sm text-gray-600">{{ Auth::user()->hasRole('admin') ? 'Administrator' : 'User' }}</p>
                            </div>
                            <div class="user-img d-flex align-items-center">
                                <div class="avatar avatar-md">
                                    <img src="{{ asset('admin') }}/assets/compiled/jpg/1.jpg">
                                </div>
                            </div>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton"
                        style="min-width: 11rem;">
                        <li>
                            <h6 class="dropdown-header">Hello, {{ Auth::user()->name }}!</h6>
                        </li>
                        <li><a class="dropdown-item" href="#"><i class="icon-mid bi bi-person me-2"></i> My
                                Profile</a></li>
                        <li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('home') }}">
                                <i class="icon-mid bi bi-house me-2"></i>
                                Halaman Utama
                            </a>
                        </li>
                        <!-- Logout Form -->
                        <li>
                            <form action="{{ route('logout') }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="dropdown-item"><i
                                        class="icon-mid bi bi-box-arrow-left me-2"></i> Logout</button>
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
        // Enable pusher logging - don't include this in production
        Pusher.logToConsole = true;

        var pusher = new Pusher('f98b3d4b520dc87e2e8c', {
        cluster: 'ap1'
        });

        var channel = pusher.subscribe('reservations');

        // Bind to the event
        channel.bind('reservation.created', function(data) {
            // Call the function to handle the notification
            handleNotification(data);
        });

        // Function to handle the notification
        function handleNotification(data) {
            // Format the date if needed
            var createdAt = new Date(data.reservation.created_at);
            var formattedDate = formatDate(createdAt); 
            // Create the notification HTML
            var notificationHtml = `
                <li class="dropdown-item notification-item" data-id="${data.reservation.id}" onclick="markAsRead(${data.reservation.id})">
                    <a class="d-flex align-items-center" href="/admin/reservations/index/pending">
                        <div class="notification-icon bg-primary">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <div class="notification-text ms-4">
                            <p class="notification-title font-bold">Reservasi Baru</p>
                            <p class="notification-subtitle font-thin text-sm">Ada reservasi baru nih dengan nomor transaksi berikut ${data.reservation.trx_id}</p>
                        </div>
                    </a>
                </li>
            `;

            // Append the new notification to the list
            $('#notification-list').prepend(notificationHtml);

            // Update the notification count
            updateNotificationCount();
        }

        // Function to format the date to d-m-Y
        function formatDate(date) {
            var day = ("0" + date.getDate()).slice(-2);
            var month = ("0" + (date.getMonth() + 1)).slice(-2);
            var year = date.getFullYear();
            return day + "-" + month + "-" + year;
        }

        // Function to update the notification count
        function updateNotificationCount() {
            // Get the number of unread notifications
            var unreadCount = $('#notification-list .notification-item:not(.read)').length;
            $('#notification-count').text(unreadCount);
        }

        // Function to mark notifications as read
        function markAsRead(notificationId) {
            $.ajax({
                url: '/notifications/mark-as-read',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    notification_id: notificationId
                },
                success: function(response) {
                    // Optionally update the notification appearance or remove it
                    $(`[data-id="${notificationId}"]`).addClass('read');
                    
                    // Update the notification count
                    updateNotificationCount();
                },
                error: function(xhr) {
                    console.error('Error marking notification as read:', xhr);
                }
            });
        }

        // Initial call to update notification count on page load
        $(document).ready(function() {
            $('#mark-all-as-read').on('click', function(event) {
                event.preventDefault(); // Prevent default link behavior
                
                $.ajax({
                    url: '/notifications/mark-all-as-read',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        // Optionally update the notification appearance or remove it
                        $('#notification-list .notification-item').addClass('read');
                        updateNotificationCount(); // Update count after marking all as read
                    },
                    error: function(xhr) {
                        console.error('Error marking all notifications as read:', xhr);
                    }
                });
            });

            updateNotificationCount();
        });

    </script>

@endpush