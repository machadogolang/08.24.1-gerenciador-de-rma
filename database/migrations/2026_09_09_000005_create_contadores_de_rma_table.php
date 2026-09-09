<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * EVO-SAAS-001 (S10) - contador transacional por empresa. Nunca `MAX(numero)+1`;
     * o próximo número é reservado com lock em transação (ver ReservarNumeroDeRma).
     */
    public function up(): void
    {
        Schema::create('contadores_de_rma', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->unsignedBigInteger('proximo_numero')->default(1);
            $table->timestamps();

            $table->unique('company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contadores_de_rma');
    }
};
