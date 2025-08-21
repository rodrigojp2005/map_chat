<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LocationService;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    protected $locationService;
    
    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }
    
    /**
     * Atualiza a localização do usuário
     */
    public function updateLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'privacy_radius' => 'nullable|integer|min:500|max:5000000' // 500m a 5000km em metros
        ]);
        
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Usuário não autenticado'], 401);
        }
        
        $radiusKm = $request->privacy_radius ? $request->privacy_radius / 1000 : 50; // converter para km
        
        $this->locationService->updateUserLocation(
            $user,
            $request->latitude,
            $request->longitude,
            $radiusKm
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Localização atualizada com sucesso',
            'anonymized_location' => [
                'latitude' => $user->fresh()->latitude,
                'longitude' => $user->fresh()->longitude
            ]
        ]);
    }
    
    /**
     * Atualiza o avatar do usuário
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar_type' => 'required|in:default,man,woman,pet,geek,sport'
        ]);
        
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Usuário não autenticado'], 401);
        }
        
        $user->update(['avatar_type' => $request->avatar_type]);
        
        return response()->json([
            'success' => true,
            'message' => 'Avatar atualizado com sucesso'
        ]);
    }
    
    /**
     * Marca usuário como offline
     */
    public function setOffline()
    {
        $user = Auth::user();
        if ($user) {
            $this->locationService->setUserOffline($user);
        }
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Obter usuários online (endpoint público)
     */
    public function getOnlineUsers()
    {
        $users = $this->locationService->getOnlineUsers();
        
        return response()->json([
            'success' => true,
            'users' => $users
        ]);
    }
    
    /**
     * Atualizar raio de privacidade
     */
    public function updatePrivacyRadius(Request $request)
    {
        $request->validate([
            'radius' => 'required|integer|min:500|max:5000000' // 500m a 5000km em metros
        ]);
        
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Usuário não autenticado'], 401);
        }
        
        // Se o usuário tem localização real, regenerar a localização aleatória
        if ($user->real_latitude && $user->real_longitude) {
            $radiusKm = $request->radius / 1000;
            $this->locationService->updateUserLocation(
                $user,
                $user->real_latitude,
                $user->real_longitude,
                $radiusKm
            );
        } else {
            // Se não tem localização real, apenas atualizar o raio
            $user->update(['privacy_radius' => $request->radius]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Raio de privacidade atualizado',
            'new_location' => [
                'latitude' => $user->fresh()->latitude,
                'longitude' => $user->fresh()->longitude
            ]
        ]);
    }
}
