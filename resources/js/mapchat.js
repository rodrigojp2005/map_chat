// Lightweight MapChat utilities (no game/score)
// - Shows a post/comments modal when a marker is clicked
// - Exposes addComment and loadComments if needed in inline views

window.MapChat = (function () {
  function showPostModal(location, opts = {}) {
    const isAuthenticated = window.isAuthenticated || false;
    const modo = opts.modo || 'streetview';
    let html = '';
    html += `<div class="post-content" style="text-align: left;">
      <div class="post-inicial" style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <h4 style="margin: 0 0 10px 0; color: #495057;">O que está acontecendo aqui?</h4>
        <p style="margin: 0; color: #6c757d; font-style: italic;">"${location.contexto || 'Descubra onde estou!'}"</p>
      </div>`;
    if (modo === 'mapa') {
      // Mostra apenas o link para Street View
      html += `<div style="text-align:center; margin: 30px 0 10px 0;">
        <button id="ver-streetview-btn" style="background: #2563eb; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-size: 1.1em; cursor: pointer;">
          👁️ Veja local da conversa
        </button>
      </div>`;
    } else {
      // Mostra comentários normalmente
      html += `<div class="comments-section">
        <h4 style="margin: 0 0 15px 0; color: #495057;">💬 Comentários</h4>
        <div id="comments-list" style="max-height: 300px; overflow-y: auto; margin-bottom: 15px;">
          <div style="text-align: center; color: #6c757d;">
            <i class="fas fa-spinner fa-spin"></i> Carregando comentários...
          </div>
        </div>
        ${isAuthenticated ? `
          <div class="add-comment" style="border-top: 1px solid #dee2e6; padding-top: 15px;">
            <textarea id="new-comment" placeholder="Compartilhe sua experiência..." style="width: 100%; height: 80px; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; resize: vertical; font-family: inherit;"></textarea>
            <button id="comment-btn" style="margin-top: 10px; background: #007bff; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;">💬 Comentar</button>
          </div>
        ` : `
          <div style="text-align: center; padding: 15px; background: #e9ecef; border-radius: 4px;">
            <p style="margin: 0; color: #6c757d;">🔐 Faça login para comentar</p>
          </div>
        `}
      </div>`;
    }
    html += '</div>';
    Swal.fire({
      title: location.name || 'Local',
      html,
      width: 600,
      showCloseButton: true,
      showConfirmButton: false,
      didOpen: () => {
        if (modo === 'mapa') {
          const btn = document.getElementById('ver-streetview-btn');
          if (btn) btn.addEventListener('click', () => {
            if (window.showStreetView) window.showStreetView(location);
            Swal.close();
          });
        } else if (window.isAuthenticated) {
          loadComments(location.mapchat_id || location.id);
          const btn = document.getElementById('comment-btn');
          if (btn) btn.addEventListener('click', () => addComment(location.mapchat_id || location.id));
        }
      }
    });
  }

  async function loadComments(mapchatId) {
    try {
      const res = await fetch(`/comentarios/${mapchatId}`);
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const text = await res.text();
      const comentarios = JSON.parse(text);
      const list = document.getElementById('comments-list');
      if (!list) return;
      if (!Array.isArray(comentarios) || comentarios.length === 0) {
        list.innerHTML = `<div style="text-align: center; color: #6c757d; padding: 20px;">🤔 Seja o primeiro a comentar!</div>`;
        return;
      }
      list.innerHTML = comentarios.map(c => `
        <div class="comment" style="border-bottom: 1px solid #eee; padding: 12px 0;">
          <div style="display: flex; align-items: center; margin-bottom: 8px;">
            <strong style="color: #495057; font-size: 14px;">${c.user.name}</strong>
            <small style="color: #6c757d; margin-left: 10px;">${formatDate(c.created_at)}</small>
          </div>
          <p style="margin: 0; color: #495057; line-height: 1.4; font-size: 14px;">${c.conteudo}</p>
        </div>
      `).join('');
    } catch (e) {
      const list = document.getElementById('comments-list');
      if (list) list.innerHTML = `<div style="text-align: center; color: #dc3545;">❌ Erro ao carregar comentários: ${e.message}</div>`;
    }
  }

  async function addComment(mapchatId) {
    const textarea = document.getElementById('new-comment');
    const conteudo = (textarea?.value || '').trim();
    if (!conteudo) {
      Swal.fire({ icon: 'warning', title: 'Atenção', text: 'Digite seu comentário primeiro!', confirmButtonColor: '#007bff' });
      return;
    }
    try {
      const csrf = document.querySelector('meta[name="csrf-token"]');
      const res = await fetch('/comentarios', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf?.getAttribute('content') || '', 'Accept': 'application/json' },
  body: JSON.stringify({ mapchat_id: mapchatId, conteudo })
      });
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const text = await res.text();
      const data = JSON.parse(text);
      if (!data.success) throw new Error(data.message || 'Erro ao comentar');
      if (textarea) textarea.value = '';
  await loadComments(mapchatId);
      Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Comentário adicionado!', showConfirmButton: false, timer: 2000 });
    } catch (e) {
      Swal.fire({ icon: 'error', title: 'Erro', text: `Não foi possível adicionar seu comentário: ${e.message}`, confirmButtonColor: '#dc3545' });
    }
  }

  function formatDate(s) {
    const d = new Date(s), now = new Date();
    const diffH = (now - d) / 36e5;
    if (diffH < 1) return 'Agora mesmo';
    if (diffH < 24) return `${Math.floor(diffH)}h atrás`;
    return d.toLocaleDateString('pt-BR');
  }

  return { showPostModal, loadComments, addComment };
})();
