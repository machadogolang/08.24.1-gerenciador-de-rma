<?php

namespace Tests\Feature\Migracao\Suporte;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Reproduz, para teste, as 8 tabelas de `rma_legacy` realmente lidas pelo migrador
 * (`relatorio`, a 9ª, nunca é lida — decisão B por omissão, PENDÊNCIA-3 de
 * `INV-RMA-06`), com fixture pequena e conhecida — não o dump de produção inteiro, não
 * depende do container Legacy real estar de pé (`design.md`).
 *
 * A conexão `rma_legacy` de teste (`phpunit.xml`) aponta para um banco físico separado
 * (`testing_legacy`) no MESMO servidor MySQL do container Sail — criado aqui sob
 * demanda, nunca tocado pelo `RefreshDatabase`/`migrate:fresh` do banco `testing`
 * padrão.
 */
trait ComBancoLegadoDeTeste
{
    protected function criarEsquemaLegado(): void
    {
        // `CREATE DATABASE` é DDL — se rodasse pela conexão padrão (`mysql`), o commit
        // implícito do MySQL quebraria os SAVEPOINTs que `RefreshDatabase` já abriu
        // nela. Usa um PDO cru, fora de qualquer conexão gerenciada pelo Laravel, só
        // para garantir que o banco físico de teste (`testing_legacy`) exista.
        $config = config('database.connections.rma_legacy');
        $pdo = new \PDO(
            "mysql:host={$config['host']};port={$config['port']}",
            $config['username'],
            $config['password']
        );
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['database']}`");
        $pdo = null;

        DB::purge('rma_legacy');

        $conexao = Schema::connection('rma_legacy');

        foreach (['bd', 'cliente', 'fabricante', 'fornecedor', 'assistencia_tecnica', 'usuario', 'log', 'modificacao'] as $tabela) {
            $conexao->dropIfExists($tabela);
        }

        $conexao->create('usuario', function ($table) {
            $table->increments('id_usuario');
            $table->string('nome', 100);
            $table->string('email', 100)->unique();
            $table->string('Key1461', 100)->nullable();
            $table->string('Key1581', 100)->nullable();
            $table->text('anotacao');
            $table->integer('permissao');
            $table->string('app', 11);
            $table->date('data_de_cadastro')->nullable();
            $table->integer('quantidade_login')->nullable();
            $table->dateTime('ultimo_login')->nullable();
        });

        $conexao->create('cliente', function ($table) {
            $table->increments('id');
            $table->string('nome', 50);
            $table->string('representante', 100)->nullable();
            $table->string('rgie', 50)->nullable();
            $table->string('cpfcnpj', 50)->nullable();
            $table->string('email', 50)->nullable();
            $table->string('fone', 50)->nullable();
            $table->string('fone2', 50)->nullable();
            $table->string('cep', 50)->nullable();
            $table->string('logradouro', 100)->nullable();
            $table->string('numero', 20)->nullable();
            $table->string('complemento', 100)->nullable();
            $table->string('bairro', 100)->nullable();
            $table->string('cidade', 100)->nullable();
            $table->string('uf', 2)->nullable();
            $table->text('observacaoSGV')->nullable();
            $table->text('observacaoFR')->nullable();
            $table->string('maior_interesse', 100)->nullable();
            $table->string('compras', 100)->nullable();
            $table->date('data_de_cadastro')->nullable();
        });

        foreach (['fabricante', 'fornecedor', 'assistencia_tecnica'] as $tabela) {
            $conexao->create($tabela, function ($table) use ($tabela) {
                $table->increments('id');
                $table->string('nome', 50);
                $table->string('representante', 100)->nullable();
                $table->string('rgie', 50)->nullable();
                $table->string('cpfcnpj', 50)->nullable();
                $table->string('email', 50)->nullable();
                $table->string('email2', 50)->nullable();
                $table->string('fone', 50)->nullable();
                $table->string('fone2', 50)->nullable();
                $table->string('cep', 50)->nullable();
                $table->string('logradouro', 100)->nullable();
                $table->string('numero', 20)->nullable();
                $table->string('complemento', 100)->nullable();
                $table->string('bairro', 100)->nullable();
                $table->string('cidade', 100)->nullable();
                $table->string('uf', 2)->nullable();
                $table->string('www', 100)->nullable();
                $table->string('frete', 50)->nullable();
                $table->string('cfop', 50)->nullable();

                if ($tabela === 'fornecedor') {
                    $table->text('observacaoSGV')->nullable();
                    $table->text('observacaoFR')->nullable();
                } else {
                    $table->text('observacao')->nullable();
                }

                $table->text('politicadegarantia')->nullable();
                $table->date('data_de_cadastro')->nullable();
            });
        }

        $conexao->create('bd', function ($table) {
            $table->integer('numero')->primary();
            $table->string('descricao', 50);
            $table->string('fabricante', 50)->nullable();
            $table->string('modelo', 50)->nullable();
            $table->string('os', 11)->nullable();
            $table->string('status', 50)->nullable();
            $table->string('origem', 50)->nullable();
            $table->string('sn', 50)->nullable();
            $table->string('prioridade', 150)->nullable();
            $table->string('defeito', 150)->nullable();
            $table->string('nfcompra', 11)->nullable();
            $table->dateTime('entrada')->nullable();
            $table->string('recebido', 50)->nullable();
            $table->string('encaminhado', 50)->nullable();
            $table->string('concluido', 50)->nullable();
            $table->string('nfcompra_emissao', 50)->nullable();
            $table->string('nfvenda', 11)->nullable();
            $table->string('nfvenda_emissao', 50)->nullable();
            $table->string('nfcompra_chave', 100)->nullable();
            $table->string('nfvenda_chave', 100)->nullable();
            $table->text('observacao')->nullable();
            $table->string('solucao', 50)->nullable();
            $table->integer('marcarestoque')->nullable();
            $table->integer('creditodisponivel')->default(0);
            $table->string('empresa', 50)->nullable();
            $table->string('cliente', 50)->nullable();
            $table->string('destinatario', 50)->nullable();
            $table->string('protocolo', 50)->nullable();
            $table->string('arquivado', 50)->nullable();
            $table->string('fornecedor', 50)->nullable();
            $table->float('valor')->nullable();
            $table->string('snretorno', 50)->nullable();
            $table->string('lancadoretorno', 11)->nullable();
            $table->dateTime('dtaalt')->nullable();
            // §1.2
            $table->string('nfdevolucaodevenda', 11)->nullable();
            $table->string('nfentrada_cli', 100)->nullable();
            $table->string('nfretorno_cli', 100)->nullable();
            $table->string('nfremessa', 50)->nullable();
            $table->string('nfremessa_emissao', 50)->nullable();
            $table->string('nfremessa_chave', 50)->nullable();
            $table->string('nfretorno', 50)->nullable();
            $table->string('nfretorno_emissao', 50)->nullable();
            $table->string('nfretorno_chave', 50)->nullable();
            $table->string('pn', 50)->nullable();
            $table->string('snid', 50)->nullable();
            $table->string('rastreio_ida', 50)->nullable();
            $table->string('rastreio_retorno', 50)->nullable();
            $table->string('cliente_email', 50)->nullable();
            $table->string('destinatario_email', 150)->nullable();
            $table->string('destinatario_fone', 50)->nullable();
            $table->string('descricao_final', 50)->nullable();
            $table->string('usuario', 50)->nullable();
            // §1.3 — não migrados, lidos só para cross-check/anomalia
            $table->string('prazo', 50)->nullable();
            $table->integer('ano')->nullable();
            $table->dateTime('dtains')->nullable();
            $table->date('retornou')->nullable();
        });

        $conexao->create('log', function ($table) {
            $table->increments('id_log');
            $table->string('email', 100)->nullable();
            $table->string('nome', 100)->nullable();
            $table->dateTime('data')->nullable();
            $table->string('sistema_operacional', 100)->nullable();
            $table->string('ip', 50)->nullable();
            $table->string('navegador', 150)->nullable();
            $table->string('retorno', 20)->nullable();
            $table->string('app', 11)->nullable();
        });

        $conexao->create('modificacao', function ($table) {
            $table->increments('id');
            $table->integer('numero')->nullable();
            $table->string('nome', 100)->nullable();
            $table->string('email', 100)->nullable();
            $table->dateTime('dta')->nullable();
            $table->string('descricao', 50)->nullable();
            $table->string('app', 11)->nullable();
            $table->string('so', 50)->nullable();
            $table->string('fabricante', 50)->nullable();
            $table->string('modelo', 50)->nullable();
            $table->string('sn', 50)->nullable();
            $table->string('ip', 50)->nullable();
            $table->string('navegador', 150)->nullable();
        });
    }

    protected function limparBancoLegado(): void
    {
        foreach (['modificacao', 'log', 'bd', 'assistencia_tecnica', 'fornecedor', 'fabricante', 'cliente', 'usuario'] as $tabela) {
            DB::connection('rma_legacy')->table($tabela)->truncate();
        }
    }
}
