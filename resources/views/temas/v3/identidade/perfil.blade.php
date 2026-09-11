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

            <section class="cartao detalhe-v3__secao" id="seletor-temas">
                <h2 class="detalhe-v3__titulo">Seleção Explícita de Tema Visual</h2>
                <p class="pagina__resumo" style="margin-bottom: 16px;">
                    Selecione a experiência visual desejada para o seu fluxo de trabalho:
                </p>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px;">
                    <div class="cartao" style="border: 1px solid #dcdfe6; padding: 16px; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <h3 style="margin-top: 0; font-size: 1.1rem; color: #1f2937;">Tema V1 (14.6.1)</h3>
                            <p style="font-size: 0.9rem; color: #4b5563;">Layout clássico de alta densidade baseado na versão legada 14.6.1.</p>
                        </div>
                        <form method="POST" action="{{ route('tema.alternar') }}" style="margin-top: 12px;">
                            @csrf
                            <input type="hidden" name="tema" value="v1">
                            <button type="submit" class="botao botao--secundario" style="width: 100%;">
                                Ativar Tema V1
                            </button>
                        </form>
                    </div>

                    <div class="cartao" style="border: 1px solid #dcdfe6; padding: 16px; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <h3 style="margin-top: 0; font-size: 1.1rem; color: #1f2937;">Tema V2 (15.8.1)</h3>
                            <p style="font-size: 0.9rem; color: #4b5563;">Interface por abas horizontais e formulários da versão 15.8.1.</p>
                        </div>
                        <form method="POST" action="{{ route('tema.alternar') }}" style="margin-top: 12px;">
                            @csrf
                            <input type="hidden" name="tema" value="v2">
                            <button type="submit" class="botao botao--secundario" style="width: 100%;">
                                Ativar Tema V2
                            </button>
                        </form>
                    </div>

                    <div class="cartao" style="border: 2px solid #2563eb; background-color: #eff6ff; padding: 16px; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <h3 style="margin-top: 0; font-size: 1.1rem; color: #1e40af;">Tema V3 (Console)</h3>
                                <span class="status-badge" style="background-color: #2563eb; color: #fff;">Ativo</span>
                            </div>
                            <p style="font-size: 0.9rem; color: #1e3a8a;">Console operacional adaptativa moderna para alta produtividade.</p>
                        </div>
                        <div style="margin-top: 12px; font-size: 0.85rem; color: #2563eb; font-weight: 600;">
                            ✓ Visual ativo nesta sessão
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
