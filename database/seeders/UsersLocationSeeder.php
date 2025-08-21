<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Services\LocationService;
use Illuminate\Support\Facades\Hash;

class UsersLocationSeeder extends Seeder
{
    public function run(): void
    {
        $locationService = new LocationService();
        
        $users = [
            [
                'name' => 'João Silva',
                'email' => 'joao@exemplo.com',
                'real_lat' => -23.5505, 'real_lng' => -46.6333, // São Paulo
                'avatar_type' => 'man'
            ],
            [
                'name' => 'Maria Santos',
                'email' => 'maria@exemplo.com',
                'real_lat' => -22.9068, 'real_lng' => -43.1729, // Rio de Janeiro
                'avatar_type' => 'woman'
            ],
            [
                'name' => 'Pedro Gamer',
                'email' => 'pedro@exemplo.com',
                'real_lat' => -15.7942, 'real_lng' => -47.8822, // Brasília
                'avatar_type' => 'geek'
            ],
            [
                'name' => 'Ana Esportista',
                'email' => 'ana@exemplo.com',
                'real_lat' => -25.4244, 'real_lng' => -49.2654, // Curitiba
                'avatar_type' => 'sport'
            ],
            [
                'name' => 'Carlos Pet Lover',
                'email' => 'carlos@exemplo.com',
                'real_lat' => -30.0346, 'real_lng' => -51.2177, // Porto Alegre
                'avatar_type' => 'pet'
            ],
        ];
        
        foreach ($users as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password123'),
                    'avatar_type' => $userData['avatar_type'],
                    'is_online' => true,
                    'last_seen' => now()
                ]
            );
            
            $randomLocation = $locationService->generateRandomLocation(
                $userData['real_lat'],
                $userData['real_lng'],
                rand(10, 100)
            );
            
            $user->update([
                'real_latitude' => $userData['real_lat'],
                'real_longitude' => $userData['real_lng'],
                'latitude' => $randomLocation['latitude'],
                'longitude' => $randomLocation['longitude'],
                'privacy_radius' => rand(10000, 100000)
            ]);
            
            $this->command->info("Usuário {$user->name} criado com localização randomizada");
        }
    }
}
