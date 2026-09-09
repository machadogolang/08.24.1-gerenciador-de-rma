{{-- Ações de ciclo de vida (Fase 4) - FRONT-003/UI-02B: partial compartilhado por
V1/V2. Carrega só semântica de papel (`.acao--primaria`/`.acao--operacional`); cada
tema estiliza em seu SCSS. Rotas, CSRF, Gates, regras de status e inputs preservados.
--}}

<div class="acoes-de-transicao">
    @if ($registro->status->podeReceber())
        <form method="POST" action="{{ route('rmas.receber', $registro->id) }}">
            @csrf
            <button type="submit" class="acao acao--operacional">Receber</button>
        </form>
    @endif

    @if ($registro->status->podeEncaminhar())
        <form method="POST" action="{{ route('rmas.encaminhar', $registro->id) }}">
            @csrf
            <label>Tipo
                <select name="destinatario_tipo" class="formSelect acao-controle-select">
                    <option value="assistencia_tecnica">Assistência técnica</option>
                    <option value="fornecedor">Fornecedor</option>
                    <option value="fabricante">Fabricante</option>
                </select>
            </label>
            <label>Destinatário (id)
                <input type="number" name="destinatario_id" class="acao-controle-input">
            </label>
            <button type="submit" class="acao acao--operacional">Encaminhar</button>
        </form>
    @endif

    @if ($registro->status->podeConcluir())
        <form method="POST" action="{{ route('rmas.concluir', $registro->id) }}">
            @csrf
            <label>Solução
                <select name="solucao" class="formSelect acao-controle-select">
                    @foreach (\App\Rma\Dominio\Solucao::cases() as $solucao)
                        <option value="{{ $solucao->value }}">{{ $solucao->value }}</option>
                    @endforeach
                </select>
            </label>
            <button type="submit" class="acao acao--operacional">Concluir</button>
        </form>
    @endif

    @if ($registro->status->podeArquivar())
        <form method="POST" action="{{ route('rmas.arquivar', $registro->id) }}">
            @csrf
            <button type="submit" class="acao acao--operacional">Arquivar</button>
        </form>
    @endif

    @if ($registro->status->podeReverterParaEntrada())
        <form method="POST" action="{{ route('rmas.reverter', $registro->id) }}">
            @csrf
            <button type="submit" class="acao acao--operacional">Reverter para Entrada</button>
        </form>
    @endif

    <form method="POST" action="{{ route('rmas.solucao', $registro->id) }}">
        @csrf
        <label>Registrar solução (a qualquer momento)
            <select name="solucao" class="formSelect acao-controle-select">
                @foreach (\App\Rma\Dominio\Solucao::cases() as $solucao)
                    <option value="{{ $solucao->value }}" @selected($registro->solucao === $solucao)>{{ $solucao->value }}</option>
                @endforeach
            </select>
        </label>
        <button type="submit" class="acao acao--primaria">Salvar solução</button>
    </form>
</div>
