@extends('layouts.app')

@section('title', 'MapChat - Converse no mapa!')

@section('content')
<div class="relative w-full" style="height: calc(100vh - 120px);">
    <div id="map" class="absolute left-0 top-0" style="width: 100%; height: 100%; z-index: 1;"></div>
    <div id="streetview" class="absolute left-0 top-0" style="width: 100%; height: 100%; display: none; z-index: 2;"></div>
    
    <div id="streetview-error" class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 p-4 bg-white rounded-lg shadow-lg text-center" style="z-index: 11; display: none;">
        <p class="font-semibold text-gray-800">Oops! Street View indisponível.</p>
        <p class="text-sm text-gray-600">Não foi possível carregar a vista da rua para este local. Mostrando no mapa.</p>
    </div>

    <button id="btn-voltar-mapa" class="px-4 py-2 focus:outline-none absolute top-5 left-5" style="z-index: 10; display: none;">
        <img src="https://media1.giphy.com/media/v1.Y2lkPTc5MGI3NjExa3JmOW9vODF4OGJqMHpxNWJ4M3h3MXhncXF6NnZ6eHF6dnlucmwweCZlcD12MV9pbnRlcm5hbF9naWZfYnlfaWQmY3Q9cw/EOIQArrlGT8SeIvYma/giphy.gif" alt="Alternar para o mapa" style="width: 72px; height: 72px;">
    </button>
    <button id="btn-voltar-streetview" class="focus:outline-none absolute top-5 left-5" style="z-index: 10; display: none;">
        <img src="https://media4.giphy.com/media/v1.Y2lkPTc5MGI3NjExc28zbjI2dTRoaG4wZHRnMWhsNGZqYTNzMzVuNmNpN2M3NXVhc2RqZSZlcD12MV9pbnRlcm5hbF9naWZfYnlfaWQmY3Q9cw/S89ccIhj3e0xyMcLOp/giphy.gif" alt="Voltar para o Street View" style="width: 96px; height: 72px;">
    </button>

    <!-- Barra lateral com filtros integrados - COMEÇA ESCONDIDA -->
    <div id="chat-carousel" class="hide" style="position: absolute; top: 0; right: 0; bottom: 0; z-index: 20; background: rgba(255,255,255,0.95 ); box-shadow: -2px 0 12px rgba(0,0,0,0.08); padding: 0; display: flex; flex-direction: column; overflow-y: auto; align-items: center; min-width: 100px; max-width: 120px;">
        <div style="position: sticky; top: 0; background: rgba(255,255,255,0.98); padding: 12px 8px; border-bottom: 1px solid rgba(0,0,0,0.08); width: 100%; display: flex; align-items: center; justify-content: space-between; z-index: 21;">
            <div style="font-size: 0.85em; font-weight: 600; color: #198754;">FILTROS</div>
            <button id="btn-hide-sidebar" title="Esconder barra" style="background: none; border: none; color: #198754; font-size: 1.3em; cursor: pointer; z-index: 30; padding: 2px; margin-left: 8px;">&#10005;</button>
        </div>
        <div style="width: 100%; padding: 8px 0 0 0;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 6px; margin-bottom: 8px;">
                <button class="filter-btn active" data-filter="proximity" title="Próximos">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                    <span>Próx</span>
                </button>
                <button class="filter-btn" data-filter="recent" title="Recentes">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12,6 12,12 16,14"></polyline></svg>
                    <span>Recentes</span>
                </button>
            </div>
            <div id="filter-status" style="text-align: center; font-size: 0.7em; color: #6c757d; margin-bottom: 8px;">
                <span id="status-text">Carregando...</span>
            </div>
            <div style="width: 100%; text-align: center; margin-bottom: 10px;">
                <label for="post-limit" style="font-size: 0.85em; color: #198754; font-weight: 600;">Qtd. posts:</label>
                <select id="post-limit" style="margin-left: 6px; padding: 2px 6px; border-radius: 4px; border: 1px solid #ccc; font-size: 0.95em;">
                    <option value="20">20</option><option value="40">40</option><option value="60">60</option><option value="100">100</option>
                </select>
            </div>
        </div>
        <div id="avatars-container" style="flex: 1; width: 100%; padding: 0 6px 18px 6px; display: flex; flex-direction: column; gap: 18px; align-items: center; overflow-y: auto;">
        @foreach(($locations ?? []) as $loc)
            @if(empty($loc['no_gincana']))
            @php
                $avatar = $loc['avatar'] ?? ''; $avatar = trim($avatar); $avatar = $avatar ? basename($avatar) : 'default.gif';
                if (!$avatar || $avatar === '.' || $avatar === '..') $avatar = 'default.gif';
            @endphp
            <div class="carousel-item" data-lat="{{ $loc['lat'] }}" data-lng="{{ $loc['lng'] }}" style="flex: 0 0 auto; text-align: center; cursor: pointer; width: 100%; display: flex; flex-direction: column; align-items: center;">
                <img src="{{ asset('images/' . $avatar) }}" alt="Avatar" style="width: 56px; height: 56px; border-radius: 50%; border: 2px solid #ddd; margin-bottom: 4px; object-fit: cover;" onerror="this.onerror=null;this.src='{{ asset('images/default.gif') }}'">
                <div style="font-size: 0.98em; font-weight: 600; color: #198754; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 90px;">{{ $loc['name'] ?? $loc['nome'] ?? 'Sala' }}</div>
                <div style="font-size: 0.85em; color: #555; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 90px;">{{ $loc['cidade'] ?? '' }}</div>
            </div>
            @endif
        @endforeach
        </div>
    </div>

    <!-- Botão para MOSTRAR a barra lateral -->
    <button id="btn-show-sidebar" title="Mostrar barra lateral">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
    </button>
</div>

<style>
.filter-btn { display: flex; flex-direction: column; align-items: center; padding: 6px 4px; background: #f8f9fa; color: #6c757d; border: 1px solid #dee2e6; border-radius: 6px; cursor: pointer; transition: all 0.2s ease; font-size: 0.7em; font-weight: 500; }
.filter-btn:hover { background: #e9ecef; transform: translateY(-1px); }
.filter-btn.active { background: linear-gradient(135deg, #198754, #20c997); color: white; border: none; box-shadow: 0 2px 8px rgba(25, 135, 84, 0.3); }
.filter-btn svg { margin-bottom: 2px; }
.filter-btn.loading { opacity: 0.7; pointer-events: none; }
#avatars-container.loading .carousel-item { opacity: 0.5; }

#btn-show-sidebar {
    position: fixed; top: 90px; right: 18px; z-index: 50; background: #198754; color: #fff; border: none; border-radius: 50%;
    width: 48px; height: 48px; box-shadow: 0 2px 8px rgba(25,135,84,0.18);
    /* COMEÇA VISÍVEL */
    display: flex; 
    align-items: center; justify-content: center; font-size: 1.7em; cursor: pointer; transition: background 0.2s;
}
#btn-show-sidebar:hover { background: #20c997; }
#chat-carousel.hide { display: none !important; }
</style>

<script src="https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function ( ) {
    window.isAuthenticated = @json(auth()->check());
    const MC_LOCATIONS = @json($locations ?? []);

    let map, markers = [], panorama, markerCluster;
    let lastStreetViewLoc = null;
    let currentPosts = MC_LOCATIONS.filter(loc => !loc.no_gincana);
    let userPosition = null;
    let postLimit = 20;

    const chatCarousel = document.getElementById('chat-carousel');
    const btnHideSidebar = document.getElementById('btn-hide-sidebar');
    const btnShowSidebar = document.getElementById('btn-show-sidebar');
    const avatarsContainer = document.getElementById('avatars-container');
    const postLimitSelect = document.getElementById('post-limit');
    const btnVoltarMapa = document.getElementById('btn-voltar-mapa');
    const btnVoltarStreetview = document.getElementById('btn-voltar-streetview');

    function getUserLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    userPosition = { lat: position.coords.latitude, lng: position.coords.longitude };
                    console.log('Localização obtida:', userPosition);
                },
                (error) => { console.log('Erro na geolocalização:', error.message); }
            );
        }
    }

    function getAvatarUrl(avatar) {
        if (!avatar || avatar === '.' || avatar === '..') return '/images/default.gif';
        let file = avatar.split('/').pop();
        if (!file) file = 'default.gif';
        return '/images/' + file;
    }

    function focusMapOnLocation(loc, zoomLevel = 18) {
        if (map && loc) {
            document.getElementById('map').style.display = 'block';
            document.getElementById('streetview').style.display = 'none';
            btnVoltarMapa.style.display = 'none';
            map.setCenter({ lat: Number(loc.lat), lng: Number(loc.lng) });
            map.setZoom(zoomLevel);
        }
    }

    function handleStreetViewError(loc) {
        console.error("Erro ao carregar Street View:", loc);
        const errorDiv = document.getElementById('streetview-error');
        errorDiv.style.display = 'block';
        focusMapOnLocation(loc);
        setTimeout(() => errorDiv.style.display = 'none', 4000);
    }

    window.showStreetView = function(loc) {
        const pos = { lat: Number(loc.lat), lng: Number(loc.lng) };
        const streetViewService = new google.maps.StreetViewService();
        document.getElementById('streetview-error').style.display = 'none';
        streetViewService.getPanorama({ location: pos, radius: 50 }, (data, status) => {
            if (status === google.maps.StreetViewStatus.OK) {
                document.getElementById('map').style.display = 'none';
                document.getElementById('streetview').style.display = 'block';
                btnVoltarMapa.style.display = 'block';
                panorama = new google.maps.StreetViewPanorama(document.getElementById('streetview'), {
                    pano: data.location.pano, pov: { heading: 0, pitch: 0 }, zoom: 1,
                    disableDefaultUI: true, showRoadLabels: false, motionTracking: false
                });
                
                // Calcula posição do avatar 10 metros à frente da câmera
                const cameraLatLng = data.location.latLng;
                const initialHeading = panorama.getPov().heading || 0;
                
                // Posiciona o avatar 10 metros à frente da câmera na direção que ela está olhando
                const avatarLatLng = google.maps.geometry.spherical.computeOffset(
                    cameraLatLng,
                    10, // 10 metros à frente
                    initialHeading // direção inicial da câmera
                );
                
                const avatar = new google.maps.Marker({
                    position: avatarLatLng, 
                    map: panorama,
                    icon: { 
                        url: getAvatarUrl(loc.avatar), 
                        scaledSize: new google.maps.Size(60, 80), 
                        anchor: new google.maps.Point(30, 80) 
                    },
                    title: loc.name || 'Local'
                });
                
                avatar.addListener('click', () => window.MapChat && window.MapChat.showPostModal(loc));
                
                // Ajusta a câmera para olhar em direção ao avatar
                panorama.setPov({ 
                    heading: initialHeading, 
                    pitch: -5 // leve inclinação para baixo para ver melhor o avatar
                });
                
                lastStreetViewLoc = loc;
                btnVoltarStreetview.style.display = 'none';
            } else {
                handleStreetViewError(loc);
            }
        });
    }

    async function applyFilter(filterType) {
        showLoading(true);
        updateStatus('Carregando...');
        try {
            let filteredPosts = [];
            switch(filterType) {
                case 'proximity': filteredPosts = await getPostsByProximity(); break;
                case 'recent': filteredPosts = getPostsByRecent(); break;
                default: filteredPosts = currentPosts;
            }
            updateSidebar(filteredPosts);
            updateMapMarkers(filteredPosts);
            updateStatus(`${filteredPosts.length} posts encontrados`);
        } catch (error) {
            console.error('Erro ao aplicar filtro:', error);
            updateStatus('Erro ao carregar');
        } finally {
            showLoading(false);
        }
    }

    async function getPostsByProximity() {
        if (!userPosition) return getPostsByRecent();
        const postsWithDistance = currentPosts.map(post => ({ ...post, distance: calculateDistance(userPosition.lat, userPosition.lng, post.lat, post.lng) }));
        return postsWithDistance.sort((a, b) => a.distance - b.distance).slice(0, postLimit);
    }

    function getPostsByRecent() {
        return [...currentPosts].sort((a, b) => new Date(b.created_at || 0) - new Date(a.created_at || 0)).slice(0, postLimit);
    }

    function calculateDistance(lat1, lng1, lat2, lng2) {
        const R = 6371, dLat = (lat2 - lat1) * Math.PI / 180, dLng = (lng2 - lng1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLng/2) * Math.sin(dLng/2);
        return R * (2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)));
    }

    function updateSidebar(posts) {
        avatarsContainer.innerHTML = '';
        posts.forEach(post => {
            const item = document.createElement('div');
            item.className = 'carousel-item';
            item.setAttribute('data-lat', post.lat); item.setAttribute('data-lng', post.lng);
            item.style.cssText = 'flex: 0 0 auto; text-align: center; cursor: pointer; width: 100%; display: flex; flex-direction: column; align-items: center;';
            const distance = post.distance ? ` (${post.distance.toFixed(1)}km)` : '';
            item.innerHTML = `<img src="${getAvatarUrl(post.avatar)}" alt="Avatar" style="width: 56px; height: 56px; border-radius: 50%; border: 2px solid #ddd; margin-bottom: 4px; object-fit: cover;" onerror="this.onerror=null;this.src='/images/default.gif'"><div style="font-size: 0.98em; font-weight: 600; color: #198754; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 90px;">${post.name || post.nome || 'Sala'}</div><div style="font-size: 0.85em; color: #555; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 90px;">${post.cidade || ''}${distance}</div>`;
            avatarsContainer.appendChild(item);
        });
    }

    function updateMapMarkers(posts) {
        if (markerCluster) markerCluster.clearMarkers();
        markers.forEach(marker => marker.setMap(null));
        markers = [];
        posts.forEach(post => {
            const marker = new google.maps.Marker({
                position: { lat: Number(post.lat), lng: Number(post.lng) }, title: post.name || 'Local',
                icon: { url: getAvatarUrl(post.avatar), scaledSize: new google.maps.Size(50, 65), anchor: new google.maps.Point(25, 65) }
            });
            marker.addListener('click', () => window.MapChat && window.MapChat.showPostModal(post, { modo: 'mapa' }));
            markers.push(marker);
        });
        if (window.markerClusterer && window.markerClusterer.MarkerClusterer) {
            markerCluster = new markerClusterer.MarkerClusterer({ map, markers });
        }
        if (markers.length > 0) {
            const bounds = new google.maps.LatLngBounds();
            markers.forEach(marker => bounds.extend(marker.getPosition()));
            map.fitBounds(bounds);
        }
    }

    function showLoading(show) {
        const buttons = document.querySelectorAll('.filter-btn');
        avatarsContainer.classList.toggle('loading', show);
        buttons.forEach(btn => btn.classList.toggle('loading', show));
    }

    function updateStatus(text) {
        const statusEl = document.getElementById('status-text');
        if (statusEl) statusEl.textContent = text;
    }

    window.initMapChatHome = function() {
        if (!window.google || !window.google.maps) return;
        
        map = new google.maps.Map(document.getElementById('map'), {
            center: { lat: -14.2350, lng: -51.9253 }, zoom: 4, streetViewControl: false,
            mapTypeControl: false, fullscreenControl: false, gestureHandling: 'greedy'
        });
        
        // Inicializa marcadores com todos os posts
        updateMapMarkers(currentPosts);
        
        // Carrega primeiro post no Street View se disponível
        if (currentPosts.length > 0) {
            // Aguarda um pouco para o mapa carregar completamente
            setTimeout(() => {
                showStreetView(currentPosts[0]);
            }, 500);
        } else {
            document.getElementById('map').style.display = 'block';
            document.getElementById('streetview').style.display = 'none';
        }
        
        // Obtém localização do usuário
        getUserLocation();
        
        // Aplica filtro inicial com delay
        setTimeout(() => applyFilter('proximity'), 1500);
    }

    btnHideSidebar.addEventListener('click', () => {
        chatCarousel.classList.add('hide');
        btnShowSidebar.style.display = 'flex';
    });
    btnShowSidebar.addEventListener('click', function() {
        chatCarousel.classList.remove('hide');
        this.style.display = 'none';
    });
    postLimitSelect.addEventListener('change', function() {
        postLimit = parseInt(this.value, 10) || 20;
        const activeBtn = document.querySelector('.filter-btn.active');
        if (activeBtn) applyFilter(activeBtn.getAttribute('data-filter'));
    });
    btnVoltarMapa.addEventListener('click', function () {
        document.getElementById('map').style.display = 'block';
        document.getElementById('streetview').style.display = 'none';
        this.style.display = 'none';
        if (lastStreetViewLoc) btnVoltarStreetview.style.display = 'block';
        if (panorama) panorama.setVisible(false);
    });
    btnVoltarStreetview.addEventListener('click', function () {
        if (lastStreetViewLoc) {
            showStreetView(lastStreetViewLoc);
            this.style.display = 'none';
        }
    });
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            applyFilter(this.getAttribute('data-filter'));
        });
    });
    avatarsContainer.addEventListener('click', function(e) {
        const item = e.target.closest('.carousel-item');
        if (item) {
            const lat = parseFloat(item.getAttribute('data-lat'));
            const lng = parseFloat(item.getAttribute('data-lng'));
            
            // Busca primeiro nos posts filtrados/atualizados
            let loc = currentPosts.find(l => 
                Number(l.lat).toFixed(5) === lat.toFixed(5) && 
                Number(l.lng).toFixed(5) === lng.toFixed(5)
            );
            
            // Se não encontrar, busca nos dados originais
            if (!loc) {
                const allPosts = MC_LOCATIONS.filter(l => !l.no_gincana);
                loc = allPosts.find(l => 
                    Number(l.lat).toFixed(5) === lat.toFixed(5) && 
                    Number(l.lng).toFixed(5) === lng.toFixed(5)
                );
            }
            
            if (loc) {
                window.showStreetView(loc);
            } else {
                focusMapOnLocation({lat, lng});
            }
        }
    });
});
</script>
@endsection

@section('scripts')
    <script async defer src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY', 'SUA_CHAVE_API_AQUI' ) }}&libraries=geometry&callback=initMapChatHome"></script>
@endsection
