@extends('temas.v3.layout')

@section('conteudo')
    <div class="pagina pagina--dados">
        <div class="pagina__cabecalho">
            <div class="pagina__cabecalho-conteudo">
                <h1 class="pagina__titulo">Relatórios Operacionais e Fiscais</h1>
                <p class="pagina__resumo">
                    Acesse os relatórios específicos e acompanhe as métricas consolidadas de RMA.
                </p>
            </div>
        </div>

        @include('temas.v3.rma.relatorios._menu_relatorios', ['relatorioAtual' => 'painel'])

        {{-- Grade com os 3 relatórios operacionais principais (Wireframe V3) --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-bottom: 28px;">
            <article class="cartao" style="display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <span class="status-badge" style="margin-bottom: 8px;">RCD</span>
                    <h2 style="font-size: 1.15rem; margin: 0 0 8px;">Créditos Disponíveis</h2>
                    <p style="color: #5b6b7b; margin: 0 0 16px; font-size: 0.875rem;">
                        RMAs com crédito disponível concedido por fabricantes e fornecedores.
                    </p>
                </div>
                <a href="{{ rota_tema('rmas.relatorios.rcd') }}" class="botao" style="text-align: center;">
                    Abrir RCD
                </a>
            </article>

            <article class="cartao" style="display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <span class="status-badge" style="margin-bottom: 8px;">RPEC</span>
                    <h2 style="font-size: 1.15rem; margin: 0 0 8px;">Produtos para Contagem</h2>
                    <p style="color: #5b6b7b; margin: 0 0 16px; font-size: 0.875rem;">
                        Inventário e conferência física de produtos em processo de garantia.
                    </p>
                </div>
                <a href="{{ rota_tema('rmas.relatorios.rpec') }}" class="botao" style="text-align: center;">
                    Abrir RPEC
                </a>
            </article>

            <article class="cartao" style="display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <span class="status-badge" style="margin-bottom: 8px;">RMPE</span>
                    <h2 style="font-size: 1.15rem; margin: 0 0 8px;">Produtos Encaminhados</h2>
                    <p style="color: #5b6b7b; margin: 0 0 16px; font-size: 0.875rem;">
                        Produtos enviados para conserto em fornecedores e assistências técnicas.
                    </p>
                </div>
                <a href="{{ rota_tema('rmas.relatorios.rmpe') }}" class="botao" style="text-align: center;">
                    Abrir RMPE
                </a>
            </article>
        </div>

        {{-- Painel Estatístico Agregado em Seções Limpas do V3 --}}
        <div class="detalhe-v3__secoes">
            <section class="cartao detalhe-v3__secao">
                <h2 class="detalhe-v3__titulo">Situação Geral dos RMAs</h2>
                <dl class="detalhe-v3__grade">
                    @foreach ($painel['situacao'] as $item)
                        <div>
                            <dt>{{ $item['rotulo'] }}</dt>
                            <dd><strong>{{ $item['valor'] }}</strong></dd>
                        </div>
                    @endforeach
                </dl>
            </section>

            <section class="cartao detalhe-v3__secao">
                <h2 class="detalhe-v3__titulo">Resolução</h2>
                <dl class="detalhe-v3__grade">
                    @foreach ($painel['resolucao'] as $item)
                        <div>
                            <dt>{{ $item['rotulo'] }}</dt>
                            <dd><strong>{{ $item['valor'] }}</strong></dd>
                        </div>
                    @endforeach
                </dl>
            </section>

            <section class="cartao detalhe-v3__secao">
                <h2 class="detalhe-v3__titulo">Origem dos Produtos</h2>
                <dl class="detalhe-v3__grade">
                    @foreach ($painel['origem'] as $item)
                        <div>
                            <dt>{{ $item['rotulo'] }}</dt>
                            <dd><strong>{{ $item['valor'] }}</strong></dd>
                        </div>
                    @endforeach
                </dl>
            </section>

            @if (! empty($painel['fornecedores']))
                <section class="cartao detalhe-v3__secao">
                    <h2 class="detalhe-v3__titulo">Fornecedores</h2>
                    <dl class="detalhe-v3__grade">
                        @foreach ($painel['fornecedores'] as $item)
                            <div>
                                <dt>{{ $item['rotulo'] }}</dt>
                                <dd><strong>{{ $item['valor'] }}</strong></dd>
                            </div>
                        @endforeach
                    </dl>
                </section>
            @endif

            <section class="cartao detalhe-v3__secao">
                <h2 class="detalhe-v3__titulo">Dados Fiscais e Sistema</h2>
                <dl class="detalhe-v3__grade">
                    @foreach ($painel['notas'] as $item)
                        <div>
                            <dt>{{ $item['rotulo'] }}</dt>
                            <dd>{{ $item['valor'] }}</dd>
                        </div>
                    @endforeach
                    @foreach ($painel['sistema'] as $item)
                        <div>
                            <dt>{{ $item['rotulo'] }}</dt>
                            <dd>{{ $item['valor'] }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>
        </div>
    </div>
@endsection
