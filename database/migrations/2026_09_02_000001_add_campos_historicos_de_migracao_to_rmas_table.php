<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase 9 (`INV-RMA-06` §1.2, §5, §7, §10) — colunas preservadas por completude, sem
     * regra de negócio dona (nenhuma `LEG-RMA-NNN`/RN as lê). Todas `nullable`, sem cast
     * especial (strings/datas cruas) — a Fase 9 é a única fase autorizada a acrescentar
     * coluna "porque o dado existe".
     */
    public function up(): void
    {
        Schema::table('rmas', function (Blueprint $table) {
            // Bloqueador técnico descoberto ao codificar `ImportarRmas`: `status`
            // (Fase 3) não é `nullable`, mas `INV-RMA-06` §2 exige "RMA importado sem
            // status resolvido" para valor fora do domínio (anomalia) — sem alterar a
            // coluna, o import da linha falharia por violação de NOT NULL. Mesma
            // categoria de ajuste já feito para `rmas.valor` na Fase 5 (bloqueador
            // técnico, não decisão de produto). Mantém o `default('Entrada')` (Fase 3,
            // `RmaFactory`/`CriarRma` continuam sem passar `status` explicitamente em
            // alguns caminhos) — só deixa de ser `NOT NULL`.
            $table->string('status')->nullable()->default('Entrada')->change();

            // §1.2 — sem uso conhecido, preservados por completude.
            $table->string('nf_devolucao_de_venda')->nullable();
            $table->string('nf_entrada_cliente_legado')->nullable();
            $table->string('nf_retorno_cliente_legado')->nullable();
            $table->string('nf_remessa')->nullable();
            $table->string('nf_remessa_emissao')->nullable();
            $table->string('nf_remessa_chave')->nullable();
            // `nfretorno` renomeado para não colidir com `snretorno` (S/N de retorno,
            // Fase 4) — conceitos diferentes (NF de devolução vs. número de série).
            $table->string('nf_retorno_numero')->nullable();
            $table->string('nf_retorno_emissao')->nullable();
            $table->string('nf_retorno_chave')->nullable();
            $table->string('pn')->nullable();
            $table->string('snid')->nullable();
            $table->string('rastreio_ida')->nullable();
            $table->string('rastreio_retorno')->nullable();
            // Snapshot histórico por RMA — nunca grava no cadastro do parceiro
            // resolvido (`cliente_email`/`destinatario_email`/`destinatario_fone`
            // digitados naquele RMA específico podem divergir do cadastro atual).
            $table->string('cliente_email_legado')->nullable();
            $table->string('destinatario_email_legado')->nullable();
            $table->string('destinatario_fone_legado')->nullable();
            $table->string('descricao_final_legado')->nullable();

            // §5 — valor bruto quando `solucao` não bate em nenhum dos 16 valores
            // fechados da Fase 4 (typo histórico, valor pré-consolidação do
            // `<select>`, ou string vazia).
            $table->string('solucao_legado_bruto')->nullable();

            // §7 — nome bruto de `bd.destinatario` quando a cascata
            // assistência→fornecedor→fabricante não resolve nenhum vínculo.
            $table->string('destinatario_nome_legado')->nullable();

            // §10 — `bd.usuario` (e-mail de quem operou), sempre preservado; `operador_id`
            // só é preenchido quando o e-mail bate (soft match, case-insensitive) contra
            // um `User` já migrado.
            $table->string('operador_email_legado')->nullable();
            $table->foreignId('operador_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rmas', function (Blueprint $table) {
            $table->string('status')->default('Entrada')->nullable(false)->change();
            $table->dropForeign(['operador_id']);
            $table->dropColumn([
                'nf_devolucao_de_venda',
                'nf_entrada_cliente_legado',
                'nf_retorno_cliente_legado',
                'nf_remessa',
                'nf_remessa_emissao',
                'nf_remessa_chave',
                'nf_retorno_numero',
                'nf_retorno_emissao',
                'nf_retorno_chave',
                'pn',
                'snid',
                'rastreio_ida',
                'rastreio_retorno',
                'cliente_email_legado',
                'destinatario_email_legado',
                'destinatario_fone_legado',
                'descricao_final_legado',
                'solucao_legado_bruto',
                'destinatario_nome_legado',
                'operador_email_legado',
                'operador_id',
            ]);
        });
    }
};
