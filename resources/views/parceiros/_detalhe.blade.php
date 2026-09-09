{{-- P5 — detalhe de parceiro e RMAs associados, compartilhado pelos temas. --}}
<div class="detalhe-parceiro">
    <p>
        <a href="{{ rota_tema('parceiros.' . $tipo . '.edit', $registro) }}" class="acao acao--primaria">Editar</a>
        <a href="{{ rota_tema('parceiros.' . $tipo . '.index') }}" class="acao acao--secundaria">Voltar</a>
    </p>

    <table class="Tabelinha-Table">
        <tbody>
            @foreach (['nome', 'representante', 'cpf_cnpj', 'email', 'email_secundario', 'telefone', 'telefone2',
                'cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf', 'www', 'frete', 'cfop'] as $campo)
                @if (! empty($registro->{$campo}))
                    <tr>
                        <th style="text-align:left;">{{ ucfirst(str_replace('_', ' ', $campo)) }}</th>
                        <td style="text-align:left;">{{ $registro->{$campo} }}</td>
                    </tr>
                @endif
            @endforeach
            @if (! empty($registro->observacao))
                <tr><th style="text-align:left;">Observação</th><td style="text-align:left;">{{ $registro->observacao }}</td></tr>
            @endif
            @if (! empty($registro->politica_de_garantia))
                <tr><th style="text-align:left;">Política de garantia</th><td style="text-align:left;">{{ $registro->politica_de_garantia }}</td></tr>
            @endif
        </tbody>
    </table>

    <h2 class="detalhe-parceiro-titulo">RMAs associados</h2>
    @if ($rmas->isEmpty())
        <p class="nenhumencontrado">Nenhum RMA associado.</p>
    @else
        <table class="Tabelinha-Table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Descrição</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rmas as $registroRma)
                    <tr class="{{ $loop->index % 2 === 0 ? 'TrZebrada1' : 'TrZebrada2' }}">
                        <td>{{ $registroRma->id }}</td>
                        <td>{{ $registroRma->descricao }}</td>
                        <td>{{ $registroRma->status->name }}</td>
                        <td>
                            <a href="{{ route('rmas.show', $registroRma->id) }}" class="acao acao--secundaria acao--compacta">Ver</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
