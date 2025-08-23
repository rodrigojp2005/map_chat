<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ChatRoomService;
use App\Models\ChatRoom;
use App\Models\ChatMessage;
use App\Models\ChatRoomUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
            Log::error('Erro ao encontrar/criar sala de chat: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Criar ou obter sala global
     */
    private function createOrGetGlobalRoom()
    {
        $roomId = 'global_room';
        
        $room = ChatRoom::where('room_id', $roomId)->first();
        
        if (!$room) {
            $room = ChatRoom::create([
                'room_id' => $roomId,
                'name' => 'Chat Global',
                'center_latitude' => 0.0,
                'center_longitude' => 0.0,
                'radius_km' => 0,
                'max_users' => 1000,
                'current_users' => 0,
                'is_active' => true,
                'last_activity' => now()
            ]);
            
            Log::info("Sala global criada: {$roomId}");
        }
        
        return [
            'room_code' => $room->room_id, // Manter compatibilidade com frontend
            'name' => $room->name,
            'location' => 'Global',
            'active_users_count' => $this->getActiveUsersCount($room->room_id)
        ];
    }

    /**
     * Juntar usuário à sala global
     */
    private function joinGlobalRoom($roomCode, $userId, $userType)
    {
        // Buscar o ID da sala pelo room_id
        $room = ChatRoom::where('room_id', $roomCode)->first();
        if (!$room) {
            throw new \Exception("Sala não encontrada: {$roomCode}");
        }

        // Primeiro, limpar possíveis duplicatas do mesmo usuário
        ChatRoomUser::where('chat_room_id', $room->id)
            ->where('user_id', $userId)
            ->where('user_type', $userType)
            ->where('created_at', '<', now()->subMinutes(1)) // Manter apenas registros recentes
            ->delete();

        $existingUser = ChatRoomUser::where('chat_room_id', $room->id)
            ->where('user_id', $userId)
            ->where('user_type', $userType)
            ->first();

        if (!$existingUser) {
            // Criar nome de usuário baseado no tipo
            $userName = $userType === 'anonymous' ? 
                'Anônimo ' . substr($userId, -8) : 
                'Usuário';

            ChatRoomUser::create([
                'chat_room_id' => $room->id,
                'user_id' => $userId,
                'user_name' => $userName,
                'user_type' => $userType,
                'avatar_type' => 'default',
                'latitude' => 0.0,
                'longitude' => 0.0,
                'joined_at' => now(),
                'last_seen' => now(),
                'is_active' => true
            ]);
            
            Log::info("Usuário {$userType}:{$userId} juntou-se à sala global");
        } else {
            // Atualizar usuário existente como ativo
            $existingUser->update([
                'last_seen' => now(),
                'is_active' => true
            ]);
            
            Log::info("Usuário {$userType}:{$userId} reativado na sala global");
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
            // Remover qualquer prefixo 'anon_' existente antes de adicionar um novo
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
        
        // Debug: Log para entender os IDs
        \Log::info("Debug getSimpleUserInfo - userId: $userId, sessionId: $sessionId");
        
        $anonymousUser = \App\Models\AnonymousUser::where('session_id', $sessionId)->first();
        
        if ($anonymousUser) {
            \Log::info("Debug - Encontrou usuário: " . json_encode([
                'session_id' => $anonymousUser->session_id,
                'name' => $anonymousUser->name
            ]));
        } else {
            \Log::info("Debug - Usuário não encontrado, tentando com prefixo anon_");
            $anonymousUser = \App\Models\AnonymousUser::where('session_id', $userId)->first();
            if ($anonymousUser) {
                \Log::info("Debug - Encontrou com prefixo: " . json_encode([
                    'session_id' => $anonymousUser->session_id,
                    'name' => $anonymousUser->name
                ]));
            }
        }
        
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
        // Buscar o ID da sala pelo room_id
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
    public function getMessages(Request $request)
    {
        try {
            $roomCode = $request->get('room_code', 'global_room');
            $limit = $request->get('limit', 50);

            // Buscar o ID da sala pelo room_id
            $room = ChatRoom::where('room_id', $roomCode)->first();
            if (!$room) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sala não encontrada'
                ], 404);
            }

            $messages = ChatMessage::where('chat_room_id', $room->id)
                ->where('is_visible', true)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->reverse()
                ->values()
                ->map(function($message) {
                    return [
                        'id' => $message->id,
                        'user_id' => $message->user_id,
                        'user_name' => $message->user_name,
                        'user_type' => $message->user_type,
                        'message' => $message->message,
                        'created_at' => $message->created_at
                    ];
                });

            return response()->json([
                'success' => true,
                'messages' => $messages
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao obter mensagens: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar mensagens'
            ], 500);
        }
    }

    /**
     * Enviar mensagem
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'room_code' => 'required|string',
            'message' => 'required|string|max:1000'
        ]);

        try {
            $userId = $this->getCurrentUserId($request);
            $userType = $this->getUserType();
            $roomCode = $request->get('room_code', 'global_room');

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não identificado'
                ], 400);
            }

            // Buscar o ID da sala pelo room_id
            $room = ChatRoom::where('room_id', $roomCode)->first();
            if (!$room) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sala não encontrada'
                ], 404);
            }

            // Auto-juntar à sala global se não estiver
            $this->joinGlobalRoom($roomCode, $userId, $userType);

            // Obter informações do usuário para criar a mensagem
            $userInfo = $this->getSimpleUserInfo($userId, $userType);

            // Criar mensagem
            $message = ChatMessage::create([
                'chat_room_id' => $room->id,
                'user_id' => $userId,
                'user_name' => $userInfo['user_name'],
                'user_type' => $userType,
                'avatar_type' => $userInfo['avatar_type'],
                'message' => $request->message,
                'message_type' => 'text',
                'sent_at' => now(),
                'is_visible' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'user_id' => $message->user_id,
                    'user_name' => $message->user_name,
                    'user_type' => $message->user_type,
                    'message' => $message->message,
                    'created_at' => $message->created_at
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao enviar mensagem: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar mensagem'
            ], 500);
        }
    }
} 