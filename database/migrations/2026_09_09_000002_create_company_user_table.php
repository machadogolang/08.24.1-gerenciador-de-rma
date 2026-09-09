<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * EVO-SAAS-001 (S2) — vínculo User ↔ Company. `papel` guarda o papel do usuário
     * DENTRO daquela empresa (mesmo enum `App\Identidade\Dominio\Papel`); `users.papel`
     * continua existindo até a transição S9 consumir só o vínculo.
     */
    public function up(): void
    {
        Schema::create('company_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('papel')->default('Leitura');
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'user_id']);
            $table->index('user_id');
            $table->index('company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_user');
    }
};
