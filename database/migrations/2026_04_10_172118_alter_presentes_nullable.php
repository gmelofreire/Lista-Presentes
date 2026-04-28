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
        Schema::table('presentes', function (Blueprint $table) {
            $table->text('descricao')->nullable()->change();
            $table->float('preco', 8, 2)->nullable()->change();
            $table->longText('link')->nullable()->change();
            $table->longText('image_url')->nullable()->change();
            $table->longText('anotacoes')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
