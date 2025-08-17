<footer class="bg-gray-100 text-black py-4 mt-auto relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <div class="text-sm text-gray-500">
                © {{ date('Y') }} MapChat. Todos os direitos reservados.
            </div>
            <div class="flex items-center space-x-4 mt-2 md:mt-0">
                <span class="text-sm text-gray-500">
                    Feito com ❤️ para amantes de mapas e conversas
                </span>
            </div>
        </div>
    </div>

    <!-- Botão flutuante do globo -->
    <button id="btn-globe-map" title="Ver chats ativos" class="fixed bottom-20 right-4 md:right-6 z-40 bg-blue-600 hover:bg-blue-700 text-white rounded-full shadow-lg w-12 h-12 flex items-center justify-center">
        <span class="text-xl">🌍</span>
    </button>

    <!-- Sidebar do mapa (abre da direita para a esquerda) -->
    <div id="active-chats-drawer" class="fixed top-0 right-0 h-full w-full sm:w-[420px] bg-white shadow-2xl transform translate-x-full transition-transform duration-300 z-50 flex flex-col">
        <div class="p-4 border-b flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span>🌍</span>
                <h3 class="font-semibold">Chats ativos no mapa</h3>
            </div>
            <button id="close-chats-drawer" class="text-gray-600 hover:text-gray-900" aria-label="Fechar">✕</button>
        </div>
        <div class="text-sm text-gray-600 px-4 py-2 border-b">Toque em um ponto para ver detalhes</div>
        <div id="active-chats-map" class="flex-1"></div>
    </div>
</footer>
