<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * EVO-SAAS-001 (S3.6) - hardening após auditoria: zero `tenant_id` NULL nas seis
     * tabelas tenant-scoped. Código de escrita (Observer/factory/listener/migrador) já
     * preenche o tenant antes desta migration.
     */
    public function up(): void
    {
        foreach ([
            'clientes',
            'fabricantes',
            'fornecedores',
            'assistencias_tecnicas',
            'rmas',
            'modificacoes_de_rma',
        ] as $tabela) {
            Schema::table($tabela, function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
            });

            Schema::table($tabela, function (Blueprint $table) {
                $table->unsignedBigInteger('tenant_id')->nullable(false)->change();
                // FK restritiva: empresa de tenant não pode ser removida se houver
                // linhas operacionais (coerente com a imutabilidade do domínio).
                $table->foreign('tenant_id')->references('id')->on('companies')->restrictOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach ([
            'clientes',
            'fabricantes',
            'fornecedores',
            'assistencias_tecnicas',
            'rmas',
            'modificacoes_de_rma',
        ] as $tabela) {
            Schema::table($tabela, function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
            });

            Schema::table($tabela, function (Blueprint $table) {
                $table->unsignedBigInteger('tenant_id')->nullable()->change();
                $table->foreign('tenant_id')->references('id')->on('companies')->nullOnDelete();
            });
        }
    }
};
