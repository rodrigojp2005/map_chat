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
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('real_latitude', 10, 8)->nullable(); // localização real do usuário
            $table->decimal('real_longitude', 11, 8)->nullable(); // localização real do usuário
            $table->integer('privacy_radius')->default(50000); // raio de privacidade em metros (50km)
            $table->string('avatar_type')->default('default'); // tipo de avatar: man, woman, pet, etc.
            $table->boolean('is_online')->default(false);
            $table->timestamp('last_seen')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'latitude',
                'longitude', 
                'real_latitude',
                'real_longitude',
                'privacy_radius',
                'avatar_type',
                'is_online',
                'last_seen'
            ]);
        });
    }
};
