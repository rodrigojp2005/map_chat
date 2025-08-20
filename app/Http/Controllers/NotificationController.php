<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
    // Lista agregada por sala (mapchat). Mantemos as chaves "gincana_*" por compatibilidade no front-end.
    $items = \App\Models\GincanaCommentNotification::with('mapchat:id,nome')
            ->where('user_id', $user->id)
            ->where('unread_count', '>', 0)
            ->orderByDesc('updated_at')
            ->get()
            ->map(function($n){
                return [
            'gincana_id' => $n->mapchat_id,
            'gincana_nome' => $n->mapchat?->nome,
                    'unread_count' => (int)$n->unread_count,
                    'last_preview' => $n->last_preview,
                    'last_author_name' => $n->last_author_name,
                    'updated_at' => $n->updated_at->toDateTimeString(),
                ];
            });

        return response()->json([
            'unread_groups' => $items->count(), // sino = número de mapchats com novidades
            'gincanas' => $items,
        ]);
    }

    public function markRead(Request $request)
    {
        // Marca como lido de forma agregada por mapchat (zera o contador)
        $request->validate([
            'gincana_id' => 'nullable|integer'
        ]);
        $user = Auth::user();
        if ($request->filled('gincana_id')) {
            \App\Models\GincanaCommentNotification::where('user_id', $user->id)
                ->where('mapchat_id', $request->integer('gincana_id'))
                ->update(['unread_count' => 0]);
        } else {
            \App\Models\GincanaCommentNotification::where('user_id', $user->id)
                ->update(['unread_count' => 0]);
        }
        return response()->json(['success' => true]);
    }
}
