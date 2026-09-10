<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PAR15-AUD-004 - o Legacy `log` tem `sistema_operacional` e `app`, que a Fase 1
     * decidiu nao migrar (nenhuma tela consultava). Com a paridade de tela do V2
     * (`subp/logs_de_autenticacao.php`), os campos voltam como colunas HISTORICAS
     * nullable: nao alteram o modelo moderno (`user_agent`/`resultado`/`ip`) e nao
     * inventam valor para eventos novos.
     */
    public function up(): void
    {
        Schema::table('tentativas_de_acesso', function (Blueprint $table) {
            $table->string('sistema_operacional_legado')->nullable()->after('user_agent');
            $table->string('app_legado')->nullable()->after('sistema_operacional_legado');
        });
    }

    public function down(): void
    {
        Schema::table('tentativas_de_acesso', function (Blueprint $table) {
            $table->dropColumn(['sistema_operacional_legado', 'app_legado']);
        });
    }
};
