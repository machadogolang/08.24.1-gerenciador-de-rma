<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * EVO-SAAS-001 (S3.4) - `tenant_id` nullable nas tabelas tenant-scoped. Nullable
     * durante o backfill; NOT NULL/FK endurecidas só quando o código de escrita passar
     * a preencher tenant por construção (S5+), com prova de zero órfãos.
     */
    public function up(): void
    {
        $tabelas = [
            'clientes',
            'fabricantes',
            'fornecedores',
            'assistencias_tecnicas',
            'rmas',
            'modificacoes_de_rma',
        ];

        foreach ($tabelas as $tabela) {
            Schema::table($tabela, function (Blueprint $table) {
                $table->foreignId('tenant_id')
                    ->nullable()
                    ->constrained('companies')
                    ->nullOnDelete()
                    ->after('id');
                $table->index('tenant_id');
            });
        }
    }

    public function down(): void
    {
        $tabelas = [
            'clientes',
            'fabricantes',
            'fornecedores',
            'assistencias_tecnicas',
            'rmas',
            'modificacoes_de_rma',
        ];

        foreach ($tabelas as $tabela) {
            Schema::table($tabela, function (Blueprint $table) {
                $table->dropIndex(['tenant_id']);
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            });
        }
    }
};
