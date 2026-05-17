<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_canal_miembros', function (Blueprint $table) {
            $table->unique(['canal_id', 'user_id'], 'chat_miembros_canal_user_unique');
        });

        Schema::table('chat_lecturas', function (Blueprint $table) {
            $table->unique(['canal_id', 'user_id'], 'chat_lecturas_canal_user_unique');
        });

        Schema::table('chat_mensajes', function (Blueprint $table) {
            $table->index(['canal_id', 'created_at'], 'chat_mensajes_canal_created_index');
            $table->index('user_id', 'chat_mensajes_user_index');
        });

        Schema::table('chat_menciones', function (Blueprint $table) {
            $table->index(['user_id', 'leido_at'], 'chat_menciones_user_leido_index');
        });
    }

    public function down(): void
    {
        Schema::table('chat_canal_miembros', function (Blueprint $table) {
            $table->dropUnique('chat_miembros_canal_user_unique');
        });

        Schema::table('chat_lecturas', function (Blueprint $table) {
            $table->dropUnique('chat_lecturas_canal_user_unique');
        });

        Schema::table('chat_mensajes', function (Blueprint $table) {
            $table->dropIndex('chat_mensajes_canal_created_index');
            $table->dropIndex('chat_mensajes_user_index');
        });

        Schema::table('chat_menciones', function (Blueprint $table) {
            $table->dropIndex('chat_menciones_user_leido_index');
        });
    }
};