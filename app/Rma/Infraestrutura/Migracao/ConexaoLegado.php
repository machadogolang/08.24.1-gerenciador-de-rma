<?php

namespace App\Rma\Infraestrutura\Migracao;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\LazyCollection;

/**
 * Thin wrapper sobre `DB::connection('rma_legacy')` — um método por tabela legada,
 * devolvendo `LazyCollection` (evita carregar as ~56 colunas × N linhas de `bd` inteiras
 * na memória de uma vez, `cursor()` do query builder). Único ponto do migrador que sabe
 * o nome real das 8 tabelas legadas (`relatorio`, a 9ª, nunca é lida — PENDÊNCIA-3
 * resolvida por omissão, opção B, ver `TabelaDeTraducao`/`RelatorioDeReconciliacao`).
 *
 * Conexão configurada como só-leitura em `config/database.php` (`rma_legacy`) — esta
 * classe nunca grava, só expõe `LazyCollection`/`count()` de leitura.
 */
final class ConexaoLegado
{
    public function tabelaExiste(string $tabela): bool
    {
        return Schema::connection('rma_legacy')->hasTable($tabela);
    }

    public function usuario(): LazyCollection
    {
        return DB::connection('rma_legacy')->table('usuario')->orderBy('id_usuario')->cursor();
    }

    public function cliente(): LazyCollection
    {
        return DB::connection('rma_legacy')->table('cliente')->orderBy('nome')->cursor();
    }

    public function fabricante(): LazyCollection
    {
        return DB::connection('rma_legacy')->table('fabricante')->orderBy('nome')->cursor();
    }

    public function fornecedor(): LazyCollection
    {
        return DB::connection('rma_legacy')->table('fornecedor')->orderBy('nome')->cursor();
    }

    public function assistenciaTecnica(): LazyCollection
    {
        return DB::connection('rma_legacy')->table('assistencia_tecnica')->orderBy('nome')->cursor();
    }

    public function bd(): LazyCollection
    {
        return DB::connection('rma_legacy')->table('bd')->orderBy('numero')->cursor();
    }

    public function log(): LazyCollection
    {
        return DB::connection('rma_legacy')->table('log')->orderBy('id_log')->cursor();
    }

    public function modificacao(): LazyCollection
    {
        return DB::connection('rma_legacy')->table('modificacao')->orderBy('id')->cursor();
    }

    public function contar(string $tabela): int
    {
        return (int) DB::connection('rma_legacy')->table($tabela)->count();
    }
}
