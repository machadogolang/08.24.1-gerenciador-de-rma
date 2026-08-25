<?php

namespace App\Rma\Infraestrutura\Migracao;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\TemaPreferido;
use App\Rma\Dominio\Origem;
use App\Rma\Dominio\Prioridade;
use App\Rma\Dominio\Solucao;
use App\Rma\Dominio\Status;
use App\Rma\Dominio\StatusDeLancamento;

/**
 * Único lugar do código onde um valor cru do legado (`'entrada'`, `-1`, `'14.6.1'`...)
 * é comparado por igualdade — `docs/arquitetura/INV-RMA-06-estrategia-reconstrucao.md`
 * §2-§5, §9, §11. Todo o resto do migrador chama estes métodos estáticos, nunca compara
 * string/int legado diretamente. Cada método devolve `null` quando o valor não bate em
 * nenhum case conhecido — quem chama decide o que fazer (anomalia/fallback), esta
 * classe nunca decide isso sozinha.
 */
final class TabelaDeTraducao
{
    /**
     * `INV-RMA-06` §2. Sem case para `'retornou'` (`Status` não tem esse case,
     * `LEG-RMA-016`) — PENDÊNCIA-2: se aparecer em dado real, quem chama registra a
     * anomalia específica, esta classe só devolve `null` como para qualquer outro valor
     * não reconhecido.
     */
    public static function status(?string $bruto): ?Status
    {
        return match ($bruto) {
            'entrada' => Status::Entrada,
            'recebido' => Status::Recebido,
            'encaminhado' => Status::Encaminhado,
            'concluido' => Status::Concluido,
            'arquivado' => Status::Arquivado,
            default => null,
        };
    }

    /**
     * `INV-RMA-06` §3.
     */
    public static function origem(?string $bruto): ?Origem
    {
        return match ($bruto) {
            'Unknown' => Origem::Unknown,
            'Loja' => Origem::Loja,
            'Casa' => Origem::Casa,
            'Cliente' => Origem::Cliente,
            'Licitação' => Origem::Licitacao,
            'Leilão' => Origem::Leilao,
            'Mercado Livre' => Origem::MercadoLivre,
            'Credito' => Origem::Credito,
            'AC' => Origem::Ac,
            'Rolo' => Origem::Rolo,
            default => null,
        };
    }

    /**
     * `INV-RMA-06` §4. Não trata `'urgente'` aqui — é uma conversão assistida
     * (condicional, não um mapeamento normal), decidida pelo importador chamador, que
     * registra a linha como conversão assistida no relatório em vez de tratar como
     * mapeamento silencioso.
     */
    public static function prioridade(?string $bruto): ?Prioridade
    {
        return match ($bruto) {
            'baixa' => Prioridade::Baixa,
            'media' => Prioridade::Media,
            'alta' => Prioridade::Alta,
            default => null,
        };
    }

    /**
     * `INV-RMA-06` §4 — identifica o resíduo `'urgente'` (RN-08, usado em código de
     * destaque, nunca no `<select>`). Não é um mapeamento normal (por isso não vive
     * dentro de `prioridade()`): quem chama decide gravar `Prioridade::Alta` como
     * conversão assistida, registrada explicitamente no relatório, nunca silenciosa.
     */
    public static function prioridadeEhResiduoUrgente(?string $bruto): bool
    {
        return $bruto === 'urgente';
    }

    /**
     * `INV-RMA-06` §5 — 16 valores fechados na Fase 4, comparação exata por igualdade de
     * string (os cases do enum já são literalmente os valores do `<select>` original).
     */
    public static function solucao(?string $bruto): ?Solucao
    {
        return match ($bruto) {
            'REPARO' => Solucao::Reparo,
            'TROCA DO PRODUTO' => Solucao::TrocaDoProduto,
            'TROCA DE PECA INTERNA' => Solucao::TrocaDePecaInterna,
            'PENDENTE CREDITO' => Solucao::PendenteCredito,
            'GERADO CREDITO' => Solucao::GeradoCredito,
            'DEVOLUCAO DO PRODUTO' => Solucao::DevolucaoDoProduto,
            'REEMBOLSO DO DINHEIRO' => Solucao::ReembolsoDoDinheiro,
            'ORCAMENTO PAGO' => Solucao::OrcamentoPago,
            'ORCAMENTO PENDENTE' => Solucao::OrcamentoPendente,
            'ORCAMENTO NEGADO' => Solucao::OrcamentoNegado,
            'REPARO PELO RMA' => Solucao::ReparoPeloRma,
            'CASO SOLUCIONADO' => Solucao::CasoSolucionado,
            'TESTADO TUDO OK' => Solucao::TestadoTudoOk,
            'PROCON' => Solucao::Procon,
            'DESCRITO NA OBSERVACAO' => Solucao::DescritoNaObservacao,
            'SEM GARANTIA' => Solucao::SemGarantia,
            default => null,
        };
    }

    /**
     * `INV-RMA-06` §9. String vazia e `NULL` são o mesmo caso ("sem estado ainda"), não
     * uma anomalia.
     */
    public static function statusDeLancamento(?string $bruto): ?StatusDeLancamento
    {
        return match ($bruto) {
            null, '' => null,
            'pendente' => StatusDeLancamento::Pendente,
            'nf_devolucao' => StatusDeLancamento::NfDevolucao,
            'sem_movimentacao' => StatusDeLancamento::SemMovimentacao,
            'nao' => StatusDeLancamento::Nao,
            'sim' => StatusDeLancamento::Sim,
            default => null,
        };
    }

    /**
     * `INV-RMA-06` §11 — domínio `-1/1/2/3/4`. Fail-safe: qualquer valor não reconhecido
     * devolve `null` (quem chama trata como anomalia e usa `Papel::Bloqueado`, nunca
     * concede acesso além do confirmado).
     */
    public static function papel(?int $bruto): ?Papel
    {
        return match ($bruto) {
            -1 => Papel::Bloqueado,
            1 => Papel::Leitura,
            2 => Papel::Operador,
            3 => Papel::Supervisor,
            4 => Papel::SuperAdministrador,
            default => null,
        };
    }

    /**
     * `INV-RMA-06` §11 — `''`/valor vazio cai no `default` (mesmo tratamento do fallback
     * `TemaPreferido::V1`, coerente com o default da coluna V3).
     */
    public static function temaPreferido(?string $bruto): TemaPreferido
    {
        return match ($bruto) {
            '15.8.1' => TemaPreferido::V2,
            default => TemaPreferido::V1,
        };
    }

    /**
     * `INV-RMA-06` §6 — não é enum (nenhuma regra de negócio ramifica sobre `empresa`),
     * mas ainda é uma comparação por igualdade de valor cru do legado, então vive aqui
     * pelo mesmo princípio. Só as 2 abreviações confirmadas são normalizadas; qualquer
     * outro valor (incluindo `NULL`) é devolvido sem alteração.
     */
    public static function empresa(?string $bruto): ?string
    {
        return match ($bruto) {
            'R A' => 'Registros Ativos',
            'T A' => 'Informatica',
            default => $bruto,
        };
    }
}
