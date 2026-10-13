<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** O model e os controllers já usam estas colunas, mas nenhuma migration as criava. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categorias', function (Blueprint $table) {
            if (! Schema::hasColumn('categorias', 'icone')) {
                $table->string('icone')->nullable();
            }
            if (! Schema::hasColumn('categorias', 'parent_id')) {
                $table->uuid('parent_id')->nullable()->index();
            }
            if (! Schema::hasColumn('categorias', 'ordem')) {
                $table->unsignedInteger('ordem')->default(0);
            }
            if (! Schema::hasColumn('categorias', 'e_default')) {
                $table->boolean('e_default')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('categorias', function (Blueprint $table) {
            $table->dropColumn(['icone', 'parent_id', 'ordem', 'e_default']);
        });
    }
};
