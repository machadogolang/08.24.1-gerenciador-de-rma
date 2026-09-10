<?php

namespace App\Rma\Aplicacao;

use App\Models\Fabricante;
use App\Models\Fornecedor;
use App\Parceiros\Aplicacao\EncontrarOuCriarCliente;
use App\Rma\Dominio\Eventos\RmaEditado;
use App\Rma\Dominio\Prioridade;
use App\Rma\Dominio\RepositorioDeRmas;
use App\Rma\Dominio\Rma;
use App\Rma\Dominio\StatusDeLancamento;
use DateTimeImmutable;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

/**
 * LEG-RMA-010 - ajuste da revisão (não tinha fase dona no plano original). Mesmas
 * normalizações RN-13/RN-14 da criação, reaplicadas a cada edição.
 *
 * Fase 7: dispara RmaEditado ao final, lido via Auth::user() - mesma justificativa de
 * CriarRma.
 *
 * PAR-DET-V1-EDIT-01 (2026-09-09): o detalhe V1 do Legacy edita em linha o boletim
 * inteiro. Este caso de uso agora aceita, além do núcleo, os campos fiscais e de
 * logística que o detalhe grava (nfcompra/nfvenda/chaves, nfremessa/nfretorno/chaves,
 * rastreios, e-mails/fone, valor, snretorno, marcarestoque, etc). Campos ausentes da
 * requisição continuam preservados - os formulários antigos de edit (V1/V2) enviam
 * apenas o núcleo e não podem zerar o restante.
 */
final class EditarRma
{
    public function __construct(
        private readonly RepositorioDeRmas $repositorio,
        private readonly EncontrarOuCriarCliente $encontrarOuCriarCliente,
    ) {}

    /**
     * @param  array<string, mixed>  $dados
     */
    public function editar(int $id, array $dados): Rma
    {
        $existente = $this->repositorio->buscarPorId($id);

        if ($existente === null) {
            throw new RuntimeException("Rma {$id} não encontrado.");
        }

        $cliente = array_key_exists('cliente_nome', $dados) && filled($dados['cliente_nome'])
            ? $this->encontrarOuCriarCliente->encontrarOuCriar($dados['cliente_nome'])
            : null;

        $fabricante = $this->resolverParceiro(Fabricante::class, 'fabricante', $dados);
        $fornecedor = $this->resolverParceiro(Fornecedor::class, 'fornecedor', $dados);

        $alteracoes = [];

        foreach ([
            'descricao' => 'descricao',
            'modelo' => 'modelo',
            'sn' => 'sn',
            'os' => 'os',
            'origem' => 'origem',
            'empresa' => 'empresa',
            'defeito' => 'defeito',
            'observacao' => 'observacao',
            'pn' => 'pn',
            'snid' => 'snid',
            'protocolo' => 'protocolo',
            'snretorno' => 'snretorno',
            'nfcompra' => 'nfcompra',
            'nfcompra_chave' => 'nfcompraChave',
            'nfvenda' => 'nfvenda',
            'nfvenda_chave' => 'nfvendaChave',
            'nf_entrada_cliente_legado' => 'nfEntradaClienteLegado',
            'nf_retorno_cliente_legado' => 'nfRetornoClienteLegado',
            'nf_devolucao_de_venda' => 'nfDevolucaoDeVenda',
            'nf_remessa' => 'nfRemessa',
            'nf_remessa_emissao' => 'nfRemessaEmissao',
            'nf_remessa_chave' => 'nfRemessaChave',
            'nf_retorno_numero' => 'nfRetornoNumero',
            'nf_retorno_emissao' => 'nfRetornoEmissao',
            'nf_retorno_chave' => 'nfRetornoChave',
            'rastreio_ida' => 'rastreioIda',
            'rastreio_retorno' => 'rastreioRetorno',
            'cliente_email_legado' => 'clienteEmailLegado',
            'destinatario_email_legado' => 'destinatarioEmailLegado',
            'destinatario_fone_legado' => 'destinatarioFoneLegado',
            'destinatario_nome_legado' => 'destinatarioNomeLegado',
        ] as $campo => $propriedade) {
            if (array_key_exists($campo, $dados)) {
                $alteracoes[$propriedade] = $dados[$campo];
            }
        }

        if (array_key_exists('nfcompra_emissao', $dados)) {
            $alteracoes['nfcompraEmissao'] = filled($dados['nfcompra_emissao'])
                ? new DateTimeImmutable($dados['nfcompra_emissao'])
                : null;
        }

        if (array_key_exists('nfvenda_emissao', $dados)) {
            $alteracoes['nfvendaEmissao'] = filled($dados['nfvenda_emissao'])
                ? new DateTimeImmutable($dados['nfvenda_emissao'])
                : null;
        }

        if (array_key_exists('valor', $dados)) {
            $valor = trim((string) $dados['valor']);
            $alteracoes['valor'] = $valor === '' ? null : (float) str_replace(',', '.', $valor);
        }

        if (array_key_exists('marcarestoque', $dados)) {
            // PAR-V2-DETAIL-02 - select Sim/Nao envia '1'/'0'; o cast booleano do
            // PHP trataria '0' como true, entao normalizamos explicitamente.
            $alteracoes['marcarestoque'] = in_array($dados['marcarestoque'], [true, 1, '1', 'on'], true);
        }

        if (array_key_exists('credito_disponivel', $dados)) {
            // PAR-DET-V1-STOCK-01/PAR-V2-DETAIL-02 - flag de leitura/gravacao do
            // detalhe (mesma normalizacao do estoque para select Sim/Nao).
            $alteracoes['creditoDisponivel'] = in_array($dados['credito_disponivel'], [true, 1, '1', 'on'], true);
        }

        if (array_key_exists('prioridade', $dados)) {
            $alteracoes['prioridade'] = match (mb_strtolower(trim((string) $dados['prioridade']))) {
                'baixa' => Prioridade::Baixa,
                'media', 'normal' => Prioridade::Media,
                'alta' => Prioridade::Alta,
                default => null,
            };
        }

        if (array_key_exists('lancadoretorno', $dados)) {
            $valor = trim((string) $dados['lancadoretorno']);
            $alteracoes['lancadoretorno'] = $valor === '' ? null : StatusDeLancamento::tryFrom($valor);
        }

        if ($fabricante !== null) {
            $alteracoes['fabricanteId'] = $fabricante->id;
        } elseif (array_key_exists('fabricante_id', $dados) || array_key_exists('fabricante_nome', $dados)) {
            $alteracoes['fabricanteId'] = null;
        }

        if ($fornecedor !== null) {
            $alteracoes['fornecedorId'] = $fornecedor->id;
        } elseif (array_key_exists('fornecedor_id', $dados) || array_key_exists('fornecedor_nome', $dados)) {
            $alteracoes['fornecedorId'] = null;
        }

        if ($cliente !== null) {
            $alteracoes['clienteId'] = $cliente->id;
        } elseif (array_key_exists('cliente_nome', $dados)) {
            $alteracoes['clienteId'] = null;
        }

        if (array_key_exists('destinatario_tipo', $dados)) {
            $valorDestinatario = (string) $dados['destinatario_tipo'];

            if ($valorDestinatario === '') {
                $alteracoes['destinatarioType'] = null;
                $alteracoes['destinatarioId'] = null;
            } else {
                // UX-003/P7 - revalida tipo, existencia e tenant (antes o id era gravado
                // cru e o slug virava uma classe inexistente).
                [$tipoDestinatario, $idDestinatario] = $this->opcoesDeDestinatario->resolver($valorDestinatario);
                $alteracoes['destinatarioType'] = $tipoDestinatario;
                $alteracoes['destinatarioId'] = $idDestinatario;
            }
        }

        $rma = $existente->comAlteracoes($alteracoes);

        $rmaNormalizado = $rma->comNormalizacaoDeGravacao(
            $fabricante?->nome,
            $fornecedor?->nome,
            $cliente?->nome,
            $rma->empresa,
        );

        $atualizado = $this->repositorio->atualizar($rmaNormalizado);

        if (Auth::user() !== null) {
            RmaEditado::dispatch(Auth::user(), $atualizado);
        }

        return $atualizado;
    }

    /**
     * Resolve um parceiro pelo id existente (formulários modernos) ou pelo nome
     * (detalhe V1 em linha, que imita o campo texto do Legacy).
     *
     * @param  class-string  $modelo
     * @param  array<string, mixed>  $dados
     */
    private function resolverParceiro(string $modelo, string $prefixo, array $dados): ?object
    {
        if (array_key_exists($prefixo.'_id', $dados) && filled($dados[$prefixo.'_id'])) {
            return $modelo::query()->find($dados[$prefixo.'_id']);
        }

        if (array_key_exists($prefixo.'_nome', $dados) && filled($dados[$prefixo.'_nome'])) {
            return $modelo::query()->where('nome', $dados[$prefixo.'_nome'])->first();
        }

        return null;
    }
}
