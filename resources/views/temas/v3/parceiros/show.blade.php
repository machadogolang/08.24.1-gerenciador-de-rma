@extends('temas.v3.layout')

@php
    $nomeSingular = match ($tipo) {
        'clientes' => 'cliente',
        'fornecedores' => 'fornecedor',
        'fabricantes' => 'fabricante',
        'assistencias-tecnicas' => 'assistência técnica',
        default => 'parceiro',
    };
@endphp

@section('conteudo')
    <div class="pagina detalhe-v3">
        <p style="display: flex; gap: 8px; align-items: center; margin-bottom: 16px;">
            <a href="{{ rota_tema('parceiros.' . $tipo . '.index') }}" class="botao botao--secundario">
                Voltar para {{ ucfirst($tipo) }}
            </a>
            @can('update', $registro)
                <a href="{{ rota_tema('parceiros.' . $tipo . '.edit', $registro) }}" class="botao">
                    Editar {{ $nomeSingular }}
                </a>
            @endcan
        </p>

        <header class="cartao detalhe-v3__cabecalho">
            <div class="detalhe-v3__identificacao">
                <p class="detalhe-v3__rotulo">Parceiro / {{ ucfirst($nomeSingular) }}</p>
                <h1 class="detalhe-v3__numero">{{ $registro->nome }}</h1>
                @if (! empty($registro->representante))
                    <p class="detalhe-v3__descricao">Representante: {{ $registro->representante }}</p>
                @endif
            </div>
            <dl class="detalhe-v3__meta">
                @if (! empty($registro->cpf_cnpj))
                    <div>
                        <dt>Documento</dt>
                        <dd>{{ $registro->cpf_cnpj }}</dd>
                    </div>
                @endif
                @if (! empty($registro->cidade))
                    <div>
                        <dt>Localizacao</dt>
                        <dd>{{ $registro->cidade }}{{ $registro->uf ? '/' . $registro->uf->value : '' }}</dd>
                    </div>
                @endif
                <div>
                    <dt>RMAs vinculados</dt>
                    <dd><strong>{{ $rmas->count() }}</strong></dd>
                </div>
            </dl>
        </header>

        <div class="detalhe-v3__secoes">
            {{-- Dados Cadastrais --}}
            <section class="cartao detalhe-v3__secao" aria-labelledby="sec-identificacao">
                <h2 id="sec-identificacao" class="detalhe-v3__titulo">Identificacao</h2>
                <dl class="detalhe-v3__grade">
                    <div><dt>Nome</dt><dd>{{ $registro->nome }}</dd></div>
                    <div><dt>Representante</dt><dd>{{ $registro->representante ?: '-' }}</dd></div>
                    <div><dt>CPF / CNPJ</dt><dd>{{ $registro->cpf_cnpj ?: '-' }}</dd></div>
                    <div><dt>RG / IE</dt><dd>{{ $registro->rgie ?: '-' }}</dd></div>
                </dl>
            </section>

            {{-- Contato --}}
            <section class="cartao detalhe-v3__secao" aria-labelledby="sec-contato">
                <h2 id="sec-contato" class="detalhe-v3__titulo">Contato</h2>
                <dl class="detalhe-v3__grade">
                    <div><dt>Telefone</dt><dd>{{ $registro->telefone ?: '-' }}</dd></div>
                    <div><dt>Telefone 2</dt><dd>{{ $registro->telefone2 ?: '-' }}</dd></div>
                    <div><dt>E-mail</dt><dd>{{ $registro->email ?: '-' }}</dd></div>
                    @if ($comEnderecoEContato ?? false)
                        <div><dt>E-mail secundario</dt><dd>{{ $registro->email_secundario ?: '-' }}</dd></div>
                        <div><dt>Website (WWW)</dt><dd>{{ $registro->www ?: '-' }}</dd></div>
                    @endif
                </dl>
            </section>

            {{-- Endereco --}}
            <section class="cartao detalhe-v3__secao" aria-labelledby="sec-endereco">
                <h2 id="sec-endereco" class="detalhe-v3__titulo">Endereco</h2>
                <dl class="detalhe-v3__grade">
                    <div><dt>CEP</dt><dd>{{ $registro->cep ?: '-' }}</dd></div>
                    <div><dt>Logradouro</dt><dd>{{ $registro->logradouro ?: '-' }}</dd></div>
                    <div><dt>Numero</dt><dd>{{ $registro->numero ?: '-' }}</dd></div>
                    <div><dt>Complemento</dt><dd>{{ $registro->complemento ?: '-' }}</dd></div>
                    <div><dt>Bairro</dt><dd>{{ $registro->bairro ?: '-' }}</dd></div>
                    <div><dt>Cidade</dt><dd>{{ $registro->cidade ?: '-' }}</dd></div>
                    <div><dt>UF</dt><dd>{{ $registro->uf?->value ?: '-' }}</dd></div>
                </dl>
            </section>

            {{-- Fiscal e Logistica (se preenchido) --}}
            @if (($comEnderecoEContato ?? false) && (! empty($registro->frete) || ! empty($registro->cfop)))
                <section class="cartao detalhe-v3__secao" aria-labelledby="sec-fiscal-logistica">
                    <h2 id="sec-fiscal-logistica" class="detalhe-v3__titulo">Comercial e Logistica</h2>
                    <dl class="detalhe-v3__grade">
                        <div><dt>Frete</dt><dd>{{ $registro->frete ?: '-' }}</dd></div>
                        <div><dt>CFOP</dt><dd>{{ $registro->cfop ?: '-' }}</dd></div>
                    </dl>
                </section>
            @endif

            {{-- Informacoes Adicionais / Garantia --}}
            @if (! empty($registro->observacao) || ! empty($registro->politica_de_garantia))
                <section class="cartao detalhe-v3__secao" aria-labelledby="sec-informacoes-adicionais">
                    <h2 id="sec-informacoes-adicionais" class="detalhe-v3__titulo">Informacoes Adicionais</h2>
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        @if (! empty($registro->observacao))
                            <div>
                                <h3 style="font-size: 0.95rem; margin: 0 0 4px; color: #5b6b7b;">Observacao</h3>
                                <p style="margin: 0; white-space: pre-wrap;">{{ $registro->observacao }}</p>
                            </div>
                        @endif
                        @if (! empty($registro->politica_de_garantia))
                            <div>
                                <h3 style="font-size: 0.95rem; margin: 0 0 4px; color: #5b6b7b;">Politica de garantia</h3>
                                <p style="margin: 0; white-space: pre-wrap;">{{ $registro->politica_de_garantia }}</p>
                            </div>
                        @endif
                    </div>
                </section>
            @endif

            {{-- RMAs Relacionados --}}
            <section class="cartao detalhe-v3__secao" aria-labelledby="sec-rmas-relacionados">
                <h2 id="sec-rmas-relacionados" class="detalhe-v3__titulo">RMAs Associados</h2>
                @if ($rmas->isEmpty())
                    <p class="estado-vazio" style="padding: 24px; text-align: center;">
                        Nenhum RMA vinculado a este parceiro.
                    </p>
                @else
                    <div class="tabela-wrapper">
                        <table class="tabela-v3">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Descricao</th>
                                    <th scope="col">Modelo</th>
                                    <th scope="col">Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rmas as $rma)
                                    <tr>
                                        <td>
                                            <a href="{{ route('v3.rmas.show', ['rma' => $rma->id]) }}">
                                                <strong>{{ $rma->numero_da_empresa ?? $rma->id }}</strong>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="status-badge status-badge--{{ strtolower($rma->status->name) }}">
                                                {{ $rma->status->name }}
                                            </span>
                                        </td>
                                        <td>{{ $rma->descricao }}</td>
                                        <td>{{ $rma->modelo ?: '-' }}</td>
                                        <td>{{ $rma->created_at?->format('d/m/Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="cartoes-rma">
                        @foreach ($rmas as $rma)
                            <article class="cartao-rma">
                                <div class="cartao-rma__cabecalho">
                                    <a href="{{ route('v3.rmas.show', ['rma' => $rma->id]) }}">
                                        <strong>{{ $rma->numero_da_empresa ?? $rma->id }}</strong>
                                    </a>
                                    <span class="status-badge status-badge--{{ strtolower($rma->status->name) }}">
                                        {{ $rma->status->name }}
                                    </span>
                                </div>
                                <p>{{ $rma->descricao }}</p>
                                <p>{{ $rma->modelo ?: 'Sem modelo' }} - {{ $rma->created_at?->format('d/m/Y') }}</p>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </div>
@endsection
