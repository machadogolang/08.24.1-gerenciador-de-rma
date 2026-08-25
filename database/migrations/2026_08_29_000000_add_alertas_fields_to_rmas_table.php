<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * `origem` já existe (Fase 3) — não recriada aqui, só ganha cast no domínio (ver
     * decisão registrada em `docs/produto/log-implementacao-v3.md`, Fase 5). Só os
     * blocos de NF `compra`/`venda` entram (usados por RN-02/05/06/09) —
     * `nfremessa`/`nfretorno` ficam para Fase 6/7 se alguma regra vier a precisar.
     *
     * `valor` — ajuste desta revisão do `design.md`: coluna usada por
     * `UrgenciaPorThreshold` (RN-12, `->where('valor', '>', 75.00)`), mas ausente do
     * schema original desta fase (divergência real, já registrada em `INV-RMA-06`).
     * Origem confirmada em `regras-negocio-rma-legado.md` RN-12: `15.8.1/banco.php:777`
     * (`right_urgente()`), campo monetário real do RMA (não calculado, não código de
     * domínio fechado — por isso `decimal`, não `string`).
     */
    public function up(): void
    {
        Schema::table('rmas', function (Blueprint $table) {
            $table->string('prioridade')->nullable()->after('snretorno');
            $table->boolean('marcarestoque')->default(true)->after('prioridade');
            $table->string('nfcompra')->nullable()->after('marcarestoque');
            $table->date('nfcompra_emissao')->nullable()->after('nfcompra');
            $table->string('nfcompra_chave')->nullable()->after('nfcompra_emissao');
            $table->string('nfvenda')->nullable()->after('nfcompra_chave');
            $table->date('nfvenda_emissao')->nullable()->after('nfvenda');
            $table->string('nfvenda_chave')->nullable()->after('nfvenda_emissao');
            $table->string('lancadoretorno')->nullable()->after('nfvenda_chave');
            $table->decimal('valor', 10, 2)->nullable()->after('lancadoretorno');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rmas', function (Blueprint $table) {
            $table->dropColumn([
                'prioridade',
                'marcarestoque',
                'nfcompra',
                'nfcompra_emissao',
                'nfcompra_chave',
                'nfvenda',
                'nfvenda_emissao',
                'nfvenda_chave',
                'lancadoretorno',
                'valor',
            ]);
        });
    }
};
