@extends('temas.v1.layout')

@section('conteudo')
    {{-- PAR14-PART - Detalhe nativo do Tema V1 (14.6.1):
    superficie de exibicao dentro de #JS-SessaoLEFT com acoes e tabela no padrao 14.6.1 --}}
    <div class="detalhe-parceiro">
        <div class="detalhe-parceiro-v1" style="margin-bottom:15px;">
        <div style="margin-bottom:12px;">
            <a href="{{ rota_tema('parceiros.' . $tipo . '.edit', $registro) }}" class="formButtonEnviarPanel acao acao--primaria" style="display:inline-block;text-decoration:none;padding:5px 12px;color:#fff;margin-right:8px;">
                EDITAR
            </a>
            <a href="{{ rota_tema('parceiros.' . $tipo . '.index') }}" class="formButtonEnviarPanel acao acao--secundaria" style="display:inline-block;text-decoration:none;padding:5px 12px;color:#fff;background:#555;">
                VOLTAR
            </a>
        </div>

        <table width="100%" class="Tabelinha-Table" style="margin-bottom:20px;">
            <tbody>
                @foreach (['nome', 'representante', 'cpf_cnpj', 'rgie', 'email', 'email_secundario', 'telefone', 'telefone2',
                    'cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf', 'www', 'frete', 'cfop'] as $campo)
                    @if (! empty($registro->{$campo}))
                        <tr class="{{ $loop->index % 2 === 0 ? 'Tabelinha-TR1' : 'Tabelinha-TR2' }}">
                            <th width="30%" style="text-align:left;padding:6px;">{{ strtoupper(str_replace('_', ' ', $campo)) }}</th>
                            <td width="70%" style="text-align:left;padding:6px;">{{ $registro->{$campo} }}</td>
                        </tr>
                    @endif
                @endforeach
                @if (! empty($registro->observacao))
                    <tr class="Tabelinha-TR1">
                        <th style="text-align:left;padding:6px;">OBSERVAÇÃO</th>
                        <td style="text-align:left;padding:6px;">{{ $registro->observacao }}</td>
                    </tr>
                @endif
                @if (! empty($registro->politica_de_garantia))
                    <tr class="Tabelinha-TR2">
                        <th style="text-align:left;padding:6px;">POLÍTICA DE GARANTIA</th>
                        <td style="text-align:left;padding:6px;">{{ $registro->politica_de_garantia }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="divisao-secao" style="margin:20px 0 10px 0; border-top:1px solid #ccc; padding-top:10px;">
            <h2 class="detalhe-parceiro-titulo" style="font-size:14px; font-weight:bold; color:#0b3c5d; margin-bottom:8px;">RMAs associados</h2>
            @if ($rmas->isEmpty())
                <p class="centrodeavisos">Nenhum RMA associado a este registro.</p>
            @else
                <table width="100%" class="Tabelinha-Table">
                    <thead>
                        <tr class="SuperTr">
                            <th width="10%">NÚMERO</th>
                            <th width="50%">DESCRIÇÃO</th>
                            <th width="25%">STATUS</th>
                            <th width="15%">AÇÕES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rmas as $registroRma)
                            <tr class="{{ $loop->index % 2 === 0 ? 'Tabelinha-TR1' : 'Tabelinha-TR2' }}" style="height:28px;">
                                <td style="text-align:center;">{{ $registroRma->id }}</td>
                                <td style="text-align:left;padding:4px 8px;">{{ $registroRma->descricao }}</td>
                                <td style="text-align:center;">{{ $registroRma->status->name }}</td>
                                <td style="text-align:center;">
                                    <a href="{{ route('rmas.show', $registroRma->id) }}" style="color:#004a80;font-weight:bold;text-decoration:none;" class="acao acao--secundaria acao--compacta">Ver</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
