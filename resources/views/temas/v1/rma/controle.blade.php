@extends('temas.v1.layout')

{{-- VIS-V1-010 — painel "Controle" do TEMA V1, fonte real `14.6.1/page/controle.php`
(= `menujs-right/controle.php`), 7 ações administrativas na mesma ordem do legado.
`<details>/<summary>` reproduz o comportamento "painel colapsado por padrão, expande ao
clicar" do `expande()`/`minimize()` original sem precisar de JS novo (mesma filosofia de
VIS-V1-002: nativo primeiro). Cada ação reaproveita rota/caso de uso V3 já existente —
nenhum caso de uso novo foi criado para esta tela. --}}
@section('conteudo')
    @if (session('status'))
        <p class="centrodeavisos">{{ session('status') }}</p>
    @endif

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $erro)
                <li>{{ $erro }}</li>
            @endforeach
        </ul>
    @endif

    {{-- #01 ADICIONAR REPRESENTANTE — legado usa 1 form + select de tipo; aqui são 3
    forms, um por rota V3 já existente (parceiros.{fornecedores,fabricantes,assistencias-tecnicas}.store),
    sem inventar um dispatcher novo por "tipo". --}}
    <details>
        <summary class="formTitlePanel">ADICIONAR REPRESENTANTE</summary>

        <form method="POST" action="{{ route('parceiros.fornecedores.store') }}">
            @csrf
            <p class="fl formLabelPanel">FORNECEDOR — NOME:</p>
            <p class="fl"><input class="formInputPanel" type="text" name="nome" maxlength="255" required></p>
            <p class="fl"><button class="formButtonEnviarPanel" type="submit">ADICIONAR</button></p>
            <div style="height:10px;clear:both;"></div>
        </form>
        <form method="POST" action="{{ route('parceiros.fabricantes.store') }}">
            @csrf
            <p class="fl formLabelPanel">FABRICANTE — NOME:</p>
            <p class="fl"><input class="formInputPanel" type="text" name="nome" maxlength="255" required></p>
            <p class="fl"><button class="formButtonEnviarPanel" type="submit">ADICIONAR</button></p>
            <div style="height:10px;clear:both;"></div>
        </form>
        <form method="POST" action="{{ route('parceiros.assistencias-tecnicas.store') }}">
            @csrf
            <p class="fl formLabelPanel">ASSISTÊNCIA — NOME:</p>
            <p class="fl"><input class="formInputPanel" type="text" name="nome" maxlength="255" required></p>
            <p class="fl"><button class="formButtonEnviarPanel" type="submit">ADICIONAR</button></p>
            <div style="height:10px;clear:both;"></div>
        </form>
    </details>

    {{-- #04 ARQUIVAR UMA SOLICITACAO DE RMA — reaproveita `rmas.arquivar`
    (POST /rmas/{rma}/arquivar), já usado no detalhe do RMA. O legado identifica por
    "NUMERO" digitado; a rota V3 usa o id na URL, então o form reescreve a própria
    `action` com o valor digitado antes de submeter (POST nativo, sem fetch). --}}
    <details>
        <summary class="formTitlePanel">ARQUIVAR UMA SOLICITACAO DE RMA</summary>

        <form method="POST"
            action="{{ route('rmas.arquivar', ['rma' => '__ID__']) }}"
            onsubmit="this.action = this.action.replace('__ID__', this.numero.value); return true;">
            @csrf
            <p class="fl formLabelPanel">NUMERO:</p>
            <p class="fl"><input class="formInputPanel" type="text" name="numero" inputmode="numeric" required></p>
            <p class="fl"><button class="formButtonEnviarPanel" type="submit">ARQUIVAR</button></p>
        </form>
        <div style="height:10px;clear:both;"></div>
    </details>

    {{-- #05 DELETAR UMA SOLICITACAO DE RMA — VIS-V1-011, sem rota V3 (hard delete não
    existe, `Route::resource('rmas', ...)->except(['destroy'])`). Decisão de produto
    pendente — não implementado por inferência, só a pendência fica registrada aqui. --}}
    <details>
        <summary class="formTitlePanel">DELETAR UMA SOLICITACAO DE RMA</summary>
        <p>Pendente — exclusão definitiva de RMA depende de decisão de produto/segurança
            ainda não tomada (ver <code>VIS-V1-011</code> em
            <code>docs/produto/checklist-paridade-visual-v1-runtime.md</code>). Hoje só
            existe arquivamento (reversível), acima.</p>
    </details>

    {{-- #06 DELETAR UM USUARIO — VIS-V1-012, mesma situação: sem rota V3, decisão de
    produto pendente. --}}
    <details>
        <summary class="formTitlePanel">DELETAR UM USUARIO</summary>
        <p>Pendente — exclusão definitiva de usuário depende de decisão de produto/segurança
            ainda não tomada (ver <code>VIS-V1-012</code> em
            <code>docs/produto/checklist-paridade-visual-v1-runtime.md</code>).</p>
    </details>

    {{-- #07 INFORMACAO DO PROCEDIMENTO DE RMA — texto estático do legado, sem regra de
    negócio; artefatos de encoding do original (`m�os`, `�`) corrigidos para
    "mãos"/"é". --}}
    <details>
        <summary class="formTitlePanel">INFORMACAO DO PROCEDIMENTO DE RMA</summary>
        <div style="font-size:12px;">
            <p class="title-comicone">Olá {{ auth()->user()?->name }}, você está na Central de Ajuda</p>
            <hr class="both">
            <p>Nome da ferramenta: <strong>FERRAMENTA INTRANET DE RMA</strong></p>
            <p><strong>Versão:</strong> 14.6.1</p>
            <br>
            <p>3 ETAPAS: Entrada, Processamento e Saída</p>
            <br>
            <p>Entrada: Dados do produto</p>
            <p>Processamento: Encaminhamento do produto</p>
            <p>Saída: O produto reparado</p>
            <br>
            <p>Todo o processo é feito pelo próprio responsável pelos RMAs, desde
                adicionar uma nova solicitação para ele próprio. Vamos entender então:
                primeiro é adicionada uma nova solicitação de RMA, este vai para a
                ENTRADA e fica lá até que o setor de RMA assinale como RECEBIDO.</p>
            <br>
            <p>Para o RMA ser recebido, precisa estar com o produto em mãos e
                identificado. Agora é identificada a nota fiscal e para quem vai
                ENCAMINHAR (destinatário), assim é inserido os dados necessários para
                fazer o ENCAMINHAMENTO.</p>
            <br>
            <p>Para sair do RECEBIDO é necessário ter entrado em contato com o
                DESTINATÁRIO, recebido formulários e informações do outro lado, para
                então enviar novamente as informações e aguardar receber a autorização,
                para logo fazer a nota fiscal de remessa e ENCAMINHAR ao setor essa NF
                de remessa e aguardar AUTORIZAÇÃO da NF de remessa — se está correta —
                para então encaminhar este produto ao setor de solução.</p>
            <br>
            <p>Quando encaminhado para o DESTINATÁRIO, a solicitação vai para os
                ENCAMINHADOS e só sai de lá quando o produto retornar ao nosso SETOR DE
                RMA.</p>
            <br>
            <p>Então, quando ele retornar ao setor de RMA, ele será assinalado como
                CONCLUIDO, e nessa hora o produto pode retornar para a sua ORIGEM, como
                por exemplo o CLIENTE ou ESTOQUE.</p>
            <br>
            <p>Importante acompanhar o produto no Smallcomerce para que, se retirado -1
                do estoque, seja feito o retorno lançando o produto no Smallcomerce com
                a NF de RETORNO.</p>
        </div>
    </details>

    {{-- #08 LISTAR SOLICITACOES DE RMA ARQUIVADAS — VIS-V1-013, construída sobre
    `Status::Arquivado` (`ControlePainelController::index`), sem decisão de produto
    nova: o status já existe e já é gravado por `rmas.arquivar`/`rmas.reverter`. --}}
    <details>
        <summary class="formTitlePanel">LISTAR SOLICITACOES DE RMA ARQUIVADAS</summary>

        @if ($arquivados->isEmpty())
            <p>Nenhum item arquivado</p>
        @else
            <table class="Tabelinha-Table">
                <thead>
                    <tr class="TableListarFPEF-TR">
                        <th>CHAVE</th><th>FABRICANTE</th><th>DESCRICAO</th><th>MODELO</th><th>S/N</th><th>OS</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($arquivados as $indice => $registro)
                        <tr class="trcontrole1 {{ $indice % 2 === 0 ? 'Tabelinha-TR1' : 'Tabelinha-TR2' }}">
                            <td class="tdcontrole1"><a href="{{ route('rmas.show', ['rma' => $registro->id]) }}">{{ $registro->id }}</a></td>
                            <td class="tdcontrole1"><a href="{{ route('rmas.show', ['rma' => $registro->id]) }}">{{ $registro->fabricante?->nome }}</a></td>
                            <td class="tdcontrole1"><a href="{{ route('rmas.show', ['rma' => $registro->id]) }}">{{ $registro->descricao }}</a></td>
                            <td class="tdcontrole1"><a href="{{ route('rmas.show', ['rma' => $registro->id]) }}">{{ $registro->modelo }}</a></td>
                            <td class="tdcontrole1"><a href="{{ route('rmas.show', ['rma' => $registro->id]) }}">{{ $registro->sn }}</a></td>
                            <td class="tdcontrole1"><a href="{{ route('rmas.show', ['rma' => $registro->id]) }}">{{ $registro->os }}</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </details>

    {{-- #09 MUDAR SENHA — legado troca só a senha do usuário logado (ver
    `post/mudar_senha.php`, usa `$_SESSION["START1597_email"]`), sem exigir senha atual.
    Reaproveita `identidade.perfil.senha.update`, que já exige senha atual + confirmação
    — mais seguro que o legado; não é reintrodução do form inseguro. --}}
    <details>
        <summary class="formTitlePanel">MUDAR SENHA</summary>

        <form method="POST" action="{{ route('identidade.perfil.senha.update') }}">
            @csrf
            @method('PUT')
            <p class="fl formLabelPanel">SENHA ATUAL:</p>
            <p class="fl"><input class="formInputPanel" type="password" name="senha_atual" required></p>
            <div style="height:10px;clear:both;"></div>
            <p class="fl formLabelPanel">NOVA SENHA:</p>
            <p class="fl"><input class="formInputPanel" type="password" name="nova_senha" required></p>
            <div style="height:10px;clear:both;"></div>
            <p class="fl formLabelPanel">CONFIRMAR NOVA SENHA:</p>
            <p class="fl"><input class="formInputPanel" type="password" name="nova_senha_confirmation" required></p>
            <p class="fl"><button class="formButtonEnviarPanel" type="submit">SALVAR</button></p>
        </form>
        <div style="height:10px;clear:both;"></div>
    </details>
@endsection
