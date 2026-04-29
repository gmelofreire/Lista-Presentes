<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('conta_ativa')->default(true)->after('email_verified_at');
            $table->timestamp('desativado_em')->nullable()->after('conta_ativa');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['conta_ativa', 'desativado_em']);
        });
    }
};
