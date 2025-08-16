<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mapchat; // alias of Gincana
use Illuminate\Support\Facades\Auth;

class MapchatController extends Controller
{
    public function index()
    {
        $gincanas = Mapchat::where('user_id', Auth::id())->get();
        return view('gincana.index', ['gincanas' => $gincanas]);
    }

    public function create()
    {
        return view('gincana.criar');
    }

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
        Mapchat::create($validated);
        return redirect()->route('mapchat.index')->with('success', 'Sala criada com sucesso!');
    }

    public function show(Mapchat $mapchat)
    {
        $user = auth()->user();
        if ($user) {
            \App\Models\GincanaCommentNotification::where('user_id', $user->id)
                ->where('gincana_id', $mapchat->id)
                ->update(['unread_count' => 0]);
        }
        return view('gincana.show', ['gincana' => $mapchat]);
    }

    public function edit(Mapchat $mapchat)
    {
        return view('gincana.edit', ['gincana' => $mapchat]);
    }

    public function update(Request $request, Mapchat $mapchat)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'duracao' => 'required|integer',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'contexto' => 'required|string|max:255',
            'privacidade' => 'required|in:publica,privada',
        ]);
        $mapchat->update($validated);
        return redirect()->route('mapchat.index')->with('success', 'Sala atualizada com sucesso!');
    }

    public function destroy(Mapchat $mapchat)
    {
        $mapchat->delete();
        return redirect()->route('mapchat.index')->with('success', 'Sala excluída com sucesso!');
    }

    public function disponiveis()
    {
        $user = auth()->user();
        $jogadasIds = $user->participacoes()->pluck('gincana_id')->toArray();
        $gincanasDisponiveis = Mapchat::where('privacidade', 'publica')
            ->whereNotIn('id', $jogadasIds)
            ->with('user')
            ->get();
        return view('gincana.disponiveis', compact('gincanasDisponiveis'));
    }

    // Jogar uma sala específica (alias)
    public function jogar(Mapchat $mapchat)
    {
        $user = auth()->user();
        if ($user) {
            \App\Models\GincanaCommentNotification::where('user_id', $user->id)
                ->where('gincana_id', $mapchat->id)
                ->update(['unread_count' => 0]);
        }
        $locations = [[
            'lat' => (float) $mapchat->latitude,
            'lng' => (float) $mapchat->longitude,
            'name' => $mapchat->nome,
            'gincana_id' => $mapchat->id,
            'contexto' => $mapchat->contexto
        ]];
        return view('gincana.play', ['gincana' => $mapchat, 'locations' => $locations]);
    }
}
