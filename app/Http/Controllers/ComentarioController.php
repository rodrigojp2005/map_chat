<?php

namespace App\Http\Controllers;

use App\Models\Comentario;
use App\Models\Mapchat;
use App\Notifications\NewCommentNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ComentarioController extends Controller
{
    public function index($mapchat_id)
    {
        try {
            // Primeiro verificar se é um número válido
            if (!is_numeric($mapchat_id)) {
                return response()->json(['error' => 'ID inválido'], 400);
            }
            
            $comentarios = Comentario::where('mapchat_id', $mapchat_id)
                ->with('user:id,name')  // Carregar apenas id e name do usuário
                ->orderBy('created_at', 'desc')
                ->get();
            
            return response()->json($comentarios);
            
        } catch (\Exception $e) {
            \Log::error("Erro ao buscar comentários: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        // Verificar se o usuário está logado
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Você precisa fazer login para comentar.',
                'redirect' => '/login'
            ], 401);
        }

        try {
            // Validação básica
            $validated = $request->validate([
                'mapchat_id' => 'required|integer',
                'conteudo' => 'required|string|max:500'
            ]);

            $comentario = Comentario::create([
                'mapchat_id' => $validated['mapchat_id'],
                'user_id' => Auth::id(),
                'conteudo' => $validated['conteudo']
            ]);

            $comentario->load('user:id,name');

            // Notificar criador e quem já comentou; atualizar contador agregado por gincana
            try {
                $mapchat = Mapchat::find($comentario->mapchat_id);
                if ($mapchat) {
                    $criadorId = $mapchat->user_id;
                    $userId = Auth::id();
                    
                    // quem comentou antes neste mapchat (exclui o comentário atual e o autor atual)
                    $comentouAntesIds = Comentario::where('mapchat_id', $mapchat->id)
                        ->where('id', '!=', $comentario->id)
                        ->pluck('user_id')
                        ->unique()
                        ->filter(fn($id) => (int)$id !== (int)$userId);

                    // Regra: criador sempre recebe (mesmo que não tenha comentado)
                    $targets = collect();
                    if ($criadorId && (int)$criadorId !== (int)$userId) {
                        $targets->push($criadorId);
                    }
                    $targets = $targets->merge($comentouAntesIds)->unique();

            if ($targets->isNotEmpty()) {
                        $notificaveis = \App\Models\User::whereIn('id', $targets)->get();
                        // Enviar push leve e atualizar contador agregado
                        foreach ($notificaveis as $u) {
                            // Push/WebPush sem poluir lista com 1 item por comentário
                            $u->notify(new NewCommentNotification($comentario));

                            // Atualização do contador agregado
                \App\Models\GincanaCommentNotification::query()
                                ->updateOrCreate(
                    ['user_id' => $u->id, 'mapchat_id' => $mapchat->id],
                                    [
                                        'last_comentario_id' => $comentario->id,
                                        'last_author_name' => $comentario->user?->name,
                                        'last_preview' => \Illuminate\Support\Str::limit($comentario->conteudo, 80),
                                    ]
                                );
                            // incrementar sem condições de corrida relevantes (operação simples)
                \DB::table('mapchat_comment_notifications')
                                ->where('user_id', $u->id)
                ->where('mapchat_id', $mapchat->id)
                                ->increment('unread_count');
                        }
                    }
                }
            } catch (\Exception $notifyEx) {
                Log::warning('Falha ao enviar notificações de comentário: ' . $notifyEx->getMessage());
            }

            return response()->json([
                'success' => true,
                'comentario' => $comentario
            ]);
            
        } catch (\Exception $e) {
            \Log::error("Erro ao salvar comentário: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
