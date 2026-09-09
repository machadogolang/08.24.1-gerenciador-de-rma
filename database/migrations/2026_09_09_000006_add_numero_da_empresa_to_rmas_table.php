<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * EVO-SAAS-001 (S10) - número operacional do RMA dentro da empresa (1..N por
     * tenant). O id técnico continua global e `numero_legado` continua preservado.
     */
    public function up(): void
    {
        Schema::table('rmas', function (Blueprint $table) {
            $table->unsignedBigInteger('numero_da_empresa')->nullable()->after('tenant_id');
            $table->unique(['tenant_id', 'numero_da_empresa']);
        });
    }

    public function down(): void
    {
        Schema::table('rmas', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'numero_da_empresa']);
            $table->dropColumn('numero_da_empresa');
        });
    }
};
