import Echo from 'laravel-echo';
window.Pusher = require('pusher-js');

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: window.pusherKey,
    cluster: window.pusherCluster,
    encrypted: true,
});

window.Echo.channel('reservations')
    .listen('.reservation.created', (event) => {
        console.log('Reservation Created:', event.reservation);
    });
