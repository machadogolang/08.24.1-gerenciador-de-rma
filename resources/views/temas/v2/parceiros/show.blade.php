@extends('temas.v2.layout')

@section('conteudo')
    {{-- PAR15-PART-001/006 - Detalhe nativo do Tema V2 (15.8.1/subp/ver_*.php):
    breadcrumb "ID / Nome", grade de dados com classes V2 e tabela de RMAs associados --}}
    <ol class="breadcrumb submenutitulo">
        <li class="fl" style="margin-top:0px;">{{ $registro->id }} / {{ $registro->nome }}</li>
        <li style="clear:both;"></li>
    </ol>

    <div class="detalhe-parceiro">
        <div class="detalhe-parceiro-v2" style="margin-bottom:20px;">
        <div style="margin-bottom:15px;">
            <a href="{{ rota_tema('parceiros.' . $tipo . '.edit', $registro) }}" class="btn btn-default acao acao--primaria" style="margin-right:5px;">
                Editar
            </a>
            <a href="{{ rota_tema('parceiros.' . $tipo . '.index') }}" class="btn btn-default acao acao--secundaria">
                Voltar
            </a>
        </div>

        <table class="Tabelinha-Table" style="margin-bottom:25px;">
            <tbody>
                @foreach (['nome', 'representante', 'cpf_cnpj', 'rgie', 'email', 'email_secundario', 'telefone', 'telefone2',
                    'cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf', 'www', 'frete', 'cfop'] as $campo)
                    @if (! empty($registro->{$campo}))
                        <tr class="{{ $loop->index % 2 === 0 ? 'TrZebrada1' : 'TrZebrada2' }}" style="height:28px;">
                            <th style="width:25%; text-align:left; padding:5px 8px; font-weight:600; color:#ddd;">{{ ucfirst(str_replace('_', ' ', $campo)) }}</th>
                            <td style="width:75%; text-align:left; padding:5px 8px; color:#fff;">{{ $registro->{$campo} }}</td>
                        </tr>
                    @endif
                @endforeach
                @if (! empty($registro->observacao))
                    <tr class="TrZebrada1" style="height:28px;">
                        <th style="text-align:left; padding:5px 8px; font-weight:600; color:#ddd;">Observação</th>
                        <td style="text-align:left; padding:5px 8px; color:#fff;">{{ $registro->observacao }}</td>
                    </tr>
                @endif
                @if (! empty($registro->politica_de_garantia))
                    <tr class="TrZebrada2" style="height:28px;">
                        <th style="text-align:left; padding:5px 8px; font-weight:600; color:#ddd;">Política de garantia</th>
                        <td style="text-align:left; padding:5px 8px; color:#fff;">{{ $registro->politica_de_garantia }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="boxtop-subpage" style="margin-top:20px;">
            <h3 class="box-subpage detalhe-parceiro-titulo" style="font-size:15px; padding:5px 0; color:#eee; border-bottom:1px solid #444; margin-bottom:10px;">
                RMAs associados
            </h3>
        </div>

        @if ($rmas->isEmpty())
            <p class="nenhumencontrado" style="color:#aaa; font-style:italic;">Nenhum RMA associado a este registro.</p>
        @else
            <table class="Tabelinha-Table" style="width:100%;">
                <thead>
                    <tr class="SuperTr">
                        <th style="width:10%; text-align:center;">#</th>
                        <th style="width:55%; text-align:left; padding-left:8px;">Descrição</th>
                        <th style="width:20%; text-align:center;">Status</th>
                        <th style="width:15%; text-align:center;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rmas as $registroRma)
                        <tr class="{{ $loop->index % 2 === 0 ? 'TrZebrada1' : 'TrZebrada2' }}" style="height:28px;">
                            <td style="text-align:center;">{{ $registroRma->id }}</td>
                            <td style="text-align:left; padding-left:8px;">{{ $registroRma->descricao }}</td>
                            <td style="text-align:center;">{{ $registroRma->status->name }}</td>
                            <td style="text-align:center;">
                                <a href="{{ route('rmas.show', $registroRma->id) }}" class="acao acao--secundaria acao--compacta">Ver</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
        </div>
    </div>
@endsection
