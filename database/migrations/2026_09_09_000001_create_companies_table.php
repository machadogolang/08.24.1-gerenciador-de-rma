<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * EVO-SAAS-001 (S2) - empresas/tenants da plataforma. Banco compartilhado com
     * `tenant_id` (INV-RMA-07 §5, Modelo A). `nome` é a única chave natural exigida
     * nesta fase; documento entra nullable para não travar hardening futuro.
     */
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->unique();
            $table->string('documento')->nullable();
            $table->boolean('ativa')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
