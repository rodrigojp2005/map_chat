window.MapChat=function(){function c(i,e={}){const r=window.isAuthenticated||!1,t=e.modo||"streetview";let o="";o+=`<div class="post-content" style="text-align: left;">
      <div class="post-inicial" style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <h4 style="margin: 0 0 10px 0; color: #495057;">O que está acontecendo aqui?</h4>
        <p style="margin: 0; color: #6c757d; font-style: italic;">"${i.contexto||"Descubra onde estou!"}"</p>
      </div>`,t==="mapa"?o+=`<div style="text-align:center; margin: 30px 0 10px 0;">
        <button id="ver-streetview-btn" style="background: #2563eb; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-size: 1.1em; cursor: pointer;">
          👁️ Veja local da conversa
        </button>
      </div>`:o+=`<div class="comments-section">
        <h4 style="margin: 0 0 15px 0; color: #495057;">💬 Comentários</h4>
        <div id="comments-list" style="max-height: 300px; overflow-y: auto; margin-bottom: 15px;">
          <div style="text-align: center; color: #6c757d;">
            <i class="fas fa-spinner fa-spin"></i> Carregando comentários
          </div>
        </div>
        ${r?`
          <div class="add-comment" style="border-top: 1px solid #dee2e6; padding-top: 15px;">
            <textarea id="new-comment" placeholder="Compartilhe sua experiência..." style="width: 100%; height: 80px; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; resize: vertical; font-family: inherit;"></textarea>
            <button id="comment-btn" style="margin-top: 10px; background: #007bff; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;">💬 Comentar</button>
          </div>
        `:`
          <div class="login-prompt" style="border-top: 1px solid #dee2e6; padding-top: 15px; text-align: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; padding: 20px; margin-top: 10px;">
            <p style="margin: 0 0 15px 0; color: white; font-weight: 600;">💬 Gostou do que viu?</p>
            <p style="margin: 0 0 20px 0; color: rgba(255,255,255,0.9); font-size: 14px;">Faça login para compartilhar sua experiência e conversar com outros exploradores!</p>
            <div style="display: flex; gap: 10px; justify-content: center;">
              <a href="/login" style="background: rgba(255,255,255,0.2); color: white; text-decoration: none; padding: 10px 20px; border-radius: 25px; font-weight: 600; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.3); transition: all 0.3s ease;">🔑 Fazer Login</a>
              <a href="/register" style="background: rgba(255,255,255,0.9); color: #667eea; text-decoration: none; padding: 10px 20px; border-radius: 25px; font-weight: 600; transition: all 0.3s ease;">✨ Criar Conta</a>
            </div>
          </div>
        `}
      </div>`,o+="</div>",Swal.fire({title:i.name||"Local",html:o,width:600,showCloseButton:!0,showConfirmButton:!1,didOpen:()=>{if(t==="mapa"){const n=document.getElementById("ver-streetview-btn");n&&n.addEventListener("click",()=>{window.showStreetView&&window.showStreetView(i),Swal.close()})}else if(s(i.mapchat_id||i.id),window.isAuthenticated){const n=document.getElementById("comment-btn");n&&n.addEventListener("click",()=>d(i.mapchat_id||i.id))}}})}async function s(i){try{const e=await fetch(`/comentarios/${i}`);if(!e.ok)throw new Error(`Erro HTTP ${e.status}: ${e.statusText}`);const r=e.headers.get("content-type");if(!r||!r.includes("application/json")){const n=await e.text();throw console.error("Resposta não é JSON:",n),new Error("Servidor retornou resposta inválida")}const t=await e.json(),o=document.getElementById("comments-list");if(!o)return;if(!Array.isArray(t)||t.length===0){window.isAuthenticated||!1?o.innerHTML='<div style="text-align: center; color: #6c757d; padding: 20px;">🤔 Seja o primeiro a comentar!</div>':o.innerHTML=`<div style="text-align: center; color: #6c757d; padding: 20px;">
            <div style="margin-bottom: 10px;">💬 Ainda não há comentários neste local</div>
            <div style="font-size: 14px; color: #aaa;">Seja o primeiro a compartilhar sua experiência fazendo login!</div>
          </div>`;return}o.innerHTML=t.map(n=>{var a;return`
        <div class="comment" style="border-bottom: 1px solid #eee; padding: 12px 0;">
          <div style="display: flex; align-items: center; margin-bottom: 8px;">
            <strong style="color: #495057; font-size: 14px;">${((a=n.user)==null?void 0:a.name)||"Usuário"}</strong>
            <small style="color: #6c757d; margin-left: 10px;">${l(n.created_at)}</small>
          </div>
          <p style="margin: 0; color: #495057; line-height: 1.4; font-size: 14px;">${n.conteudo}</p>
        </div>
      `}).join("")}catch(e){console.error("Erro ao carregar comentários:",e);const r=document.getElementById("comments-list");r&&(r.innerHTML=`<div style="text-align: center; color: #dc3545; padding: 20px;">
        <div>❌ Erro ao carregar comentários</div>
        <div style="font-size: 12px; margin-top: 5px; color: #999;">${e.message}</div>
      </div>`)}}async function d(i){const e=document.getElementById("new-comment"),r=((e==null?void 0:e.value)||"").trim();if(!r){Swal.fire({icon:"warning",title:"Atenção",text:"Digite seu comentário primeiro!",confirmButtonColor:"#007bff"});return}try{const t=document.querySelector('meta[name="csrf-token"]'),o=await fetch("/comentarios",{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":(t==null?void 0:t.getAttribute("content"))||"",Accept:"application/json"},body:JSON.stringify({mapchat_id:i,conteudo:r})});if(o.status===401){const p=await o.json();Swal.fire({icon:"info",title:"🔑 Login Necessário",text:p.message||"Você precisa fazer login para comentar.",confirmButtonText:"🔑 Fazer Login",showCancelButton:!0,cancelButtonText:"Cancelar",confirmButtonColor:"#007bff"}).then(m=>{m.isConfirmed&&(window.location.href="/login")});return}if(!o.ok)throw new Error(`HTTP ${o.status}`);const n=await o.text(),a=JSON.parse(n);if(!a.success)throw new Error(a.message||"Erro ao comentar");e&&(e.value=""),await s(i),Swal.fire({toast:!0,position:"top-end",icon:"success",title:"Comentário adicionado!",showConfirmButton:!1,timer:2e3})}catch(t){Swal.fire({icon:"error",title:"Erro",text:`Não foi possível adicionar seu comentário: ${t.message}`,confirmButtonColor:"#dc3545"})}}function l(i){const e=new Date(i),t=(new Date-e)/36e5;return t<1?"Agora mesmo":t<24?`${Math.floor(t)}h atrás`:e.toLocaleDateString("pt-BR")}return{showPostModal:c,loadComments:s,addComment:d}}();
