<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lista_usuarios', function (Blueprint $table) {
            $table->uuid('Lista_UUID');
            $table->uuid('Usuario_UUID');

            $table->primary(['Lista_UUID', 'Usuario_UUID']);

            $table->foreign('Lista_UUID')->references('id')->on('listas')->onDelete('cascade');
            $table->foreign('Usuario_UUID')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lista_usuarios');
    }
};
