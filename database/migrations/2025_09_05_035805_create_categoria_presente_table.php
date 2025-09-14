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
        Schema::create('categoria_presente', function (Blueprint $table) {
            $table->uuid("categoria_id");
            $table->uuid("presente_id");
            $table->timestamps();

            $table->primary(['categoria_id', 'presente_id']);

            $table->foreign('categoria_id')->references('id')->on('categorias')->onDelete('cascade');
            $table->foreign('presente_id')->references('id')->on('presentes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categoria_presente');
    }
};
