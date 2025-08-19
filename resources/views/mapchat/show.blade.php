@extends('layouts.app')

@section('title', 'MapChat - Sala')

@section('content')
<div class="relative w-full" style="height: calc(100vh - 120px);">
        <div id="streetview" class="absolute inset-0" style="width:100%; height:100%;"></div>

        <div class="absolute bottom-5 left-1/2 -translate-x-1/2 transform">
                <button id="btn-comentarios" class="px-4 py-2 rounded-md shadow bg-blue-600 text-white hover:bg-blue-700 focus:outline-none">
                        💬 Comentários
                </button>
        </div>
</div>

<script>
    // Dados vindos do backend (1 local específico)
    window.isAuthenticated = @json(auth()->check());
    const MC_LOCATION = @json(($locations ?? [])[0] ?? null);

    function initStreet() {
        if (!MC_LOCATION) return;
        const pos = { lat: Number(MC_LOCATION.lat), lng: Number(MC_LOCATION.lng) };

        const pano = new google.maps.StreetViewPanorama(document.getElementById('streetview'), {
            position: pos,
            pov: { heading: 165, pitch: 0 },
            zoom: 1,
            disableDefaultUI: true,
            showRoadLabels: false
        });

        // Avatar no Street View
        const avatarUrl = MC_LOCATION.avatar ? '/images/' + MC_LOCATION.avatar : '/images/default.gif';
        const avatar = new google.maps.Marker({
            position: pos,
            map: pano,
            icon: {
                url: avatarUrl,
                scaledSize: new google.maps.Size(60, 80),
                anchor: new google.maps.Point(30, 80)
            },
            title: MC_LOCATION.name || 'Local'
        });

        const openComments = () => window.MapChat && window.MapChat.showPostModal(MC_LOCATION);
        avatar.addListener('click', openComments);
        document.getElementById('btn-comentarios')?.addEventListener('click', openComments);
    }
</script>

{{-- Carrega a API do Google Maps somente aqui, com callback para initStreet --}}
@section('scripts')
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBzEzusC_k3oEoPnqynq2N4a0aA3arzH-c&callback=initStreet"></script>
@endsection
@endsection
