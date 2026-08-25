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
        Schema::table('rmas', function (Blueprint $table) {
            $table->string('status')->default('Entrada')->after('id');
            $table->dateTime('recebido_em')->nullable()->after('status');
            $table->dateTime('encaminhado_em')->nullable()->after('recebido_em');
            $table->dateTime('concluido_em')->nullable()->after('encaminhado_em');
            $table->dateTime('arquivado_em')->nullable()->after('concluido_em');
            $table->string('protocolo')->nullable()->after('arquivado_em');
            $table->string('solucao')->nullable()->after('protocolo');
            $table->string('snretorno')->nullable()->after('solucao');
            $table->nullableMorphs('destinatario');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rmas', function (Blueprint $table) {
            $table->dropMorphs('destinatario');
            $table->dropColumn([
                'status',
                'recebido_em',
                'encaminhado_em',
                'concluido_em',
                'arquivado_em',
                'protocolo',
                'solucao',
                'snretorno',
            ]);
        });
    }
};
