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
        Schema::table('grupos', function (Blueprint $table) {
            $table->string('token_convite', 64)->nullable()->unique()->after('image_url');
            $table->string('banner_url')->nullable()->after('token_convite');
            $table->timestamp('token_expira_em')->nullable()->after('banner_url');
        });

        Schema::table('grupo_usuario', function (Blueprint $table) {
            $table->string('role', 20)->default('visualizador')->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grupo_usuario', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        Schema::table('grupos', function (Blueprint $table) {
            $table->dropColumn(['token_convite', 'banner_url', 'token_expira_em']);
        });
    }
};
