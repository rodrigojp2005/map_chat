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
        Schema::create('chat_room_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_room_id')->constrained('chat_rooms')->onDelete('cascade');
            $table->string('user_id'); // 'user_123' ou 'anon_session123'
            $table->string('user_name'); // Nome do usuário ou nickname
            $table->string('user_type')->default('anonymous'); // 'registered' ou 'anonymous'
            $table->string('avatar_type')->default('default'); // Tipo do avatar
            $table->decimal('latitude', 10, 8); // Localização atual do usuário
            $table->decimal('longitude', 11, 8); // Localização atual do usuário
            $table->timestamp('joined_at')->nullable(); // Quando entrou na sala
            $table->timestamp('last_seen')->nullable(); // Última atividade
            $table->boolean('is_active')->default(true); // Ativo na sala
            $table->timestamps();
            
            // Índices para performance
            $table->unique(['chat_room_id', 'user_id']); // Um usuário por sala
            $table->index(['chat_room_id', 'is_active', 'last_seen']);
            $table->index(['user_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_room_users');
    }
};
