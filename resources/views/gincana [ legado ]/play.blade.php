@extends('layouts.app')

@section('title')
    @auth
        MapChat - Conversas no mapa
    @else
        MapChat - Geochat
    @endauth
@endsection

@section('scripts')
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBzEzusC_k3oEoPnqynq2N4a0aA3arzH-c&libraries=geometry"></script>
@endsection

@section('content')
<div class="game-container">
    <!-- Informações do jogo -->
    <div class="game-info">
       @auth
            <div><strong>Pontuação:</strong> <span id="score">1000</span></div>
            <div><strong>Tentativas:</strong> <span id="attempts">5</span></div>
            <div><strong>Rodada:</strong> <span id="round">1</span></div> 
            <div><strong>Jogador:</strong> {{ auth()->user()->name }}</div>
        @else
            <div><strong>Tentativas:</strong> <span id="attempts">5</span></div>
            <div><strong>Modo:</strong> Visitante</div>
        @endauth
    </div>

    <!-- Container do Street View -->
    <div id="streetview" class="street-view-container"></div>

    <!-- Controles do jogo -->
    <div class="game-controls">
        <div style="display: flex; justify-content: center; align-items: center; width: 100%; padding: 10px 0;">
            <button id="showMapBtn" class="btn" style="padding: 0; border: none; background: none; width: 100%; max-width: 220px;">
            <img src="https://media0.giphy.com/media/v1.Y2lkPTc5MGI3NjExbjFnOGtlcnl5dmpveGJydTNxb2twNGxudXB3Nm8wMjNlMnI2bDBrZyZlcD12MV9pbnRlcm5hbF9naWZfYnlfaWQmY3Q9cw/PrfN2Hqu24ln2eAaOS/giphy.gif" alt="JOGAR" style="width: 100%; height: auto; max-width: 180px; max-height: 120px; display: block; margin: 0 auto;">
            </button>
        </div>
        <style>
            @media (max-width: 600px) {
            #showMapBtn img {
                max-width: 100%;
                max-height: 80px;
            }
            .game-controls > div {
                padding: 0 5px;
            }
            }
        </style>
    </div>
    <!-- Popup de feedback -->
    <div id="overlay" class="overlay"></div>
    <div id="popup" class="popup">
        <h3 id="popupTitle">Resultado</h3>
        <p id="popupMessage"></p>
        <button id="continueBtn" class="btn">Continuar</button>
    </div>
</div>

<script>
    // Dados do chat e autenticação, sem dependência do game.js
    window.isAuthenticated = @json(auth()->check());
    // Se necessário, injetar dados de locais diretamente na página específica do chat.
    window.mapchatLocations = @json($locations ?? []);
</script>
@endsection
