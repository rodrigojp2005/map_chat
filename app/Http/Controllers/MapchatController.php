<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mapchat; // alias of Gincana
use Illuminate\Support\Facades\Auth;

class MapchatController extends Controller
{
    // Landing page showing public mapchats (was GincanaController@welcome)
    public function welcome()
    {
        $locations = [];
        $gincanas = Mapchat::where('privacidade', 'publica')->get();
        foreach ($gincanas as $gincana) {
            $locations[] = [
                'lat' => (float) $gincana->latitude,
                'lng' => (float) $gincana->longitude,
                'name' => $gincana->nome,
                'mapchat_id' => $gincana->id,
                'contexto' => $gincana->contexto,
                'avatar' => $gincana->avatar,
            ];
        }
        if (empty($locations)) {
            $locations[] = ['no_gincana' => true];
        }
        return view('welcome', compact('locations'));
    }
    public function index()
    {
        $gincanas = Mapchat::where('user_id', Auth::id())->get();
    return view('mapchat.index', ['gincanas' => $gincanas]);
    }

    public function create()
    {
    return view('mapchat.criar');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'avatar' => 'nullable|string|max:255',
            'duracao' => 'nullable|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'contexto' => 'required|string|max:255',
            'privacidade' => 'required|in:publica,privada,comercial',
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
                ->where('mapchat_id', $mapchat->id)
                ->update(['unread_count' => 0]);
        }
    return view('mapchat.show', ['gincana' => $mapchat]);
    }

    public function edit(Mapchat $mapchat)
    {
    return view('mapchat.edit', ['gincana' => $mapchat]);
    }

    public function update(Request $request, Mapchat $mapchat)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'duracao' => 'required|integer',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'contexto' => 'required|string|max:255',
            'privacidade' => 'required|in:publica,privada,comercial',
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
    $jogadasIds = $user->participacoes()->pluck('mapchat_id')->toArray();
        $gincanasDisponiveis = Mapchat::where('privacidade', 'publica')
            ->whereNotIn('id', $jogadasIds)
            ->with('user')
            ->get();
    return view('mapchat.disponiveis', compact('gincanasDisponiveis'));
    }

    // Jogar uma sala específica (alias)
    public function jogar(Mapchat $mapchat)
    {
        $user = auth()->user();
        if ($user) {
            \App\Models\GincanaCommentNotification::where('user_id', $user->id)
                ->where('mapchat_id', $mapchat->id)
                ->update(['unread_count' => 0]);
        }
        $locations = [[
            'lat' => (float) $mapchat->latitude,
            'lng' => (float) $mapchat->longitude,
            'name' => $mapchat->nome,
            'mapchat_id' => $mapchat->id,
            'contexto' => $mapchat->contexto
        ]];
    return view('mapchat.play', ['gincana' => $mapchat, 'locations' => $locations]);
    }

    /**
     * Lista de MapChats ativos (públicos) em JSON para o mapa lateral.
     */
    public function ativosJson()
    {
        $items = Mapchat::with('user')
            ->where('privacidade', 'publica')
            ->get()
            ->map(function ($m) {
                return [
                    'id' => $m->id,
                    'nome' => $m->nome,
                    'lat' => (float) $m->latitude,
                    'lng' => (float) $m->longitude,
                    'contexto' => $m->contexto,
                    'duracao' => (int) $m->duracao,
                    'criador' => $m->user ? $m->user->name : null,
                    'created_at' => $m->created_at ? $m->created_at->toIso8601String() : null,
                ];
            });

        return response()->json(['success' => true, 'data' => $items]);
    }
}
