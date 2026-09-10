<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PAR14-REL-RPEC-004/RCD-003/RMPE-002 - o Legacy guardava a "informacao adicional"
     * de cada relatorio em `relatorio.informacaoadicional` (chave = codigo do
     * relatorio). Nao existia equivalente moderno; a tabela e tenant-aware, indexada
     * por codigo, e NAO acopla a informacao ao RMA.
     */
    public function up(): void
    {
        Schema::create('relatorio_informacoes_adicionais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')
                ->nullable()
                ->constrained('companies')
                ->nullOnDelete()
                ->after('id');
            $table->string('codigo');
            $table->text('informacao_adicional')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
            $table->unique(['tenant_id', 'codigo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('relatorio_informacoes_adicionais');
    }
};
