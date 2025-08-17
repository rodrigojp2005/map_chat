// Sidebar map for active MapChats using Google Maps (loaded on demand)

let drawerEl, mapEl, gmap, markers = [];
let lastBounds = null;
let gmapsLoading = false;
let clusterer = null;

function ensureGoogleMaps() {
  return new Promise((resolve, reject) => {
    if (window.google && window.google.maps) return resolve(window.google.maps);
    if (gmapsLoading) {
      const check = () => (window.google && window.google.maps) ? resolve(window.google.maps) : setTimeout(check, 50);
      return check();
    }
    gmapsLoading = true;
    const key = window.GMAPS_API_KEY || '';
    const script = document.createElement('script');
    script.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(key)}`;
    script.async = true;
    script.defer = true;
    script.onerror = () => reject(new Error('Falha ao carregar Google Maps'));
    script.onload = () => resolve(window.google.maps);
    document.body.appendChild(script);
  });
}

function ensureMarkerClusterer() {
  return new Promise((resolve, reject) => {
    if (window.markerClusterer && window.markerClusterer.MarkerClusterer) {
      return resolve(window.markerClusterer);
    }
    const script = document.createElement('script');
    script.src = 'https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js';
    script.async = true;
    script.defer = true;
    script.onload = () => resolve(window.markerClusterer);
    script.onerror = () => reject(new Error('Falha ao carregar MarkerClusterer'));
    document.body.appendChild(script);
  });
}

async function openDrawerWithMap() {
  drawerEl = drawerEl || document.getElementById('active-chats-drawer');
  mapEl = mapEl || document.getElementById('active-chats-map');
  if (!drawerEl || !mapEl) return;

  drawerEl.classList.remove('translate-x-full');
  try {
    const GM = await ensureGoogleMaps();
  await ensureMarkerClusterer();
    if (!gmap) {
      // Center roughly in Brazil
      gmap = new GM.Map(mapEl, {
        center: { lat: -14.235, lng: -51.9253 },
        zoom: 4,
        mapTypeControl: false,
        fullscreenControl: false,
        streetViewControl: false
      });
    }
    // Give the drawer time to finish transition, then trigger resize
    setTimeout(() => {
      try { GM.event.trigger(gmap, 'resize'); } catch {}
      if (lastBounds && !lastBounds.isEmpty()) {
        try { gmap.fitBounds(lastBounds, { top: 20, bottom: 20, left: 20, right: 20 }); } catch {}
      }
    }, 310);
    await loadAndRenderMarkers();
  } catch (e) {
    console.error(e);
    // Mantém o container do mapa; apenas registra no console.
  }
}

function closeDrawer() {
  const el = document.getElementById('active-chats-drawer');
  if (el) el.classList.add('translate-x-full');
}

function formatWhen(s) {
  try {
    if (!s) return '';
    const d = new Date(s);
    return d.toLocaleString('pt-BR');
  } catch { return ''; }
}

async function loadAndRenderMarkers() {
  if (!gmap) return;
  // Clear previous markers
  if (clusterer) {
    try { clusterer.clearMarkers(); } catch {}
  }
  markers = [];
  const res = await fetch('/mapchat-ativos.json');
  if (!res.ok) throw new Error('HTTP ' + res.status);
  const { success, data } = await res.json();
  if (!success) throw new Error('Resposta inválida');

  // Mesmo sem dados, mantemos o mapa visível centralizado no Brasil
  const GM = window.google.maps;
  const bounds = new GM.LatLngBounds();
  const info = new GM.InfoWindow();
  data.forEach(item => {
    if (typeof item.lat !== 'number' || typeof item.lng !== 'number') return;
    const marker = new GM.Marker({ position: { lat: item.lat, lng: item.lng }, title: item.nome || 'Chat' });
    markers.push(marker);
    bounds.extend(marker.getPosition());
    const content = `
      <div style="min-width: 200px;">
        <div style="font-weight:600; color:#111;">${item.nome || 'Chat'}</div>
        <div style="font-size: 12px; color:#555;">Criado por: ${item.criador || 'Desconhecido'}</div>
        <div style="font-size: 12px; color:#555;">Duração: ${item.duracao || '-'} min</div>
        <div style="font-size: 12px; color:#777;">${item.contexto ? '“' + item.contexto + '”' : ''}</div>
        <div style="margin-top:8px; display:flex; gap:6px;">
          <a href="/mapchat/${item.id}" class="text-blue-600 hover:underline text-sm">Abrir</a>
          <button data-open-post="${item.id}" class="text-sm text-gray-700 hover:underline">Ver comentários</button>
        </div>
      </div>`;
    marker.addListener('click', () => {
      info.setContent(content);
      info.open({ anchor: marker, map: gmap, shouldFocus: false });
      setTimeout(() => {
        const btn = document.querySelector(`button[data-open-post="${item.id}"]`);
        if (btn) btn.onclick = () => {
          if (window.MapChat && typeof window.MapChat.showPostModal === 'function') {
            window.MapChat.showPostModal({ id: item.id, mapchat_id: item.id, name: item.nome, contexto: item.contexto });
          }
        };
      }, 0);
    });
  });

  // Apply clustering (adds markers to the map via clusterer)
  if (window.markerClusterer && window.markerClusterer.MarkerClusterer) {
    if (!clusterer) {
      clusterer = new window.markerClusterer.MarkerClusterer({ map: gmap, markers });
    } else {
      try { clusterer.addMarkers(markers); } catch {}
    }
  } else {
    // Fallback: add markers directly if clusterer failed to load
    markers.forEach(m => m.setMap(gmap));
  }
  if (!bounds.isEmpty()) {
    gmap.fitBounds(bounds, { top: 20, bottom: 20, left: 20, right: 20 });
    lastBounds = bounds;
  } else {
    // Sem pontos, centraliza no Brasil
    try { gmap.setCenter({ lat: -14.235, lng: -51.9253 }); gmap.setZoom(4); } catch {}
    lastBounds = null;
  }
}

function setupTriggers() {
  const openBtn = document.getElementById('btn-globe-map');
  const closeBtn = document.getElementById('close-chats-drawer');
  openBtn && openBtn.addEventListener('click', openDrawerWithMap);
  closeBtn && closeBtn.addEventListener('click', closeDrawer);
}

// Auto attach on load
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', setupTriggers);
} else {
  setupTriggers();
}

// Expose for debug
window.ActiveChatsMap = { open: openDrawerWithMap, close: closeDrawer };
