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
        Schema::create('modificacoes_de_rma', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rma_id')->constrained('rmas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('acao');
            $table->string('ip')->nullable();
            $table->string('user_agent')->nullable();
            $table->json('estado_apos');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modificacoes_de_rma');
    }
};
