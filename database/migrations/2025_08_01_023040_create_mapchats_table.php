<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mapchats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nome');
            $table->integer('duracao');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->text('contexto');
            $table->enum('privacidade', ['publica', 'privada'])->default('publica');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mapchats');
    }
};
