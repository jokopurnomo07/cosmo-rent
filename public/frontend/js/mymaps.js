var map = L.map("map", {
    center: [-7.7945047, 110.3803253],
    zoom: 12,
});

// Set map tiles source
var osm = L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution:
        'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors',
    maxZoom: 18,
}).addTo(map);

var Esri_WorldImagery = L.tileLayer(
    "https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}",
    {
        attribution:
            "Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community",
    }
);

// Add marker
var marker = L.marker([-7.7945047, 110.3803253], { draggable: true })
    .addTo(map)
    .bindPopup("Yogyakarta, Indonesia")
    .openPopup();

// Scale bar
L.control
    .scale({
        imperial: false,
    })
    .addTo(map);

// Add layer control
var baseMaps = {
    OSM: osm,
    "Esri World Imagery": Esri_WorldImagery,
};

var overlayMaps = {
    Marker: marker,
};

L.control.layers(baseMaps, overlayMaps).addTo(map);

// Function to update textarea with city name and hidden fields with coordinates
function updateAddress(latLng) {
    var lat = latLng.lat;
    var lng = latLng.lng;
    
    // Update hidden fields
    document.getElementById("lat").value = lat;
    document.getElementById("lng").value = lng;

    // Reverse geocoding to get city name in Indonesian
    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=10&addressdetails=1&accept-language=id`)
        .then(response => response.json())
        .then(data => {
            document.getElementById("alamat").value = data.display_name;
            marker.bindPopup(data.display_name).setLatLng(latLng).openPopup();
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById("alamat").value = "Tidak dapat mengambil nama kota.";
        });
}

// Update textarea on marker dragend
marker.on("dragend", function () {
    updateAddress(marker.getLatLng());
});

// Handle map clicks to move marker
map.on("click", function (e) {
    var latLng = e.latlng;
    marker.setLatLng(latLng);
    updateAddress(latLng);
});

// Get the user's current location
if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
        function (position) {
            var lat = position.coords.latitude;
            var lng = position.coords.longitude;
            map.setView([lat, lng], 12);
            marker.setLatLng([lat, lng]);
            updateAddress({lat: lat, lng: lng});
        },
        function (error) {
            console.error("Error getting location:", error);
            alert("Tidak dapat mengambil lokasi Anda.");
        }
    );
} else {
    alert("Geolocation tidak didukung oleh browser ini.");
}