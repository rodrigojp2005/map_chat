<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mapchat_comment_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('mapchat_id')->constrained('mapchats')->onDelete('cascade');
            $table->unsignedInteger('unread_count')->default(0);
            $table->unsignedBigInteger('last_comentario_id')->nullable();
            $table->string('last_author_name')->nullable();
            $table->string('last_preview', 120)->nullable();
            $table->timestamps();
            $table->unique(['user_id','mapchat_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mapchat_comment_notifications');
    }
};
