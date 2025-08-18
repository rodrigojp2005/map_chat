
@extends('layouts.app')

@section('title', 'MapChat - Conversas no mapa!')

@section('content')
<div class="relative w-full" style="height: calc(100vh - 120px);">
    <div id="map" class="absolute left-0 top-0" style="width: 100%; height: 100%; z-index: 1;"></div>
    <div id="streetview" class="absolute left-0 top-0" style="width: 100%; height: 100%; display: none; z-index: 2;"></div>
    <button id="btn-voltar-mapa" class="px-4 py-2 focus:outline-none absolute top-5 left-5" style="z-index: 10; display: none;">
        <img src="https://media0.giphy.com/media/v1.Y2lkPTc5MGI3NjExdWdkejl1cmF1azd1eGppcmdydmY3eXp6NDlmZmxwbW8xZmtnNHgzcCZlcD12MV9pbnRlcm5hbF9naWZfYnlfaWQmY3Q9cw/3fxOM1vBQJmCqqx5dV/giphy.gif" alt="Alternar para o mapa" style="width: 72px; height: 72px;">
    </button>
</div>

<script>
window.isAuthenticated = @json(auth()->check());
const MC_LOCATIONS = @json($locations ?? []);

let map, markers = [], panorama;

function showStreetView(loc) {
    document.getElementById('map').style.display = 'none';
    document.getElementById('streetview').style.display = 'block';
    document.getElementById('btn-voltar-mapa').style.display = 'block';
    const pos = { lat: Number(loc.lat), lng: Number(loc.lng) };
    panorama = new google.maps.StreetViewPanorama(document.getElementById('streetview'), {
        position: pos,
        pov: { heading: 165, pitch: 0 },
        zoom: 1,
        disableDefaultUI: true,
        showRoadLabels: false,
        motionTracking: false // desabilita giroscópio/mobile motion
    });
    // Avatar no Street View
    const avatar = new google.maps.Marker({
        position: pos,
        map: panorama,
        icon: {
            url: 'https://media1.giphy.com/media/v1.Y2lkPTc5MGI3NjExeTRweGJoMHk1eG5nb2tyOHMyMHp1ZGlpYTFoZDZ6Ym9zZ3ZkYXB2MSZlcD12MV9pbnRlcm5hbF9naWZfYnlfaWQmY3Q9cw/bvQHYGOF8UOXqXSFir/giphy.gif',
            scaledSize: new google.maps.Size(60, 80),
            anchor: new google.maps.Point(30, 80)
        },
        title: loc.name || 'Local'
    });
    avatar.addListener('click', () => window.MapChat && window.MapChat.showPostModal(loc));
}

function initMapChatHome() {
    if (!window.google || !window.google.maps) return;
    // Se houver pelo menos um local, já abre o Street View do primeiro
    if (Array.isArray(MC_LOCATIONS) && MC_LOCATIONS.length > 0 && !MC_LOCATIONS[0].no_gincana) {
        showStreetView(MC_LOCATIONS[0]);
    } else {
        // Se não houver locais, mostra o mapa vazio
        document.getElementById('map').style.display = 'block';
        document.getElementById('streetview').style.display = 'none';
        document.getElementById('btn-voltar-mapa').style.display = 'none';
    }
    // Inicializa o mapa (para quando clicar em "voltar")
    map = new google.maps.Map(document.getElementById('map'), {
        center: { lat: -14.2350, lng: -51.9253 },
        zoom: 4,
        streetViewControl: false,
        mapTypeControl: false,
        fullscreenControl: false,
        gestureHandling: 'greedy' // permite mover o mapa com um dedo no mobile
    });
    markers = [];
    if (Array.isArray(MC_LOCATIONS)) {
        MC_LOCATIONS.forEach(loc => {
            if (loc.no_gincana) return;
            const marker = new google.maps.Marker({
                position: { lat: Number(loc.lat), lng: Number(loc.lng) },
                map,
                title: loc.name || 'Local',
                icon: {
                    url: 'https://media1.giphy.com/media/v1.Y2lkPTc5MGI3NjExeTRweGJoMHk1eG5nb2tyOHMyMHp1ZGlpYTFoZDZ6Ym9zZ3ZkYXB2MSZlcD12MV9pbnRlcm5hbF9naWZfYnlfaWQmY3Q9cw/bvQHYGOF8UOXqXSFir/giphy.gif',
                    scaledSize: new google.maps.Size(50, 65),
                    anchor: new google.maps.Point(25, 65)
                }
            });
            marker.addListener('click', () => showStreetView(loc));
            markers.push(marker);
        });
    }
}

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('btn-voltar-mapa').addEventListener('click', function () {
        document.getElementById('map').style.display = 'block';
        document.getElementById('streetview').style.display = 'none';
        document.getElementById('btn-voltar-mapa').style.display = 'none';
        if (panorama) panorama.setVisible(false);
    });
});
</script>

@section('scripts')
    <script async defer src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY', 'AIzaSyBzEzusC_k3oEoPnqynq2N4a0aA3arzH-c') }}&callback=initMapChatHome"></script>
@endsection

@endsection
