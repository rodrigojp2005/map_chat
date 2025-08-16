@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- Cabeçalho -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">
                        🏆 Atividades por Sala
                    </h1>
                    <p class="text-gray-600 mt-2">
                        Veja as atividades recentes em cada sala
                    </p>
                </div>
                <div class="text-right">
                    <a href="{{ route('ranking.geral') }}" 
                       class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg transition-colors mr-2">
                        Ranking Geral
                    </a>
                    <a href="{{ route('gincana.index') }}" 
                       class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                        Ver Salas
                    </a>
                </div>
            </div>
        </div>

        @if($gincanas->count() > 0)
            <!-- Lista de Gincanas com Rankings -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($gincanas as $gincana)
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                        <!-- Cabeçalho do Card -->
                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-4">
                            <h3 class="text-lg font-bold text-white">
                                {{ $gincana->nome }}
                            </h3>
                            <p class="text-blue-100 text-sm mt-1">
                                {{ Str::limit($gincana->contexto, 60) }}
                            </p>
                        </div>

                        <!-- Conteúdo do Card -->
                        <div class="p-4">
                            <!-- Estatísticas -->
                            <div class="space-y-3 mb-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">👥 Participantes:</span>
                                    <span class="font-semibold text-gray-900">{{ $gincana->participacoes_count }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">⏱️ Duração:</span>
                                    <span class="font-semibold text-gray-900">{{ $gincana->duracao }} min</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">🌍 Privacidade:</span>
                                    <span class="px-2 py-1 text-xs rounded-full 
                                        {{ $gincana->privacidade == 'publica' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ ucfirst($gincana->privacidade) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Preview do Top 3 -->
                            @php
                                $top3 = $gincana->participacoes()
                                    ->where('status', 'concluida')
                                    ->with('user')
                                    ->orderBy('pontuacao', 'desc')
                                    ->orderBy('tempo_total_segundos', 'asc')
                                    ->take(3)
                                    ->get();
                            @endphp

                            @if($top3->count() > 0)
                                <div class="border-t pt-4">
                                    <h4 class="text-sm font-semibold text-gray-700 mb-2">🏅 Top 3:</h4>
                                    <div class="space-y-2">
                                        @foreach($top3 as $index => $participacao)
                                            <div class="flex items-center justify-between text-sm">
                                                <div class="flex items-center">
                                                    @if($index == 0)
                                                        <span class="text-yellow-500">🥇</span>
                                                    @elseif($index == 1)
                                                        <span class="text-gray-400">🥈</span>
                                                    @else
                                                        <span class="text-orange-400">🥉</span>
                                                    @endif
                                                    <span class="ml-2 text-gray-900">
                                                        {{ $participacao->user ? Str::limit($participacao->user->name, 15) : 'Usuário Desconhecido' }}
                                                    </span>
                                                </div>
                                                <span class="font-semibold text-blue-600">
                                                    {{ number_format($participacao->pontuacao) }}pts
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Rodapé do Card -->
                        <div class="bg-gray-50 px-4 py-3">
                            <a href="{{ route('ranking.show', $gincana->id) }}" 
                               class="w-full bg-blue-500 hover:bg-blue-600 text-white text-center py-2 px-4 rounded-lg transition-colors block">
                                Ver Ranking Completo
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Estatísticas Gerais -->
            <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white rounded-lg shadow-md p-6 text-center">
                    <div class="text-3xl mb-2">🏆</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $gincanas->count() }}</div>
                    <div class="text-sm text-gray-600">Salas com Atividades</div>
                </div>
                
                <div class="bg-white rounded-lg shadow-md p-6 text-center">
                    <div class="text-3xl mb-2">👥</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $gincanas->sum('participacoes_count') }}</div>
                    <div class="text-sm text-gray-600">Total de Interações</div>
                </div>
                
                <div class="bg-white rounded-lg shadow-md p-6 text-center">
                    <div class="text-3xl mb-2">⭐</div>
                    <div class="text-2xl font-bold text-gray-900">
                        {{ number_format($gincanas->avg('participacoes_count'), 1) }}
                    </div>
                    <div class="text-sm text-gray-600">Média de Interações</div>
                </div>
                
                <div class="bg-white rounded-lg shadow-md p-6 text-center">
                    <div class="text-3xl mb-2">🎯</div>
                    <div class="text-2xl font-bold text-gray-900">
                        {{ $gincanas->where('privacidade', 'publica')->count() }}
                    </div>
                    <div class="text-sm text-gray-600">Salas Públicas</div>
                </div>
            </div>

        @else
            <!-- Nenhuma gincana com ranking -->
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <div class="text-6xl mb-4">🏆</div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">
                    Nenhuma sala com atividade ainda
                </h3>
                <p class="text-gray-600 mb-6">
                    Interaja nas salas para ver estatísticas aparecerem aqui!
                </p>
                <a href="{{ route('gincana.index') }}" 
                   class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg transition-colors">
                    Ver Salas Disponíveis
                </a>
            </div>
        @endif
    </div>
</div>
@endsection