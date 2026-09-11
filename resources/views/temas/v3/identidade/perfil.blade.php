@extends('temas.v3.layout')

@section('conteudo')
    <div class="pagina detalhe-v3">
        <div class="pagina__cabecalho">
            <div class="pagina__cabecalho-conteudo">
                <h1 class="pagina__titulo">Meu Perfil</h1>
                <p class="pagina__resumo">
                    Dados da conta e preferências operacionais.
                </p>
            </div>
        </div>

        @if (session('status'))
            <div class="cartao" style="border-left: 4px solid #177245; background-color: #f4fbf7; margin-bottom: 20px; padding: 12px 16px; color: #177245;">
                {{ session('status') }}
            </div>
        @endif

        <div class="detalhe-v3__secoes">
            <section class="cartao detalhe-v3__secao">
                <h2 class="detalhe-v3__titulo">Identificação do Usuário</h2>
                <dl class="detalhe-v3__grade">
                    <div><dt>Nome</dt><dd><strong>{{ $usuario->name }}</strong></dd></div>
                    <div><dt>E-mail</dt><dd>{{ $usuario->email }}</dd></div>
                    <div><dt>Papel no sistema</dt><dd><span class="status-badge">{{ $usuario->papelAtivo()->name }}</span></dd></div>
                    <div><dt>Tema atual</dt><dd>Console V3 (Modo Operacional)</dd></div>
                </dl>
            </section>
        </div>
    </div>
@endsection
