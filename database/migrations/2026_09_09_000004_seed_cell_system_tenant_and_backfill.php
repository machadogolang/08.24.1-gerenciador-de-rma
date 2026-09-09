<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * EVO-SAAS-001 (S3.1-S3.5) — cria o tenant `CellSystem` deterministicamente,
     * vincula usuários existentes preservando `users.papel` e faz backfill das tabelas
     * tenant-scoped atuais para esse tenant. Idempotente: pode rodar com banco vazio
     * ou já vinculado.
     */
    public function up(): void
    {
        DB::transaction(function (): void {
            $agora = now();

            $celula = DB::table('companies')
                ->where('nome', 'CellSystem')
                ->first();

            if ($celula === null) {
                $id = DB::table('companies')->insertGetId([
                    'nome' => 'CellSystem',
                    'documento' => null,
                    'ativa' => true,
                    'created_at' => $agora,
                    'updated_at' => $agora,
                ]);
            } else {
                $id = $celula->id;
            }

            // S3.2/S3.3 — usuário que ainda não tem vínculo nenhum ganha CellSystem com
            // o papel atual de `users.papel`; vínculos já existentes não são tocados.
            $usuariosSemVinculo = DB::table('users')
                ->leftJoin('company_user', 'company_user.user_id', '=', 'users.id')
                ->whereNull('company_user.id')
                ->select('users.id', 'users.papel')
                ->get();

            foreach ($usuariosSemVinculo as $usuario) {
                DB::table('company_user')->insertOrIgnore([
                    'company_id' => $id,
                    'user_id' => $usuario->id,
                    'papel' => $usuario->papel ?? 'Leitura',
                    'ativo' => true,
                    'created_at' => $agora,
                    'updated_at' => $agora,
                ]);
            }

            // S3.5 — backfill das linhas existentes para CellSystem (todas as linhas
            // atuais são da empresa semente nesta fase).
            foreach ([
                'clientes',
                'fabricantes',
                'fornecedores',
                'assistencias_tecnicas',
                'rmas',
                'modificacoes_de_rma',
            ] as $tabela) {
                DB::table($tabela)->whereNull('tenant_id')->update(['tenant_id' => $id]);
            }
        });
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            $celula = DB::table('companies')->where('nome', 'CellSystem')->first();
            if ($celula === null) {
                return;
            }

            // Remove somente vínculos criados por esta migration: usuário cujo único
            // vínculo atual é CellSystem.
            $idsCriados = DB::table('company_user')
                ->where('company_id', $celula->id)
                ->whereRaw('(SELECT COUNT(*) FROM company_user cu WHERE cu.user_id = company_user.user_id) = 1')
                ->pluck('user_id');

            DB::table('company_user')
                ->where('company_id', $celula->id)
                ->whereIn('user_id', $idsCriados)
                ->delete();

            if (! DB::table('company_user')->where('company_id', $celula->id)->exists()) {
                DB::table('companies')->where('id', $celula->id)->delete();
            }
        });
    }
};
