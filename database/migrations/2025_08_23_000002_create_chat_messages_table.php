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
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_room_id')->constrained('chat_rooms')->onDelete('cascade');
            $table->string('user_id'); // 'user_123' ou 'anon_session123'
            $table->string('user_name'); // Nome do usuário ou nickname
            $table->string('user_type')->default('anonymous'); // 'registered' ou 'anonymous'
            $table->string('avatar_type')->default('default'); // Tipo do avatar
            $table->text('message'); // Conteúdo da mensagem
            $table->string('message_type')->default('text'); // 'text', 'emoji', 'system'
            $table->timestamp('sent_at'); // Data/hora do envio
            $table->boolean('is_visible')->default(true); // Para moderação
            $table->timestamps();
            
            // Índices para performance
            $table->index(['chat_room_id', 'sent_at']);
            $table->index(['user_id', 'chat_room_id']);
            $table->index('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
