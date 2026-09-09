@extends('temas.v3.layout')

@php
    $l = $legado ?? [];
    $fabricanteNome = $fabricante?->nome ?? '';
    $fornecedorNome = $fornecedor?->nome ?? '';
    $clienteNome = $cliente?->nome ?? '';
    $destinatarioNome = $l['destinatario_nome'] ?? '';
    $proximaAcao = match ($registro->status) {
        \App\Rma\Dominio\Status::Entrada => 'Receber',
        \App\Rma\Dominio\Status::Recebido => 'Encaminhar',
        \App\Rma\Dominio\Status::Encaminhado => 'Concluir',
        default => 'Nenhuma pendente',
    };
    $prioridadeClasse = match ($registro->prioridade) {
        \App\Rma\Dominio\Prioridade::Alta => 'prioridade-badge--alta',
        \App\Rma\Dominio\Prioridade::Media => 'prioridade-badge--media',
        default => 'prioridade-badge--baixa',
    };
    $statusClasse = 'status-badge--' . strtolower($registro->status->name);
@endphp

@section('conteudo')
    <div class="pagina detalhe-v3">
        <p>
            <a href="{{ route('v3.rmas.index') }}" class="botao botao--secundario">Voltar para RMAs</a>
            @if (($podeEditar ?? false) === true)
                <a href="{{ route('v3.rmas.edit', ['rma' => $registro->id]) }}" class="botao">Editar RMA</a>
            @endif
        </p>

        <header class="cartao detalhe-v3__cabecalho">
            <div class="detalhe-v3__identificacao">
                <p class="detalhe-v3__rotulo">RMA</p>
                <h1 class="detalhe-v3__numero">{{ $numeroExibicao }}</h1>
                <p class="detalhe-v3__descricao">{{ $registro->descricao }}</p>
            </div>
            <dl class="detalhe-v3__meta">
                <div>
                    <dt>Status</dt>
                    <dd><span class="status-badge {{ $statusClasse }}">{{ $registro->status->name }}</span></dd>
                </div>
                <div>
                    <dt>Prioridade</dt>
                    <dd><span class="prioridade-badge {{ $prioridadeClasse }}">{{ $registro->prioridade?->name ?? 'Baixa' }}</span></dd>
                </div>
                <div>
                    <dt>Proxima acao</dt>
                    <dd>{{ $proximaAcao }}</dd>
                </div>
            </dl>
        </header>

        <div class="detalhe-v3__secoes">

            <section class="cartao detalhe-v3__secao" aria-labelledby="sec-resumo">
                <h2 id="sec-resumo" class="detalhe-v3__titulo">Resumo</h2>
                <dl class="detalhe-v3__grade">
                    <div><dt>Status</dt><dd>{{ $registro->status->name }}</dd></div>
                    <div><dt>Prioridade</dt><dd>{{ $registro->prioridade?->name ?? 'Baixa' }}</dd></div>
                    <div><dt>Origem</dt><dd>{{ $registro->origem }}</dd></div>
                    <div><dt>Empresa</dt><dd>{{ $registro->empresa }}</dd></div>
                    <div><dt>Protocolo</dt><dd>{{ $registro->protocolo }}</dd></div>
                    <div><dt>Valor</dt><dd>{{ $registro->valor !== null ? number_format($registro->valor, 2, ',', '.') : '' }}</dd></div>
                </dl>
            </section>

            <section class="cartao detalhe-v3__secao" aria-labelledby="sec-produto">
                <h2 id="sec-produto" class="detalhe-v3__titulo">Produto</h2>
                <dl class="detalhe-v3__grade">
                    <div><dt>Descricao</dt><dd>{{ $registro->descricao }}</dd></div>
                    <div><dt>Modelo</dt><dd>{{ $registro->modelo }}</dd></div>
                    <div><dt>Fabricante</dt><dd>{{ $fabricanteNome }}</dd></div>
                    <div><dt>S/N</dt><dd>{{ $registro->sn }}</dd></div>
                    <div><dt>P/N</dt><dd>{{ $registro->pn }}</dd></div>
                    <div><dt>SNID</dt><dd>{{ $registro->snid }}</dd></div>
                    <div><dt>OS</dt><dd>{{ $registro->os }}</dd></div>
                </dl>
            </section>

            <section class="cartao detalhe-v3__secao" aria-labelledby="sec-parceiros">
                <h2 id="sec-parceiros" class="detalhe-v3__titulo">Parceiros e origem</h2>
                <dl class="detalhe-v3__grade">
                    <div><dt>Fabricante</dt><dd>{{ $fabricanteNome }}</dd></div>
                    <div><dt>Fornecedor</dt><dd>{{ $fornecedorNome }}</dd></div>
                    <div><dt>Cliente</dt><dd>{{ $clienteNome }}</dd></div>
                    <div><dt>Origem</dt><dd>{{ $registro->origem }}</dd></div>
                    <div><dt>Destinatario</dt><dd>{{ $destinatarioNome }}</dd></div>
                </dl>
            </section>

            <section class="cartao detalhe-v3__secao detalhe-v3__secao--larga" aria-labelledby="sec-fiscal">
                <h2 id="sec-fiscal" class="detalhe-v3__titulo">Fiscal</h2>
                <dl class="detalhe-v3__grade detalhe-v3__grade--fiscal">
                    <div><dt>NF de compra</dt><dd>{{ $registro->nfcompra }}</dd></div>
                    <div><dt>Data de emissao</dt><dd>{{ $registro->nfcompraEmissao?->format('d/m/Y') }}</dd></div>
                    <div><dt>DANFE compra</dt><dd class="mono">{{ $registro->nfcompraChave }}</dd></div>
                    <div><dt>NF de venda</dt><dd>{{ $registro->nfvenda }}</dd></div>
                    <div><dt>Data de emissao</dt><dd>{{ $registro->nfvendaEmissao?->format('d/m/Y') }}</dd></div>
                    <div><dt>DANFE venda</dt><dd class="mono">{{ $registro->nfvendaChave }}</dd></div>
                    <div><dt>NF remessa</dt><dd>{{ $l['nf_remessa'] ?? '' }}</dd></div>
                    <div><dt>Data</dt><dd>{{ $l['nf_remessa_emissao'] ?? '' }}</dd></div>
                    <div><dt>DANFE remessa</dt><dd class="mono">{{ $l['nf_remessa_chave'] ?? '' }}</dd></div>
                    <div><dt>NF retorno</dt><dd>{{ $l['nf_retorno_numero'] ?? '' }}</dd></div>
                    <div><dt>Data</dt><dd>{{ $l['nf_retorno_emissao'] ?? '' }}</dd></div>
                    <div><dt>DANFE retorno</dt><dd class="mono">{{ $l['nf_retorno_chave'] ?? '' }}</dd></div>
                    <div><dt>NF entrada cliente</dt><dd>{{ $l['nf_entrada_cliente_legado'] ?? '' }}</dd></div>
                    <div><dt>NF retorno cliente</dt><dd>{{ $l['nf_retorno_cliente_legado'] ?? '' }}</dd></div>
                    <div><dt>NF devolucao de venda</dt><dd>{{ $l['nf_devolucao_de_venda'] ?? '' }}</dd></div>
                </dl>
            </section>

            <section class="cartao detalhe-v3__secao" aria-labelledby="sec-logistica">
                <h2 id="sec-logistica" class="detalhe-v3__titulo">Destinatario e logistica</h2>
                <dl class="detalhe-v3__grade">
                    <div><dt>Destinatario</dt><dd>{{ $destinatarioNome }}</dd></div>
                    <div><dt>E-mail</dt><dd>{{ $l['destinatario_email_legado'] ?? '' }}</dd></div>
                    <div><dt>Fone</dt><dd>{{ $l['destinatario_fone_legado'] ?? '' }}</dd></div>
                    <div><dt>Rastreio ida</dt><dd>{{ $l['rastreio_ida'] ?? '' }}</dd></div>
                    <div><dt>Rastreio retorno</dt><dd>{{ $l['rastreio_retorno'] ?? '' }}</dd></div>
                </dl>
            </section>

            <section class="cartao detalhe-v3__secao" aria-labelledby="sec-solucao">
                <h2 id="sec-solucao" class="detalhe-v3__titulo">Solucao e credito</h2>
                <dl class="detalhe-v3__grade">
                    <div><dt>Solucao</dt><dd>{{ $registro->solucao?->value ?? '' }}</dd></div>
                    <div><dt>S/N retorno</dt><dd>{{ $registro->snretorno }}</dd></div>
                    <div><dt>Item do estoque</dt><dd>{{ $registro->marcarestoque ? 'Sim' : 'Nao' }}</dd></div>
                    <div><dt>Credito disponivel</dt><dd>{{ $registro->creditoDisponivel ? 'Sim' : 'Nao' }}</dd></div>
                    <div><dt>Defeito reclamado</dt><dd>{{ $registro->defeito }}</dd></div>
                    <div><dt>Observacao</dt><dd class="detalhe-v3__observacao">{{ $registro->observacao }}</dd></div>
                </dl>
            </section>

            <section class="cartao detalhe-v3__secao" aria-labelledby="sec-historico">
                <h2 id="sec-historico" class="detalhe-v3__titulo">Historico e auditoria</h2>
                <dl class="detalhe-v3__grade">
                    <div><dt>Entrada</dt><dd>{{ $registro->createdAt?->format('d/m/Y H:i') }}</dd></div>
                    <div><dt>Recebido</dt><dd>{{ $registro->recebidoEm?->format('d/m/Y H:i') }}</dd></div>
                    <div><dt>Encaminhado</dt><dd>{{ $registro->encaminhadoEm?->format('d/m/Y H:i') }}</dd></div>
                    <div><dt>Concluido</dt><dd>{{ $registro->concluidoEm?->format('d/m/Y H:i') }}</dd></div>
                    <div><dt>Arquivado</dt><dd>{{ $registro->arquivadoEm?->format('d/m/Y H:i') }}</dd></div>
                    <div><dt>Protocolo</dt><dd>{{ $registro->protocolo }}</dd></div>
                </dl>
            </section>

        </div>
    </div>
@endsection
