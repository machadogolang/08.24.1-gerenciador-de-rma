{{-- PAR15-RMA-DET-001 - select de ciclo de vida do detalhe RMA V2, usado no TOPO
(`selectacaoup` + `okup`) e no RODAPE (`selectacaodown` + `okdown`), exatamente como
o Legacy `15.8.1/page/rma.php` (que tem os dois blocos com as mesmas opcoes). As
regras de disponibilidade vem do dominio (`Status::pode*`), nunca duplicadas no
markup; o controller resolve a acao pelo bloco clicado. --}}
@php
    $sufixoAcao = $sufixoAcao ?? 'up';
    $classeSelectAcao = $classeSelectAcao ?? 'formSelect formSelect3';
    $classeBotaoAcao = $classeBotaoAcao ?? 'btn btn-default buttonSalvar';
@endphp
<select class="{{ $classeSelectAcao }}" name="selectacao{{ $sufixoAcao }}" id="selectacao{{ $sufixoAcao }}">
    <option value="salvar">SALVAR</option>
    @if ($registro->status->podeReverterParaEntrada())
        <option value="reverter">RETORNAR P/ ENTRADA</option>
    @endif
    @if ($registro->status->podeReceber())
        <option value="receber">RECEBER</option>
    @endif
    @if ($registro->status->podeEncaminhar())
        <option value="encaminhar">ENCAMINHAR</option>
    @endif
    @if ($registro->status->podeConcluir())
        <option value="concluir">CONCLUIR</option>
    @endif
</select>
<div class="both fr" style="margin-top:0px;margin-left:10px;">
    <button class="{{ $classeBotaoAcao }}" name="ok{{ $sufixoAcao }}" type="submit">OK</button>
</div>
