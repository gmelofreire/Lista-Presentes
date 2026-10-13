<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lista_participantes')) {
            Schema::create('lista_participantes', function (Blueprint $table) {
                $table->id();
                $table->foreignUuid('lista_id')->constrained('listas')->cascadeOnDelete();
                $table->foreignUuid('usuario_id')->constrained('users')->cascadeOnDelete();
                $table->string('papel', 20)->default('visualizador');
                $table->timestamps();
                $table->unique(['lista_id', 'usuario_id']);
            });

            // Quem criou a lista é admin; demais vínculos antigos viram visualizador.
            $agora = now();
            $linhas = DB::table('lista_usuarios')
                ->join('listas', 'listas.id', '=', 'lista_usuarios.lista_id')
                ->get(['lista_usuarios.lista_id', 'lista_usuarios.usuario_id', 'listas.cadastrado_por_id']);

            foreach ($linhas as $linha) {
                DB::table('lista_participantes')->insert([
                    'lista_id' => $linha->lista_id,
                    'usuario_id' => $linha->usuario_id,
                    'papel' => $linha->usuario_id === $linha->cadastrado_por_id ? 'admin' : 'visualizador',
                    'created_at' => $agora,
                    'updated_at' => $agora,
                ]);
            }
        }

        if (! Schema::hasColumn('listas', 'token_convite')) {
            Schema::table('listas', function (Blueprint $table) {
                $table->string('token_convite', 64)->nullable()->unique();
                $table->timestamp('token_expira_em')->nullable();
            });
        }

        if (! Schema::hasColumn('grupos', 'visibilidade')) {
            Schema::table('grupos', function (Blueprint $table) {
                $table->string('visibilidade', 20)->default('publica');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lista_participantes');

        Schema::table('listas', function (Blueprint $table) {
            if (Schema::hasColumn('listas', 'token_convite')) {
                $table->dropColumn(['token_convite', 'token_expira_em']);
            }
        });
    }
};
