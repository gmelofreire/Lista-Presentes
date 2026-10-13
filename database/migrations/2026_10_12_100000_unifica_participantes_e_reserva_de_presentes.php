<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. lista_usuarios deixa de existir: qualquer vínculo que ainda não esteja em lista_participantes é copiado.
        if (Schema::hasTable('lista_usuarios')) {
            $agora = now();
            $existentes = DB::table('lista_participantes')->get(['lista_id', 'usuario_id'])
                ->map(fn ($p) => $p->lista_id.'|'.$p->usuario_id)->flip();

            $linhas = DB::table('lista_usuarios')
                ->join('listas', 'listas.id', '=', 'lista_usuarios.lista_id')
                ->get(['lista_usuarios.lista_id', 'lista_usuarios.usuario_id', 'listas.cadastrado_por_id']);

            foreach ($linhas as $l) {
                if (! isset($existentes[$l->lista_id.'|'.$l->usuario_id])) {
                    DB::table('lista_participantes')->insert([
                        'lista_id' => $l->lista_id,
                        'usuario_id' => $l->usuario_id,
                        'papel' => $l->usuario_id === $l->cadastrado_por_id ? 'admin' : 'visualizador',
                        'created_at' => $agora,
                        'updated_at' => $agora,
                    ]);
                }
            }

            Schema::drop('lista_usuarios');
        }

        // 2. Reserva de presente: guarda quem reservou (invisível para o dono da lista).
        Schema::table('presentes', function (Blueprint $table) {
            $table->foreignUuid('reservado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reservado_em')->nullable();
            $table->index('lista_id');
        });

        // Presentes já marcados como comprados ficam reservados pelo autor, para não perder o estado.
        DB::table('presentes')->where('comprado', true)->update([
            'reservado_por' => DB::raw('cadastrado_por'),
            'reservado_em' => now(),
        ]);

        // 3. Preço em decimal (float(2) perde precisão).
        Schema::table('presentes', function (Blueprint $table) {
            $table->decimal('preco', 12, 2)->nullable()->change();
        });

        // 4. Índices das consultas mais frequentes.
        Schema::table('listas', function (Blueprint $table) {
            $table->index('grupo_id');
            $table->index('cadastrado_por_id');
        });
        Schema::table('notificacoes', function (Blueprint $table) {
            $table->index(['user_id', 'lido']);
        });
        Schema::table('amizades', function (Blueprint $table) {
            $table->index('amigo_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('amizades', function (Blueprint $table) {
            $table->dropIndex(['amigo_id']);
            $table->dropIndex(['status']);
        });
        Schema::table('notificacoes', fn (Blueprint $t) => $t->dropIndex(['user_id', 'lido']));
        Schema::table('listas', function (Blueprint $table) {
            $table->dropIndex(['grupo_id']);
            $table->dropIndex(['cadastrado_por_id']);
        });
        Schema::table('presentes', function (Blueprint $table) {
            $table->dropForeign(['reservado_por']);
            $table->dropColumn(['reservado_por', 'reservado_em']);
            $table->dropIndex(['lista_id']);
        });
    }
};
