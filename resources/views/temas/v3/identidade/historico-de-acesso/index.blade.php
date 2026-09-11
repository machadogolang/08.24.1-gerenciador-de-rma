@extends('temas.v3.layout')

@section('conteudo')
    <div class="pagina pagina--dados">
        <div class="pagina__cabecalho">
            <div class="pagina__cabecalho-conteudo">
                <h1 class="pagina__titulo">Histórico de Acesso</h1>
                <p class="pagina__resumo">
                    Registro de autenticações e tentativas de login no sistema.
                </p>
            </div>
        </div>

        <div class="barra-busca">
            <input class="barra-busca__campo" type="search" id="filtro-acesso"
                placeholder="Filtrar por data, usuário ou IP..."
                aria-label="Filtrar acessos" data-v3-filtro-tabela>
        </div>

        @if ($tentativas->isEmpty())
            <div class="estado-vazio">
                <p>Nenhum registro de acesso encontrado.</p>
            </div>
        @else
            <div class="tabela-wrapper">
                <table class="tabela-v3" data-tabela-skinless="true">
                    <thead>
                        <tr>
                            <th scope="col">Data</th>
                            <th scope="col">Usuário / Identificação</th>
                            <th scope="col">IP</th>
                            <th scope="col">Navegador</th>
                            <th scope="col" style="text-align: right;">Resultado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tentativas as $tentativa)
                            <tr data-linha-parceiro>
                                <td>{{ $tentativa->created_at?->format('d/m/Y H:i:s') }}</td>
                                <td><strong>{{ $tentativa->email_tentado ?? $tentativa->user?->name ?? '-' }}</strong></td>
                                <td>{{ $tentativa->ip ?? '-' }}</td>
                                <td>{{ $tentativa->user_agent ?? '-' }}</td>
                                <td style="text-align: right;">
                                    @if ($tentativa->sucesso)
                                        <span class="status-badge status-badge--concluido">Sucesso</span>
                                    @else
                                        <span class="status-badge status-badge--arquivado" style="color: #b3261e; border-color: #b3261e;">Falha</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="cartoes-parceiro">
                @foreach ($tentativas as $tentativa)
                    <article class="cartao-parceiro" data-linha-parceiro>
                        <div class="cartao-parceiro__cabecalho">
                            <strong>{{ $tentativa->email_tentado ?? $tentativa->user?->name ?? '-' }}</strong>
                            @if ($tentativa->sucesso)
                                <span class="status-badge status-badge--concluido">Sucesso</span>
                            @else
                                <span class="status-badge" style="color: #b3261e;">Falha</span>
                            @endif
                        </div>
                        <p style="font-size: 0.875rem; color: #5b6b7b;">
                            {{ $tentativa->created_at?->format('d/m/Y H:i:s') }} - IP: {{ $tentativa->ip ?? '-' }}
                        </p>
                    </article>
                @endforeach
            </div>

            <div style="margin-top: 20px;">
                {{ $tentativas->links() }}
            </div>
        @endif
    </div>
@endsection
