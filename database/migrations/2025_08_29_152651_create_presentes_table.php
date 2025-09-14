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
        Schema::create('presentes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('lista_id')->references('id')->on("listas");
            $table->string("nome");
            $table->longText('descricao');
            $table->float("preco", 2)->nullable();
            $table->longText('link')->nullable();
            $table->longText('image_url')->nullable();
            $table->longText('anotacoes')->nullable();
            $table->foreignUuid('cadastrado_por')->references('id')->on("users");
            $table->boolean('comprado')->default(false);
            $table->integer("avaliacao")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presentes');
    }
};
