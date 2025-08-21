<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mapchat; // alias of Gincana
use App\Services\LocationService;
use Illuminate\Support\Facades\Auth;

class MapchatController extends Controller
{
    protected $locationService;
    
    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }
    
    // Landing page showing online users on the map
    public function welcome()
    {
        // Obter usuários online como localizações para o mapa
        $onlineUsers = $this->locationService->getOnlineUsers();
        
        $locations = [];
        foreach ($onlineUsers as $user) {
            $locations[] = [
                'lat' => $user['latitude'],
                'lng' => $user['longitude'],
                'name' => $user['name'],
                'user_id' => $user['id'],
                'avatar' => $this->getAvatarFilename($user['avatar_type']),
                'tipo' => 'usuario_online',
                'last_seen' => $user['last_seen']
            ];
        }
        
        // Se não houver usuários online, adicionar marcador padrão
        if (empty($locations)) {
            $locations[] = [
                'lat' => -14.2350,
                'lng' => -51.9253,
                'name' => 'Aguardando usuários...',
                'avatar' => 'default.gif',
                'tipo' => 'placeholder'
            ];
        }
        
        return view('welcome', compact('locations'));
    }
    
    /**
     * Mapear tipo de avatar para nome do arquivo
     */
    private function getAvatarFilename($avatarType)
    {
        $avatarMap = [
            'default' => 'default.gif',
            'man' => 'mario.gif',
            'woman' => 'girl.gif',
            'pet' => 'pets.gif',
            'geek' => 'geek.gif',
            'sport' => 'sport.gif'
        ];
        
        return $avatarMap[$avatarType] ?? 'default.gif';
    }
    public function index()
    {
        $mapchats = Mapchat::where('user_id', Auth::id())->get();
        return view('mapchat.index', ['mapchats' => $mapchats]);
    }

    public function create()
    {
    return view('mapchat.criar');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'avatar' => 'nullable|string|max:500',
            'cidade' => 'nullable|string|max:255',
            'duracao' => 'nullable|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'contexto' => 'required|string|max:500',
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
        return view('mapchat.show', ['mapchat' => $mapchat]);
    }

    public function edit(Mapchat $mapchat)
    {
        return view('mapchat.edit', ['mapchat' => $mapchat]);
    }

    public function update(Request $request, Mapchat $mapchat)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'avatar' => 'nullable|string|max:500',
            'cidade' => 'nullable|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'contexto' => 'required|string|max:500',
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
        $mapchatsDisponiveis = Mapchat::where('privacidade', 'publica')
            ->whereNotIn('id', $jogadasIds)
            ->with('user')
            ->get();
        return view('mapchat.disponiveis', compact('mapchatsDisponiveis'));
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
        return view('mapchat.play', ['mapchat' => $mapchat, 'locations' => $locations]);
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
