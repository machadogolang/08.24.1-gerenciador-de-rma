<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PAR15-PART-DATA-001 - Adiciona coluna rgie nas tabelas de parceiros para
     * preservar dados históricos de RG/IE do legado 15.8.1 (subp/ver_*.php).
     */
    public function up(): void
    {
        foreach (['clientes', 'fornecedores', 'fabricantes', 'assistencias_tecnicas'] as $tabela) {
            Schema::table($tabela, function (Blueprint $table) {
                $table->string('rgie', 50)->nullable()->after('cpf_cnpj');
            });
        }
    }

    public function down(): void
    {
        foreach (['clientes', 'fornecedores', 'fabricantes', 'assistencias_tecnicas'] as $tabela) {
            Schema::table($tabela, function (Blueprint $table) {
                $table->dropColumn('rgie');
            });
        }
    }
};
