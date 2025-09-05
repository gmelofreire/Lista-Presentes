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
            $table->id();
            $table->foreignUuid("categoria_id")->references("id")->on("categorias");
            $table->foreignUuid("presente_id")->references("id")->on("presentes");
            $table->timestamps();
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
