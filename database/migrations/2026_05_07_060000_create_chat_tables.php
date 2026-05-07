<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_canales', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['proyecto', 'departamento', 'direccion', 'privado']);
            $table->foreignId('contexto_id')->nullable();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->foreignId('creado_por_id')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('chat_canal_miembros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('canal_id')->constrained('chat_canales')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->string('rol_en_canal')->nullable();
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();
        });

        Schema::create('chat_mensajes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('canal_id')->constrained('chat_canales')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('parent_message_id')->nullable()->constrained('chat_mensajes');
            $table->text('contenido');
            $table->timestamp('edited_at')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamps();
        });

        Schema::create('chat_menciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mensaje_id')->constrained('chat_mensajes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamp('leido_at')->nullable();
            $table->timestamps();
        });

        Schema::create('chat_lecturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('canal_id')->constrained('chat_canales')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('ultimo_mensaje_leido_id')->nullable()->constrained('chat_mensajes');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_lecturas');
        Schema::dropIfExists('chat_menciones');
        Schema::dropIfExists('chat_mensajes');
        Schema::dropIfExists('chat_canal_miembros');
        Schema::dropIfExists('chat_canales');
    }
};
