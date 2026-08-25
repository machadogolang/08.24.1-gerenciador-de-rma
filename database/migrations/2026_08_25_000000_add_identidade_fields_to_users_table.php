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
        Schema::table('users', function (Blueprint $table) {
            $table->string('papel')->default('Leitura')->after('password');
            $table->string('tema_preferido')->default('v1')->after('papel');
            $table->text('anotacao')->nullable()->after('tema_preferido');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['papel', 'tema_preferido', 'anotacao']);
        });
    }
};
