<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dispositivos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('token', 512)->unique();
            $table->string('plataforma', 20);
            $table->string('nome', 100)->nullable();
            $table->timestamp('ultimo_uso_em')->nullable();
            $table->timestamps();
            $table->index('user_id');
        });

        Schema::table('notificacoes', function (Blueprint $table) {
            // Garante que o push de uma notificação sai uma única vez mesmo com retries da fila.
            $table->timestamp('push_enviado_em')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('notificacoes', fn (Blueprint $t) => $t->dropColumn('push_enviado_em'));
        Schema::dropIfExists('dispositivos');
    }
};
