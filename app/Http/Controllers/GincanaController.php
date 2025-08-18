<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gincana;
use App\Models\Mapchat;
use App\Models\Participacao; // Adicionado para clareza
use Illuminate\Support\Facades\Auth;

class GincanaController extends Controller
{
    // Legacy controller no longer used; kept for reference during transition.
    /**
     * Exibe a página inicial com as gincanas públicas.
     * Corresponde à lógica anterior da função global getGameLocations().
     *
     * @return \Illuminate\View\View
     */
    public function welcome()
    {
        $locations = [];
        
        // Buscar apenas os locais principais das gincanas públicas
    $gincanas = Mapchat::where('privacidade', 'publica')->get();
    foreach ($gincanas as $gincana) {
            $locations[] = [
                'lat' => (float) $gincana->latitude,
                'lng' => (float) $gincana->longitude,
                'name' => $gincana->nome,
        'mapchat_id' => $gincana->id,
                'contexto' => $gincana->contexto
            ];
        }
        
        // Se não houver gincanas, retornar um marcador especial para exibir alerta no front-end
        if (empty($locations)) {
            $locations[] = [
                'no_gincana' => true
            ];
        }
        
        return view('welcome', compact('locations'));
    }

    // Exibe o formulário de criação
    public function create()
    {
        return view('gincana.criar');
    }

    // Salva uma nova gincana
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'duracao' => 'required|integer',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'contexto' => 'required|string|max:255',
            'privacidade' => 'required|in:publica,privada',
        ]);

        $validated['user_id'] = Auth::id();
        $gincana = Gincana::create($validated);

        return redirect()->route('gincana.index')->with('success', 'Gincana criada com sucesso!');
    }

    // Exibe formulário de edição
    public function edit(Gincana $gincana)
    {
        return view('gincana.edit', compact('gincana'));
    }

    // Atualiza gincana
    public function update(Request $request, Gincana $gincana)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'duracao' => 'required|integer',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'contexto' => 'required|string|max:255',
            'privacidade' => 'required|in:publica,privada',
        ]);
        
        $gincana->update($validated);
        return redirect()->route('gincana.index')->with('success', 'Gincana atualizada com sucesso!');
    }

    // Exclui gincana
    public function destroy(Gincana $gincana)
    {
        $gincana->delete();
        return redirect()->route('gincana.index')->with('success', 'Gincana excluída com sucesso!');
    }

    // Lista gincanas criadas pelo usuário
    public function index()
    {
        $gincanas = Gincana::where('user_id', Auth::id())->get();
        return view('gincana.index', compact('gincanas'));
    }

    // Exibe detalhes de uma gincana
    public function show(Gincana $gincana)
    {
        $user = auth()->user();
        if ($user) {
            // Ao entrar na página da gincana, zera contador agregado dessa gincana
            \App\Models\GincanaCommentNotification::where('user_id', $user->id)
                ->where('gincana_id', $gincana->id)
                ->update(['unread_count' => 0]);
        }
        // Exibe sempre a view padrão
        return view('gincana.show', compact('gincana'));
    }

    // Jogadas removido

    // Lista gincanas disponíveis para jogar
    public function disponiveis()
    {
        $user = auth()->user();
        $jogadasIds = $user->participacoes()->pluck('gincana_id')->toArray();
        
        $gincanasDisponiveis = Gincana::where('privacidade', 'publica')
            ->whereNotIn('id', $jogadasIds)
            ->with('user')
            ->get();
            
        return view('gincana.disponiveis', compact('gincanasDisponiveis'));
    }

    // Jogar uma gincana específica
    public function jogar(Gincana $gincana)
    {
        $user = auth()->user();
        if ($user) {
            // Ao entrar em jogar, também zera contador
            \App\Models\GincanaCommentNotification::where('user_id', $user->id)
                ->where('gincana_id', $gincana->id)
                ->update(['unread_count' => 0]);
        }

        // Criar array de locais da gincana
        $locations = [];
        // Adicionar local principal da gincana
        $locations[] = [
            'lat' => (float) $gincana->latitude,
            'lng' => (float) $gincana->longitude,
            'name' => $gincana->nome,
            'gincana_id' => $gincana->id,
            'contexto' => $gincana->contexto
        ];
        return view('gincana.play', compact('gincana', 'locations'));
    }
}
