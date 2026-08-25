<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase 9 (`INV-RMA-06` §1) — `numero_legado` grava `bd.numero` (PK do legado, int
     * gerado em PHP, sem FK real). Chave de idempotência do importador de RMA:
     * `Rma::where('numero_legado', $numero)->exists()` decide se a linha já foi migrada.
     *
     * Desvio de nomenclatura do `design.md`/`tasks.md`: a data original desta migration
     * (`2026_09_01_000000`) já estava ocupada por
     * `create_modificacoes_de_rma_table.php` (Fase 7, commitada antes desta fase
     * começar a ser codificada) — deslocada para `2026_09_02_000000` para preservar
     * ordem cronológica sem colidir.
     */
    public function up(): void
    {
        Schema::table('rmas', function (Blueprint $table) {
            $table->unsignedInteger('numero_legado')->nullable()->unique()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rmas', function (Blueprint $table) {
            $table->dropColumn('numero_legado');
        });
    }
};
