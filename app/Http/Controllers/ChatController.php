<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ChatRoomService;
use App\Models\ChatRoom;
use App\Models\ChatMessage;
use App\Models\ChatRoomUser;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    protected $chatRoomService;
    
    public function __construct(ChatRoomService $chatRoomService)
    {
        $this->chatRoomService = $chatRoomService;
    }

    /**
     * Encontrar ou criar sala de chat global simplificada
     */
    public function findOrCreateRoom(Request $request)
    {
        try {
            $userId = $this->getCurrentUserId($request);
            $userType = $this->getUserType();

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não identificado'
                ], 400);
            }

            // Criar ou obter sala global
            $roomInfo = $this->createOrGetGlobalRoom();
            
            // Juntar usuário à sala global
            $this->joinGlobalRoom($roomInfo['room_code'], $userId, $userType);

            return response()->json([
                'success' => true,
                'room' => $roomInfo,
                'user_id' => $userId,
                'user_type' => $userType
            ]);

        } catch (\Exception $e) {
            // Debug: capturar erro específico
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor',
                'debug' => [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
    }

    /**
     * Criar ou obter sala global
     */
    private function createOrGetGlobalRoom()
    {
        $roomId = 'global_chat';
        
        $room = ChatRoom::firstOrCreate([
            'room_id' => $roomId
        ], [
            'name' => 'Chat Global',
            'is_active' => true,
            'max_users' => 1000,
            'center_latitude' => 0.0,
            'center_longitude' => 0.0,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return [
            'room_code' => $roomId,
            'name' => 'Chat Global',
            'id' => $room->id,
            'users_count' => $this->getActiveUsersCount($roomId)
        ];
    }

    /**
     * Adicionar usuário à sala global
     */
    private function joinGlobalRoom($roomCode, $userId, $userType)
    {
        $room = ChatRoom::where('room_id', $roomCode)->first();
        if (!$room) return;

        $existingUser = ChatRoomUser::where('chat_room_id', $room->id)
            ->where('user_id', $userId)
            ->first();

        if (!$existingUser) {
            // Obter nome do usuário
            $userInfo = $this->getSimpleUserInfo($userId, $userType);
            $userName = $userInfo['user_name'] ?? 'Usuário Anônimo';
            
            ChatRoomUser::create([
                'chat_room_id' => $room->id,
                'user_id' => $userId,
                'user_type' => $userType,
                'user_name' => $userName,
                'joined_at' => now(),
                'is_active' => true
            ]);
        } else {
            $existingUser->update([
                'is_active' => true,
                'joined_at' => now()
            ]);
        }
    }

    /**
     * Obter usuários de uma sala
     */
    public function getRoomUsers(Request $request, $roomCode)
    {
        try {
            $userId = $this->getCurrentUserId($request);
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não identificado'
                ], 400);
            }

            $room = ChatRoom::where('room_id', $roomCode)->first();
            if (!$room) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sala não encontrada'
                ], 404);
            }

            $users = ChatRoomUser::where('chat_room_id', $room->id)
                ->where('is_active', true)
                ->where('joined_at', '>=', now()->subHours(24))
                ->get()
                ->map(function($user) {
                    $userInfo = $this->getSimpleUserInfo($user->user_id, $user->user_type);
                    return [
                        'user_id' => $user->user_id,
                        'user_name' => $userInfo['user_name'],
                        'user_type' => $user->user_type,
                        'joined_at' => $user->joined_at,
                        'avatar_type' => $userInfo['avatar_type']
                    ];
                });

            return response()->json([
                'success' => true,
                'users' => $users->toArray()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno'
            ], 500);
        }
    }

    /**
     * Obter ID do usuário atual
     */
    private function getCurrentUserId(Request $request)
    {
        if (Auth::check()) {
            return Auth::id();
        }

        $sessionId = $request->get('anonymous_session_id') ?? $request->header('X-Anonymous-Session-ID');
        
        if ($sessionId) {
            $cleanSessionId = str_starts_with($sessionId, 'anon_') ? substr($sessionId, 5) : $sessionId;
            return 'anon_' . $cleanSessionId;
        }

        return null;
    }

    /**
     * Obter tipo do usuário
     */
    private function getUserType()
    {
        return Auth::check() ? 'authenticated' : 'anonymous';
    }

    /**
     * Obter informações simples do usuário
     */
    private function getSimpleUserInfo($userId, $userType)
    {
        if ($userType === 'authenticated' && Auth::check()) {
            $user = Auth::user();
            return [
                'user_name' => $user->name ?? 'Usuário',
                'avatar_type' => 'user'
            ];
        }
        
        // Usuário anônimo - tentar buscar nome personalizado
        $sessionId = str_replace('anon_', '', $userId);
        $anonymousUser = \App\Models\AnonymousUser::where('session_id', $sessionId)->first();
        
        if ($anonymousUser && $anonymousUser->name && $anonymousUser->name !== 'Usuário Anônimo') {
            return [
                'user_name' => $anonymousUser->name,
                'avatar_type' => $anonymousUser->avatar_type ?? 'default'
            ];
        }

        // Nome padrão se não encontrou nome personalizado
        return [
            'user_name' => 'Anônimo ' . substr($userId, -8),
            'avatar_type' => 'default'
        ];
    }

    /**
     * Contar usuários ativos em uma sala
     */
    private function getActiveUsersCount($roomCode)
    {
        $room = ChatRoom::where('room_id', $roomCode)->first();
        if (!$room) {
            return 0;
        }

        return ChatRoomUser::where('chat_room_id', $room->id)
            ->where('joined_at', '>=', now()->subHours(24))
            ->where('is_active', true)
            ->count();
    }

    /**
     * Obter mensagens de uma sala
     */
    public function getMessages(Request $request, $roomCode)
    {
        try {
            $userId = $this->getCurrentUserId($request);
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não identificado'
                ], 400);
            }

            $room = ChatRoom::where('room_id', $roomCode)->first();
            if (!$room) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sala não encontrada'
                ], 404);
            }

            $messages = ChatMessage::where('chat_room_id', $room->id)
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get()
                ->reverse()
                ->values()
                ->map(function($message) {
                    $userInfo = $this->getSimpleUserInfo($message->user_id, $message->user_type);
                    return [
                        'id' => $message->id,
                        'content' => $message->content,
                        'user_id' => $message->user_id,
                        'user_name' => $userInfo['user_name'],
                        'user_type' => $message->user_type,
                        'created_at' => $message->created_at,
                        'avatar_type' => $userInfo['avatar_type']
                    ];
                });

            return response()->json([
                'success' => true,
                'messages' => $messages->toArray()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar mensagens'
            ], 500);
        }
    }

    /**
     * Enviar mensagem
     */
    public function sendMessage(Request $request, $roomCode)
    {
        try {
            $userId = $this->getCurrentUserId($request);
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não identificado'
                ], 400);
            }

            $request->validate([
                'content' => 'required|string|max:500'
            ]);

            $room = ChatRoom::where('room_id', $roomCode)->first();
            if (!$room) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sala não encontrada'
                ], 404);
            }

            $message = ChatMessage::create([
                'chat_room_id' => $room->id,
                'user_id' => $userId,
                'user_type' => $this->getUserType(),
                'content' => $request->content,
                'created_at' => now()
            ]);

            $userInfo = $this->getSimpleUserInfo($userId, $this->getUserType());

            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'content' => $message->content,
                    'user_id' => $userId,
                    'user_name' => $userInfo['user_name'],
                    'user_type' => $this->getUserType(),
                    'created_at' => $message->created_at,
                    'avatar_type' => $userInfo['avatar_type']
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar mensagem'
            ], 500);
        }
    }

    /**
     * Heartbeat para manter usuário ativo
     */
    public function heartbeat(Request $request, $roomCode)
    {
        try {
            $userId = $this->getCurrentUserId($request);
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não identificado'
                ], 400);
            }

            $room = ChatRoom::where('room_id', $roomCode)->first();
            if (!$room) {
                return response()->json(['success' => false], 404);
            }

            ChatRoomUser::where('chat_room_id', $room->id)
                ->where('user_id', $userId)
                ->update([
                    'joined_at' => now(),
                    'is_active' => true
                ]);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }
}
