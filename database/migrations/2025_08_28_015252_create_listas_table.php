<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('listas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nome');
            $table->longText("descricao")->nullable();
            $table->foreignUuid("cadastrado_por_id")->references("id")->on("users");
            $table->enum("status", ["ativa", "inativa"])->default("ativa");
            $table->enum("visibilidade", ["privada", "publica"])->default("privada");
            $table->date("data_evento")->nullable();
            $table->longText("image_url")->nullable();
            $table->uuid('grupo_id')->nullable();
            $table->foreign('grupo_id')->references('id')->on('grupos')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listas');
    }
};
