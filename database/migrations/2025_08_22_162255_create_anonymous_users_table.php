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
        Schema::create('anonymous_users', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique(); // ID único da sessão anônima
            $table->string('name')->default('Usuário Anônimo'); // nome padrão
            $table->double('real_latitude', 10, 8)->nullable();
            $table->double('real_longitude', 11, 8)->nullable();
            $table->double('latitude', 10, 8)->nullable(); // coordenada com privacidade
            $table->double('longitude', 11, 8)->nullable(); // coordenada com privacidade
            $table->integer('privacy_radius')->default(5000); // raio em metros
            $table->boolean('is_online')->default(true);
            $table->timestamp('last_seen')->nullable();
            $table->string('avatar_type')->default('anonymous');
            $table->timestamps();
            
            // Índices para performance
            $table->index(['is_online', 'last_seen']);
            $table->index(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anonymous_users');
    }
};
