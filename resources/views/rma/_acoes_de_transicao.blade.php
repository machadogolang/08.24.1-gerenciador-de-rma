{{-- Ações de ciclo de vida (Fase 4) — sem fidelidade visual, ver Fase 8. --}}

@if ($registro->status->podeReceber())
    <form method="POST" action="{{ route('rmas.receber', $registro->id) }}">
        @csrf
        <button type="submit">Receber</button>
    </form>
@endif

@if ($registro->status->podeEncaminhar())
    <form method="POST" action="{{ route('rmas.encaminhar', $registro->id) }}">
        @csrf
        <label>Tipo
            <select name="destinatario_tipo">
                <option value="assistencia_tecnica">Assistência técnica</option>
                <option value="fornecedor">Fornecedor</option>
                <option value="fabricante">Fabricante</option>
            </select>
        </label>
        <label>Destinatário (id)
            <input type="number" name="destinatario_id">
        </label>
        <button type="submit">Encaminhar</button>
    </form>
@endif

@if ($registro->status->podeConcluir())
    <form method="POST" action="{{ route('rmas.concluir', $registro->id) }}">
        @csrf
        <label>Solução
            <select name="solucao">
                @foreach (\App\Rma\Dominio\Solucao::cases() as $solucao)
                    <option value="{{ $solucao->value }}">{{ $solucao->value }}</option>
                @endforeach
            </select>
        </label>
        <button type="submit">Concluir</button>
    </form>
@endif

@if ($registro->status->podeArquivar())
    <form method="POST" action="{{ route('rmas.arquivar', $registro->id) }}">
        @csrf
        <button type="submit">Arquivar</button>
    </form>
@endif

@if ($registro->status->podeReverterParaEntrada())
    <form method="POST" action="{{ route('rmas.reverter', $registro->id) }}">
        @csrf
        <button type="submit">Reverter para Entrada</button>
    </form>
@endif

<form method="POST" action="{{ route('rmas.solucao', $registro->id) }}">
    @csrf
    <label>Registrar solução (a qualquer momento)
        <select name="solucao">
            @foreach (\App\Rma\Dominio\Solucao::cases() as $solucao)
                <option value="{{ $solucao->value }}" @selected($registro->solucao === $solucao)>{{ $solucao->value }}</option>
            @endforeach
        </select>
    </label>
    <button type="submit">Salvar solução</button>
</form>
