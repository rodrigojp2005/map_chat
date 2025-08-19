@extends('layouts.app')

@section('title', 'MapChat - Converse no mapa!')

@section('content')
<div class="relative w-full" style="height: calc(100vh - 120px);">
    <div id="map" class="absolute left-0 top-0" style="width: 100%; height: 100%; z-index: 1;"></div>
    <div id="streetview" class="absolute left-0 top-0" style="width: 100%; height: 100%; display: none; z-index: 2;"></div>
    
    <!-- Mensagem de erro para o Street View -->
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

    <!-- Barra lateral com filtros -->
    <div id="chat-carousel" style="position: absolute; top: 72px; right: 0; bottom: 0; z-index: 20; background: rgba(255,255,255,0.95); box-shadow: -2px 0 12px rgba(0,0,0,0.08); padding: 0; display: flex; flex-direction: column; overflow-y: auto; align-items: center; min-width: 120px; max-width: 160px;">
        
        <!-- Botão minimizar -->
        <button id="btn-minimizar" 
            style="position: absolute; top: 6px; left: -28px; background: #198754; color: white; 
                   border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; 
                   justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.2); cursor: pointer; z-index: 25;">
            «
        </button>

        <!-- Filtros -->
        <div style="position: sticky; top: 0; background: rgba(255,255,255,0.98); padding: 12px 8px; border-bottom: 1px solid rgba(0,0,0,0.08); width: 100%;">
            <div style="text-align: center; font-size: 0.85em; font-weight: 600; color: #198754; margin-bottom: 12px;">FILTROS</div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 6px;">
                <button class="filter-btn active" data-filter="proximity" title="Próximos">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                    <span>Próx</span>
                </button>
                <button class="filter-btn" data-filter="recent" title="Recentes">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12,6 12,12 16,14"></polyline>
                    </svg>
                    <span>Recentes</span>
                </button>
            </div>
            
            <div id="filter-status" style="text-align: center; font-size: 0.7em; color: #6c757d; margin-top: 8px;">
                <span id="status-text">Carregando...</span>
            </div>
        </div>

        <!-- Container dos avatares -->
        <div id="avatars-container" style="flex: 1; width: 100%; padding: 18px 6px; display: flex; flex-direction: column; gap: 18px; align-items: center;">
            @foreach(($locations ?? []) as $loc)
                @if(empty($loc['no_gincana']))
                <div class="carousel-item" data-lat="{{ $loc['lat'] }}" data-lng="{{ $loc['lng'] }}" style="flex: 0 0 auto; text-align: center; cursor: pointer; min-width: 80px; max-width: 100px;">
                    <img src="{{ $loc['avatar'] ? (Str::startsWith($loc['avatar'], 'http') ? $loc['avatar'] : 'https://media4.giphy.com/media/' . $loc['avatar']) : 'https://media1.giphy.com/media/v1.Y2lkPTc5MGI3NjExeTRweGJoMHk1eG5nb2tyOHMyMHp1ZGlpYTFoZDZ6Ym9zZ3ZkYXB2MSZlcD12MV9pbnRlcm5hbF9naWZfYnlfaWQmY3Q9cw/bvQHYGOF8UOXqXSFir/giphy.gif' }}"
                        alt="Avatar" style="width: 56px; height: 56px; border-radius: 50%; border: 2px solid #198754; margin-bottom: 4px; object-fit: cover;"
                        onerror="this.src='https://media1.giphy.com/media/v1.Y2lkPTc5MGI3NjExeTRweGJoMHk1eG5nb2tyOHMyMHp1ZGlpYTFoZDZ6Ym9zZ3ZkYXB2MSZlcD12MV9pbnRlcm5hbF9naWZfYnlfaWQmY3Q9cw/bvQHYGOF8UOXqXSFir/giphy.gif'">
                    <div style="font-size: 0.98em; font-weight: 600; color: #198754; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 90px;">{{ $loc['name'] ?? $loc['nome'] ?? 'Sala' }}</div>
                    <div style="font-size: 0.85em; color: #555; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 90px;">{{ $loc['cidade'] ?? '' }}</div>
                </div>
                @endif
            @endforeach
        </div>
    </div>

    <!-- Botão maximizar (aparece quando sidebar está recolhida) -->
    <button id="btn-maximizar" 
        style="position: absolute; top: 78px; right: 6px; background: #198754; color: white; 
               border-radius: 50%; width: 28px; height: 28px; display: none; 
               align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.2); 
               cursor: pointer; z-index: 25;">
        »
    </button>
</div>

<style>
.filter-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 6px 4px;
    background: #f8f9fa;
    color: #6c757d;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.7em;
    font-weight: 500;
}

.filter-btn:hover {
    background: #e9ecef;
    transform: translateY(-1px);
}

.filter-btn.active {
    background: linear-gradient(135deg, #198754, #20c997);
    color: white;
    border: none;
    box-shadow: 0 2px 8px rgba(25, 135, 84, 0.3);
}

.filter-btn svg {
    margin-bottom: 2px;
}

.filter-btn.loading {
    opacity: 0.7;
    pointer-events: none;
}

#avatars-container.loading .carousel-item {
    opacity: 0.5;
}
</style>

<script src="https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js"></script>

<script>
window.isAuthenticated = @json(auth()->check());
const MC_LOCATIONS = @json($locations ?? []);

let map, markers = [], panorama, markerCluster;
let lastStreetViewLoc = null;
let currentPosts = MC_LOCATIONS.filter(loc => !loc.no_gincana);
let userPosition = null;

// Geolocalização simples
function getUserLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                userPosition = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
            },
            (error) => {
                console.log('Erro na geolocalização:', error.message);
            }
        );
    }
}

function getAvatarUrl(avatar) {
    if (!avatar) return 'https://media1.giphy.com/media/v1.Y2lkPTc5MGI3NjExeTRweGJoMHk1eG5nb2tyOHMyMHp1ZGlpYTFoZDZ6Ym9zZ3ZkYXB2MSZlcD12MV9pbnRlcm5hbF9naWZfYnlfaWQmY3Q9cw/bvQHYGOF8UOXqXSFir/giphy.gif';
    return avatar.startsWith('http') ? avatar : 'https://media4.giphy.com/media/' + avatar;
}

function focusMapOnLocation(loc, zoomLevel = 18) {
    if (map && loc) {
        document.getElementById('map').style.display = 'block';
        document.getElementById('streetview').style.display = 'none';
        document.getElementById('btn-voltar-mapa').style.display = 'none';
        map.setCenter({ lat: Number(loc.lat), lng: Number(loc.lng) });
        map.setZoom(zoomLevel);
    }
}

function handleStreetViewError(loc) {
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
            document.getElementById('btn-voltar-mapa').style.display = 'block';

            panorama = new google.maps.StreetViewPanorama(document.getElementById('streetview'), {
                pano: data.location.pano,
                pov: { heading: 0, pitch: 0 },
                zoom: 1,
                disableDefaultUI: true,
                showRoadLabels: false,
                motionTracking: false
            });

            const avatar = new google.maps.Marker({
                position: pos,
                map: panorama,
                icon: {
                    url: getAvatarUrl(loc.avatar),
                    scaledSize: new google.maps.Size(60, 80),
                    anchor: new google.maps.Point(30, 80)
                },
                title: loc.name || 'Local'
            });
            avatar.addListener('click', () => window.MapChat && window.MapChat.showPostModal(loc));

            lastStreetViewLoc = loc;
            document.getElementById('btn-voltar-streetview').style.display = 'none';
        } else {
            handleStreetViewError(loc);
        }
    });
}

// Sistema de filtros combinados
async function applyFilter() {
    showLoading(true);
    updateStatus('Carregando...');

    try {
        const activeFilters = Array.from(document.querySelectorAll('.filter-btn.active'))
                                   .map(btn => btn.getAttribute('data-filter'));

        let filteredPosts = [...currentPosts];

        if (activeFilters.length === 0) {
            // Nenhum filtro → todos no mapa, nenhum na lista
            updateSidebar([]);
            updateMapMarkers(currentPosts);
            updateStatus(`${currentPosts.length} posts no mapa`);
            return;
        }

        if (activeFilters.includes('proximity')) {
            filteredPosts = await getPostsByProximity();
        }
        if (activeFilters.includes('recent')) {
            const recent = getPostsByRecent();
            filteredPosts = filteredPosts.filter(p => 
                recent.find(r => r.lat == p.lat && r.lng == p.lng)
            );
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

// Filtros auxiliares
async function getPostsByProximity() {
    if (!userPosition) return getPostsByRecent();

    const postsWithDistance = currentPosts.map(post => {
        const distance = calculateDistance(userPosition.lat, userPosition.lng, post.lat, post.lng);
        return { ...post, distance };
    });

    return postsWithDistance.sort((a, b) => a.distance - b.distance).slice(0, 20);
}

function getPostsByRecent() {
    return [...currentPosts].sort((a, b) => new Date(b.created_at || 0) - new Date(a.created_at || 0)).slice(0, 20);
}

// Utilitários
function calculateDistance(lat1, lng1, lat2, lng2) {
    const R = 6371;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLng = (lng2 - lng1) * Math.PI / 180;
    const a = Math.sin(dLat/2)**2 +
              Math.cos(lat1 * Math.PI/180) * Math.cos(lat2 * Math.PI/180) *
              Math.sin(dLng/2)**2;
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
}

function updateSidebar(posts) {
    const container = document.getElementById('avatars-container');
    container.innerHTML = '';

    posts.forEach(post => {
        const item = document.createElement('div');
        item.className = 'carousel-item';
        item.setAttribute('data-lat', post.lat);
        item.setAttribute('data-lng', post.lng);
        item.style.cssText = 'flex: 0 0 auto; text-align: center; cursor: pointer; min-width: 80px; max-width: 100px;';
        
        const distance = post.distance ? ` (${post.distance.toFixed(1)}km)` : '';
        
        item.innerHTML = `
            <img src="${getAvatarUrl(post.avatar)}" 
                 alt="Avatar" 
                 style="width: 56px; height: 56px; border-radius: 50%; border: 2px solid #198754; margin-bottom: 4px; object-fit: cover;"
                 onerror="this.src='https://media1.giphy.com/media/v1.Y2lkPTc5MGI3NjExeTRweGJoMHk1eG5nb2tyOHMyMHp1ZGlpYTFoZDZ6Ym9zZ3ZkYXB2MSZlcD12MV9pbnRlcm5hbF9naWZfYnlfaWQmY3Q9cw/bvQHYGOF8UOXqXSFir/giphy.gif'">
            <div style="font-size: 0.98em; font-weight: 600; color: #198754; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 90px;">
                ${post.username}${distance}
            </div>
        `;
        item.addEventListener('click', () => {
            map.setCenter({ lat: post.lat, lng: post.lng });
            map.setZoom(15);
        });
        container.appendChild(item);
    });
}

// Funções auxiliares de filtro
function getPostsByRecent() {
    const sorted = [...currentPosts].sort((a, b) => new Date(b.date) - new Date(a.date));
    return sorted.slice(0, 10);
}

        async function getPostsByProximity() {
            if (!userLocation) return [];
            return currentPosts
                .map(post => ({
                    ...post,
                    distance: google.maps.geometry.spherical.computeDistanceBetween(
                        new google.maps.LatLng(userLocation.lat, userLocation.lng),
                        new google.maps.LatLng(post.lat, post.lng)
                    )
                }))
                .sort((a, b) => a.distance - b.distance)
                .slice(0, 10);
        }

        // ================================
        // ✅ Minimizar/Maximizar Sidebar
        // ================================
        document.getElementById('btn-minimizar').addEventListener('click', function() {
            document.getElementById('chat-carousel').style.display = 'none';
            document.getElementById('btn-maximizar').style.display = 'flex';
        });

        document.getElementById('btn-maximizar').addEventListener('click', function() {
            document.getElementById('chat-carousel').style.display = 'flex';
            this.style.display = 'none';
        });

        // ================================
        // Inicialização
        // ================================
        // Inicialização do Google Maps
        window.initMap = function() {
            map = new google.maps.Map(document.getElementById('map'), {
                center: { lat: -14.2350, lng: -51.9253 }, // Centro do Brasil
                zoom: 4,
                streetViewControl: false,
                mapTypeControl: false,
                fullscreenControl: false
            });
            // Aqui você pode adicionar marcadores iniciais, se quiser
        };

        window.onload = async () => {
            if (typeof window.initMap === 'function') {
                window.initMap();
            }
            // await fetchPosts(); // Se necessário, descomente e implemente fetchPosts
            applyFilter();
        };
    </script>
</body>
</html>
