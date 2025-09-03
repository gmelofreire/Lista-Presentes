<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lista_usuarios', function (Blueprint $table) {
            $table->uuid('lista_id');
            $table->uuid('usuario_id');

            $table->primary(['lista_id', 'usuario_id']);

            $table->foreign('lista_id')->references('id')->on('listas')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lista_usuarios');
    }
};
