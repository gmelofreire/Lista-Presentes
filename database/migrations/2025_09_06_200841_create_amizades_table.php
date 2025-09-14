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
        Schema::create('amizades', function (Blueprint $table) {
            $table->uuid('usuario_id');
            $table->uuid('amigo_id');
            $table->string('status');

            $table->primary(['usuario_id', 'amigo_id']);
            $table->unique(['usuario_id', 'amigo_id']);

            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('amigo_id')->references('id')->on('users')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('amizades');
    }
};
