<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chat_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_id')->unique(); // ID único da sala (baseado em coordenadas)
            $table->string('name')->nullable(); // Nome da sala (gerado automaticamente)
            $table->decimal('center_latitude', 10, 8); // Centro geográfico da sala
            $table->decimal('center_longitude', 11, 8); // Centro geográfico da sala
            $table->integer('radius_km')->default(100); // Raio da sala em km
            $table->integer('max_users')->default(100); // Limite de usuários
            $table->integer('current_users')->default(0); // Usuários atuais
            $table->boolean('is_active')->default(true); // Sala ativa
            $table->timestamp('last_activity')->nullable(); // Última atividade
            $table->timestamps();
            
            // Índices para performance
            $table->index(['center_latitude', 'center_longitude']);
            $table->index(['is_active', 'last_activity']);
            $table->index('room_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_rooms');
    }
};
