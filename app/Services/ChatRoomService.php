<?php

namespace App\Services;

use App\Models\ChatRoom;
use App\Models\ChatMessage;
use App\Models\ChatRoomUser;
use App\Models\User;
use App\Models\AnonymousUser;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ChatRoomService
{
    private $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    /**
     * Encontrar ou criar sala de chat baseada na localização do usuário
     */
    public function findOrCreateChatRoom($userId, $userType = 'anonymous'): ?ChatRoom
    {
        \Log::info('ChatRoomService: findOrCreateChatRoom', [
            'userId' => $userId,
            'userType' => $userType
        ]);
        
        $userLocation = $this->getUserLocation($userId, $userType);
        
        \Log::info('ChatRoomService: userLocation result', [
            'userLocation' => $userLocation
        ]);
        
        if (!$userLocation) {
            \Log::warning('ChatRoomService: No user location found');
            return null;
        }

        // Primeiro, tentar encontrar sala existente próxima
        $existingRoom = $this->findNearbyRoom(
            $userLocation['latitude'], 
            $userLocation['longitude']
        );

        if ($existingRoom && $existingRoom->canAcceptUser()) {
            \Log::info('ChatRoomService: Found existing room', ['room_id' => $existingRoom->room_id]);
            return $existingRoom;
        }

        // Se não encontrou sala adequada, criar nova
        $newRoom = $this->createNewRoom(
            $userLocation['latitude'], 
            $userLocation['longitude'],
            $this->calculateOptimalRadius($userLocation['latitude'], $userLocation['longitude'])
        );
        
        \Log::info('ChatRoomService: Created new room', ['room_id' => $newRoom?->room_id]);
        
        return $newRoom;
    }

    /**
     * Encontrar sala próxima existente
     */
    private function findNearbyRoom($latitude, $longitude): ?ChatRoom
    {
        return ChatRoom::active()
            ->withinRadius($latitude, $longitude, 500) // Buscar em 500km
            ->where('current_users', '<', 100) // Que não esteja lotada
            ->first();
    }

    /**
     * Criar nova sala de chat
     */
    private function createNewRoom($latitude, $longitude, $radiusKm = 100): ChatRoom
    {
        $roomId = ChatRoom::generateRoomId($latitude, $longitude, $radiusKm);
        $roomName = ChatRoom::generateRoomName($latitude, $longitude);

        return ChatRoom::create([
            'room_id' => $roomId,
            'name' => $roomName,
            'center_latitude' => $latitude,
            'center_longitude' => $longitude,
            'radius_km' => $radiusKm,
            'max_users' => 100,
            'current_users' => 0,
            'is_active' => true,
            'last_activity' => now()
        ]);
    }

    /**
     * Calcular raio ótimo baseado na densidade de usuários
     */
    private function calculateOptimalRadius($latitude, $longitude): int
    {
        // Contar usuários online num raio de 1000km
        $onlineUsers = $this->locationService->getOnlineUsers();
        
        $nearbyUsers = collect($onlineUsers)->filter(function($user) use ($latitude, $longitude) {
            $distance = $this->locationService->calculateDistance(
                $latitude, $longitude, 
                $user['latitude'], $user['longitude']
            );
            return $distance <= 1000; // 1000km
        })->count();

        // Ajustar raio baseado na densidade
        if ($nearbyUsers <= 2) {
            return 500; // Raio maior para poucos usuários
        } elseif ($nearbyUsers <= 10) {
            return 200; // Raio médio
        } elseif ($nearbyUsers <= 50) {
            return 100; // Raio padrão
        } else {
            return 50;  // Raio menor para muitos usuários
        }
    }

    /**
     * Adicionar usuário a uma sala de chat
     */
    public function joinChatRoom($roomId, $userId, $userType = 'anonymous'): bool
    {
        \Log::info('ChatRoomService: joinChatRoom', [
            'roomId' => $roomId,
            'userId' => $userId,
            'userType' => $userType
        ]);
        
        $room = ChatRoom::where('room_id', $roomId)->first();
        \Log::info('ChatRoomService: room found', ['room' => !!$room]);
        
        if (!$room || !$room->canAcceptUser()) {
            \Log::warning('ChatRoomService: room not found or cannot accept user', [
                'room_exists' => !!$room,
                'can_accept' => $room ? $room->canAcceptUser() : false
            ]);
            return false;
        }

        $userInfo = $this->getUserInfo($userId, $userType);
        \Log::info('ChatRoomService: userInfo', ['userInfo' => $userInfo]);
        
        if (!$userInfo) {
            \Log::warning('ChatRoomService: userInfo not found');
            return false;
        }

        // Verificar se usuário já está na sala
        $existingUser = ChatRoomUser::where('chat_room_id', $room->id)
                                   ->where('user_id', $userId)
                                   ->first();

        \Log::info('ChatRoomService: existing user check', ['exists' => !!$existingUser]);

        if ($existingUser) {
            // Atualizar atividade se já existe
            $existingUser->updateActivity();
            \Log::info('ChatRoomService: updated existing user activity');
        } else {
            // Criar novo registro de usuário na sala
            try {
                ChatRoomUser::create([
                    'chat_room_id' => $room->id,
                    'user_id' => $userId,
                    'user_name' => $userInfo['name'],
                    'user_type' => $userType,
                    'avatar_type' => $userInfo['avatar_type'],
                    'latitude' => $userInfo['latitude'],
                    'longitude' => $userInfo['longitude'],
                    'joined_at' => now(),
                    'last_seen' => now(),
                    'is_active' => true
                ]);
                
                \Log::info('ChatRoomService: created new user in room');

                // Enviar mensagem de boas-vindas
                $this->sendSystemMessage($room->id, "{$userInfo['name']} entrou na sala");
                \Log::info('ChatRoomService: sent welcome message');
            } catch (\Exception $e) {
                \Log::error('ChatRoomService: failed to create user in room', [
                    'error' => $e->getMessage(),
                    'data' => [
                        'chat_room_id' => $room->id,
                        'user_id' => $userId,
                        'user_name' => $userInfo['name'],
                        'user_type' => $userType,
                        'avatar_type' => $userInfo['avatar_type'],
                        'latitude' => $userInfo['latitude'],
                        'longitude' => $userInfo['longitude']
                    ]
                ]);
                return false;
            }
        }

        // Atualizar contagem de usuários da sala
        $room->updateUserCount();
        $room->touchActivity();
        
        \Log::info('ChatRoomService: joinChatRoom completed successfully');

        return true;
    }

    /**
     * Remover usuário da sala
     */
    public function leaveChatRoom($roomId, $userId): bool
    {
        $room = ChatRoom::where('room_id', $roomId)->first();
        if (!$room) {
            return false;
        }

        $roomUser = ChatRoomUser::where('chat_room_id', $room->id)
                                ->where('user_id', $userId)
                                ->first();

        if ($roomUser) {
            $userName = $roomUser->user_name;
            $roomUser->markInactive();

            // Enviar mensagem de despedida
            $this->sendSystemMessage($room->id, "{$userName} saiu da sala");

            // Atualizar contagem de usuários
            $room->updateUserCount();
            
            // Se sala ficou vazia, marcar como inativa
            if ($room->current_users == 0) {
                $room->update(['is_active' => false]);
            }
        }

        return true;
    }

    /**
     * Enviar mensagem para a sala
     */
    public function sendMessage($roomId, $userId, $message, $userType = 'anonymous', $messageType = 'text'): ?ChatMessage
    {
        $room = ChatRoom::where('room_id', $roomId)->first();
        if (!$room) {
            return null;
        }

        // Verificar se usuário está na sala
        $roomUser = ChatRoomUser::where('chat_room_id', $room->id)
                                ->where('user_id', $userId)
                                ->where('is_active', true)
                                ->first();

        if (!$roomUser) {
            return null;
        }

        // Validar e limitar tamanho da mensagem
        $message = trim($message);
        if (empty($message) || strlen($message) > 500) {
            return null;
        }

        // Criar mensagem
        $chatMessage = ChatMessage::create([
            'chat_room_id' => $room->id,
            'user_id' => $userId,
            'user_name' => $roomUser->user_name,
            'user_type' => $userType,
            'avatar_type' => $roomUser->avatar_type,
            'message' => $message,
            'message_type' => $messageType,
            'sent_at' => now(),
            'is_visible' => true
        ]);

        // Atualizar atividade do usuário e da sala
        $roomUser->updateActivity();
        $room->touchActivity();

        return $chatMessage;
    }

    /**
     * Enviar mensagem do sistema
     */
    public function sendSystemMessage($roomId, $message): ?ChatMessage
    {
        $room = ChatRoom::find($roomId);
        if (!$room) {
            return null;
        }

        return ChatMessage::create([
            'chat_room_id' => $room->id,
            'user_id' => 'system',
            'user_name' => 'Sistema',
            'user_type' => 'system',
            'avatar_type' => 'default',
            'message' => $message,
            'message_type' => 'system',
            'sent_at' => now(),
            'is_visible' => true
        ]);
    }

    /**
     * Obter mensagens da sala
     */
    public function getChatMessages($roomId, $limit = 50): Collection
    {
        $room = ChatRoom::where('room_id', $roomId)->first();
        if (!$room) {
            return collect([]);
        }

        return ChatMessage::byRoom($room->id)
                          ->visible()
                          ->with([]) // Não precisamos de relacionamentos adicionais
                          ->orderBy('sent_at', 'desc')
                          ->limit($limit)
                          ->get()
                          ->reverse() // Mais antigas primeiro para exibição
                          ->map(function($message) {
                              return [
                                  'id' => $message->id,
                                  'user_id' => $message->user_id,
                                  'user_name' => $message->user_name,
                                  'user_type' => $message->user_type,
                                  'avatar_type' => $message->avatar_type,
                                  'avatar_url' => $message->avatar_url,
                                  'message' => $message->formatted_message,
                                  'message_type' => $message->message_type,
                                  'sent_at' => $message->sent_at->toISOString(),
                                  'sent_at_human' => $message->sent_at->diffForHumans(),
                                  'is_system' => $message->isSystemMessage()
                              ];
                          });
    }

    /**
     * Obter usuários ativos da sala
     */
    public function getChatRoomUsers($roomId): Collection
    {
        $room = ChatRoom::where('room_id', $roomId)->first();
        if (!$room) {
            return collect([]);
        }

        return ChatRoomUser::inRoom($room->id)
                          ->active()
                          ->orderBy('joined_at', 'asc')
                          ->get()
                          ->map(function($user) {
                              return [
                                  'user_id' => $user->user_id,
                                  'name' => $user->user_name,
                                  'user_type' => $user->user_type,
                                  'avatar_type' => $user->avatar_type,
                                  'avatar_url' => $user->avatar_url,
                                  'joined_at' => $user->joined_at->toISOString(),
                                  'time_in_room' => $user->time_in_room,
                                  'is_online' => $user->isOnline()
                              ];
                          });
    }

    /**
     * Obter informações da sala
     */
    public function getChatRoomInfo($roomId): ?array
    {
        $room = ChatRoom::where('room_id', $roomId)->first();
        if (!$room) {
            return null;
        }

        return [
            'room_id' => $room->room_id,
            'name' => $room->name,
            'center_latitude' => $room->center_latitude,
            'center_longitude' => $room->center_longitude,
            'radius_km' => $room->radius_km,
            'current_users' => $room->current_users,
            'max_users' => $room->max_users,
            'is_active' => $room->is_active,
            'last_activity' => $room->last_activity?->toISOString(),
            'created_at' => $room->created_at->toISOString()
        ];
    }

    /**
     * Obter localização do usuário
     */
    private function getUserLocation($userId, $userType): ?array
    {
        if ($userType === 'registered') {
            $user = User::find(str_replace('user_', '', $userId));
            if ($user && $user->latitude && $user->longitude) {
                return [
                    'latitude' => $user->latitude,
                    'longitude' => $user->longitude
                ];
            }
        } else {
            // O $userId já vem no formato 'anon_anon_1755973698843_jwy01zg49'
            // Mas no banco está salvo como 'anon_1755973698843_jwy01zg49'
            $sessionId = $userId;
            
            // Se tem prefixo duplo 'anon_anon_', remover um 'anon_'
            if (str_starts_with($userId, 'anon_anon_')) {
                $sessionId = str_replace('anon_anon_', 'anon_', $userId);
            }
            
            $user = AnonymousUser::where('session_id', $sessionId)->first();
            if ($user && $user->latitude && $user->longitude) {
                return [
                    'latitude' => $user->latitude,
                    'longitude' => $user->longitude
                ];
            }
        }

        return null;
    }

    /**
     * Obter informações do usuário
     */
    private function getUserInfo($userId, $userType): ?array
    {
        if ($userType === 'registered') {
            $user = User::find(str_replace('user_', '', $userId));
            if ($user) {
                return [
                    'name' => $user->name,
                    'avatar_type' => $user->avatar_type ?? 'default',
                    'latitude' => $user->latitude,
                    'longitude' => $user->longitude
                ];
            }
        } else {
            // O $userId já vem no formato 'anon_anon_1755973698843_jwy01zg49'
            // Mas no banco está salvo como 'anon_1755973698843_jwy01zg49'
            $sessionId = $userId;
            
            // Se tem prefixo duplo 'anon_anon_', remover um 'anon_'
            if (str_starts_with($userId, 'anon_anon_')) {
                $sessionId = str_replace('anon_anon_', 'anon_', $userId);
            }
            
            $user = AnonymousUser::where('session_id', $sessionId)->first();
            if ($user) {
                return [
                    'name' => $user->name,
                    'avatar_type' => $user->avatar_type ?? 'anonymous',
                    'latitude' => $user->latitude,
                    'longitude' => $user->longitude
                ];
            }
        }

        return null;
    }

    /**
     * Verificar e reorganizar salas baseado na localização dos usuários
     */
    public function reorganizeRooms(): void
    {
        $activeRooms = ChatRoom::active()->get();
        
        foreach ($activeRooms as $room) {
            $users = $room->activeUsers;
            
            if ($users->count() == 0) {
                $room->update(['is_active' => false]);
                continue;
            }

            // Se há mais usuários do que o limite, tentar dividir a sala
            if ($users->count() > $room->max_users) {
                $this->splitChatRoom($room);
            }
            
            // Se há poucos usuários, tentar mesclar com sala próxima
            elseif ($users->count() < 5) {
                $this->tryMergeWithNearbyRoom($room);
            }
        }
    }

    /**
     * Dividir sala com muitos usuários
     */
    private function splitChatRoom(ChatRoom $room): void
    {
        $users = $room->activeUsers()->get();
        
        // Dividir usuários em dois grupos baseado na localização
        $group1 = $users->take($users->count() / 2);
        $group2 = $users->skip($users->count() / 2);

        // Calcular centro do primeiro grupo
        $center1 = $this->calculateGroupCenter($group1);
        
        // Criar nova sala para o segundo grupo
        $newRoom = $this->createNewRoom(
            $center1['latitude'],
            $center1['longitude'],
            $room->radius_km / 2
        );

        // Mover usuários do grupo 2 para a nova sala
        foreach ($group2 as $user) {
            $user->update(['chat_room_id' => $newRoom->id]);
        }

        // Atualizar contagens
        $room->updateUserCount();
        $newRoom->updateUserCount();

        // Enviar mensagens informativas
        $this->sendSystemMessage($room->id, "A sala foi dividida devido ao número de usuários");
        $this->sendSystemMessage($newRoom->id, "Bem-vindos à nova sala!");
    }

    /**
     * Tentar mesclar sala com poucas pessoas com sala próxima
     */
    private function tryMergeWithNearbyRoom(ChatRoom $room): void
    {
        $nearbyRoom = ChatRoom::active()
            ->where('id', '!=', $room->id)
            ->withinRadius($room->center_latitude, $room->center_longitude, $room->radius_km * 2)
            ->first();

        if ($nearbyRoom && ($nearbyRoom->current_users + $room->current_users) <= $nearbyRoom->max_users) {
            // Mover todos os usuários para a sala próxima
            ChatRoomUser::where('chat_room_id', $room->id)
                        ->where('is_active', true)
                        ->update(['chat_room_id' => $nearbyRoom->id]);

            // Enviar mensagem informativa
            $this->sendSystemMessage($nearbyRoom->id, "Usuários de uma sala próxima se juntaram a vocês!");

            // Desativar sala vazia
            $room->update(['is_active' => false]);
            $nearbyRoom->updateUserCount();
        }
    }

    /**
     * Calcular centro geográfico de um grupo de usuários
     */
    private function calculateGroupCenter($users): array
    {
        $totalLat = $users->sum('latitude');
        $totalLng = $users->sum('longitude');
        $count = $users->count();

        return [
            'latitude' => $totalLat / $count,
            'longitude' => $totalLng / $count
        ];
    }

    /**
     * Limpar dados antigos
     */
    public function cleanup(): void
    {
        // Limpar usuários inativos
        ChatRoomUser::cleanupInactiveUsers();
        
        // Limpar salas inativas
        ChatRoom::cleanupInactiveRooms();
        
        // Executar reorganização das salas
        $this->reorganizeRooms();
    }
}
