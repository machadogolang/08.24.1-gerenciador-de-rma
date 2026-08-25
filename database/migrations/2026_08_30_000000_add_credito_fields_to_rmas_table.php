<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `credito_disponivel` — flag manual gravada por `MarcarCreditoDisponivel`
     * (`LEG-RMA-036`). Sem transição automática a partir de `solucao=GeradoCredito`;
     * o legado também não automatiza (controle manual em duas camadas independentes).
     */
    public function up(): void
    {
        Schema::table('rmas', function (Blueprint $table) {
            $table->boolean('credito_disponivel')->default(false)->after('valor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rmas', function (Blueprint $table) {
            $table->dropColumn('credito_disponivel');
        });
    }
};
