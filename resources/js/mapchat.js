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
            <i class="fas fa-spinner fa-spin"></i> Carregando comentários
          </div>
        </div>
        ${isAuthenticated ? `
          <div class="add-comment" style="border-top: 1px solid #dee2e6; padding-top: 15px;">
            <textarea id="new-comment" placeholder="Compartilhe sua experiência..." style="width: 100%; height: 80px; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; resize: vertical; font-family: inherit;"></textarea>
            <button id="comment-btn" style="margin-top: 10px; background: #007bff; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;">💬 Comentar</button>
          </div>
        ` : `
          <div class="login-prompt" style="border-top: 1px solid #dee2e6; padding-top: 15px; text-align: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; padding: 20px; margin-top: 10px;">
            <p style="margin: 0 0 15px 0; color: white; font-weight: 600;">💬 Gostou do que viu?</p>
            <p style="margin: 0 0 20px 0; color: rgba(255,255,255,0.9); font-size: 14px;">Faça login para compartilhar sua experiência e conversar com outros exploradores!</p>
            <div style="display: flex; gap: 10px; justify-content: center;">
              <a href="/login" style="background: rgba(255,255,255,0.2); color: white; text-decoration: none; padding: 10px 20px; border-radius: 25px; font-weight: 600; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.3); transition: all 0.3s ease;">🔑 Fazer Login</a>
              <a href="/register" style="background: rgba(255,255,255,0.9); color: #667eea; text-decoration: none; padding: 10px 20px; border-radius: 25px; font-weight: 600; transition: all 0.3s ease;">✨ Criar Conta</a>
            </div>
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
        } else {
          // Sempre carregar comentários, independente do login
          loadComments(location.mapchat_id || location.id);
          
          // Só adicionar event listener do botão se estiver logado
          if (window.isAuthenticated) {
            const btn = document.getElementById('comment-btn');
            if (btn) btn.addEventListener('click', () => addComment(location.mapchat_id || location.id));
          }
        }
      }
    });
  }

  async function loadComments(mapchatId) {
    try {
      const res = await fetch(`/comentarios/${mapchatId}`);
      
      if (!res.ok) {
        throw new Error(`Erro HTTP ${res.status}: ${res.statusText}`);
      }
      
      const contentType = res.headers.get('content-type');
      if (!contentType || !contentType.includes('application/json')) {
        const text = await res.text();
        console.error('Resposta não é JSON:', text);
        throw new Error('Servidor retornou resposta inválida');
      }
      
      const comentarios = await res.json();
      const list = document.getElementById('comments-list');
      if (!list) return;
      
      if (!Array.isArray(comentarios) || comentarios.length === 0) {
        const isAuthenticated = window.isAuthenticated || false;
        if (isAuthenticated) {
          list.innerHTML = `<div style="text-align: center; color: #6c757d; padding: 20px;">🤔 Seja o primeiro a comentar!</div>`;
        } else {
          list.innerHTML = `<div style="text-align: center; color: #6c757d; padding: 20px;">
            <div style="margin-bottom: 10px;">💬 Ainda não há comentários neste local</div>
            <div style="font-size: 14px; color: #aaa;">Seja o primeiro a compartilhar sua experiência fazendo login!</div>
          </div>`;
        }
        return;
      }
      
      list.innerHTML = comentarios.map(c => `
        <div class="comment" style="border-bottom: 1px solid #eee; padding: 12px 0;">
          <div style="display: flex; align-items: center; margin-bottom: 8px;">
            <strong style="color: #495057; font-size: 14px;">${c.user?.name || 'Usuário'}</strong>
            <small style="color: #6c757d; margin-left: 10px;">${formatDate(c.created_at)}</small>
          </div>
          <p style="margin: 0; color: #495057; line-height: 1.4; font-size: 14px;">${c.conteudo}</p>
        </div>
      `).join('');
    } catch (e) {
      console.error('Erro ao carregar comentários:', e);
      const list = document.getElementById('comments-list');
      if (list) list.innerHTML = `<div style="text-align: center; color: #dc3545; padding: 20px;">
        <div>❌ Erro ao carregar comentários</div>
        <div style="font-size: 12px; margin-top: 5px; color: #999;">${e.message}</div>
      </div>`;
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
      
      if (res.status === 401) {
        // Usuário não está logado
        const data = await res.json();
        Swal.fire({
          icon: 'info',
          title: '🔑 Login Necessário',
          text: data.message || 'Você precisa fazer login para comentar.',
          confirmButtonText: '🔑 Fazer Login',
          showCancelButton: true,
          cancelButtonText: 'Cancelar',
          confirmButtonColor: '#007bff'
        }).then((result) => {
          if (result.isConfirmed) {
            window.location.href = '/login';
          }
        });
        return;
      }
      
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
