@extends('temas.v1.layout')

@section('omitirTituloPadrao', true)

{{-- UI-V1-CONTROLE-01 - painel "Controle" do TEMA V1, fonte real `14.6.1/page/controle.php`
(= `menujs-right/controle.php`).
Preserva a fidelidade estrita ao Legacy 14.6.1:
- Formulario unico de Adicionar Representante (Nome + Select tipo + Adicionar)
- Arquivar RMA sem inline JS via rota segura dedicada
- Supressao de documentacao interna na interface do usuario
- Informacao do Procedimento fechada por padrao fiel ao 14.6.1
- Formularios historicos preservados em estetica pura (inputs/botoes)
- Mudar Senha preservando a seguranca moderna em grid compacto horizontal
- Recursos promovidos da Capability Union na linguagem compacta nativa V1
--}}
@section('conteudo')
    @if (session('status'))
        <p class="centrodeavisos" style="margin-bottom:12px;">{{ session('status') }}</p>
    @endif

    @if ($errors->any())
        <div class="centrodeavisos centrodeavisos--erro" style="margin-bottom:12px;background-color:rgba(180,40,40,0.4);border-color:rgba(255,80,80,0.6);">
            @foreach ($errors->all() as $erro)
                <p style="margin:2px 0;">{{ $erro }}</p>
            @endforeach
        </div>
    @endif

    {{-- #01 ADICIONAR REPRESENTANTE - Legacy 14.6.1: 1 form compacto horizontal com select de tipo --}}
    <details id="panel1-details" {{ session('painel_aberto') === 'representante' || $errors->has('nome') || $errors->has('tipo') ? 'open' : '' }}>
        <summary class="formTitlePanel">ADICIONAR REPRESENTANTE</summary>

        <form method="POST" action="{{ route('rmas.controle.representante.store') }}" style="margin-top:0px;">
            @csrf
            <p class="fl formLabelPanel">NOME:</p>
            <p class="fl">
                <input class="formInputPanel" type="text" name="nome" value="{{ old('nome') }}" maxlength="255" style="width:220px;" required>
            </p>
            <select class="formSelectPanel fl" name="tipo" style="margin-left:10px;">
                <option value="assistencia_tecnica" {{ old('tipo') === 'assistencia_tecnica' ? 'selected' : '' }}>ASSISTENCIA</option>
                <option value="fornecedor" {{ old('tipo') === 'fornecedor' ? 'selected' : '' }}>FORNECEDOR</option>
                <option value="fabricante" {{ old('tipo') === 'fabricante' ? 'selected' : '' }}>FABRICANTE</option>
            </select>
            <p class="fl">
                <button class="formButtonEnviarPanel" type="submit" style="margin-left:10px;">ADICIONAR</button>
            </p>
            <div style="height:10px;clear:both;"></div>
        </form>
    </details>

    {{-- #04 ARQUIVAR UMA SOLICITACAO DE RMA - Legacy 14.6.1: NUMERO + ARQUIVAR --}}
    <details id="panel4-details" {{ session('painel_aberto') === 'arquivar' || $errors->has('numero') ? 'open' : '' }}>
        <summary class="formTitlePanel">ARQUIVAR UMA SOLICITACAO DE RMA</summary>

        <form method="POST" action="{{ route('rmas.controle.arquivar') }}" style="margin-top:0px;">
            @csrf
            <p class="fl formLabelPanel">NUMERO:</p>
            <p class="fl">
                <input class="formInputPanel" type="text" name="numero" value="{{ old('numero') }}" inputmode="numeric" style="width:120px;" required>
            </p>
            <p class="fl">
                <button class="formButtonEnviarPanel" type="submit" style="margin-left:10px;">ARQUIVAR</button>
            </p>
            <div style="height:10px;clear:both;"></div>
        </form>
    </details>

    {{-- #05 DELETAR UMA SOLICITACAO DE RMA - formulario historico preservado (acao destrutiva desabilitada) --}}
    <details id="panel5-details">
        <summary class="formTitlePanel">DELETAR UMA SOLICITACAO DE RMA</summary>
        <div style="margin-top:0px;">
            <p class="fl formLabelPanel">NUMERO:</p>
            <p class="fl">
                <input class="formInputPanel" type="text" style="width:120px;" disabled title="Operação indisponível nesta versão">
            </p>
            <p class="fl">
                <button class="formButtonEnviarPanel" type="button" disabled style="margin-left:10px;opacity:0.6;cursor:not-allowed;" title="Operação indisponível nesta versão">DELETAR</button>
            </p>
            <div style="height:10px;clear:both;"></div>
        </div>
    </details>

    {{-- #06 DELETAR UM USUARIO - formulario historico preservado (acao destrutiva desabilitada) --}}
    <details id="panel6-details">
        <summary class="formTitlePanel">DELETAR UM USUARIO</summary>
        <div style="margin-top:0px;">
            <p class="fl formLabelPanel">E-MAIL:</p>
            <p class="fl">
                <input class="formInputPanel" type="text" style="width:200px;" disabled title="Operação indisponível nesta versão">
            </p>
            <p class="fl">
                <button class="formButtonEnviarPanel" type="button" disabled style="margin-left:10px;opacity:0.6;cursor:not-allowed;" title="Operação indisponível nesta versão">DELETAR</button>
            </p>
            <div style="height:10px;clear:both;"></div>
        </div>
    </details>

    {{-- #07 INFORMACAO DO PROCEDIMENTO DE RMA - texto operacional fiel ao 14.6.1 --}}
    <details id="panel7-details">
        <summary class="formTitlePanel">INFORMACAO DO PROCEDIMENTO DE RMA</summary>
        <div style="font-size:12px;font-family:'Fira Mono','Open Sans','Arial',sans-serif;line-height:1.6;max-width:780px;padding:4px 0 6px 0;">
            <p class="title-comicone">Ola {{ auth()->user()?->name }}, voce esta na Central de Ajuda</p>
            <hr class="both" style="border:0;border-top:1px solid rgba(255,255,255,0.15);margin:6px 0 10px 0;">
            <p>Nome da ferramenta: <strong>FERRAMENTA INTRANET DE RMA</strong></p>
            <p><strong>Versao:</strong> 14.6.1</p>
            <br>
            <p>3 ETAPAS: Entrada, Processamento e Saida</p>
            <br>
            <p>Entrada: Dados do produto</p>
            <p>Processamento: Encaminhamento do produto</p>
            <p>Saida: O produto reparado</p>
            <br>
            <p>Todo o processo e feito pelo proprio responsavel pelos RMAS desde adicionar uma nova solicitacao para ele proprio, vamos entender entao, primeiro e adicionado uma nova solicitacao de rma, este vai para a ENTRADA e fica la ate que o setor de rma assinale como RECEBIDO</p>
            <br>
            <p>Para o rma ser recebido, precisa estar com o produto em maos e identificado, agora e identificado a nota fiscal e para quem vai ENCAMINHAR (destinatario), assim e inserido os dados necessarios para fazer o ENCAMINHAMENTO</p>
            <br>
            <p>Para sair do RECEBIDO e necessario ter entrado em contato com o DESTINATARIO, recebido formularios e informacoes do outro lado para entao enviar novamente as informacoes e aguardar receber a autorizacao, para logo fazer a nota fiscal de remessa e ENCAMINHAR ao setor essa nf de remessa e aguardar AUTORIZACAO da nf de remessa SE ESTA CORRETA para entao encaminhar este produto ao setor de solucao</p>
            <br>
            <p>Quando encaminhado para o DESTINATARIO a solicitacao vai para os ENCAMINHADOS e so sai de la quando o produto retornar ao nosso SETOR DE RMA.</p>
            <br>
            <p>Entao quando ele retornar ao o setor de rma, ele sera assinalado como CONCLUIDO, e nessa hora que o produto pode retornar para a sua ORIGEM como por exemplo o CLIENTE ou ESTOQUE.</p>
            <br>
            <p>Importante acompanhar o produto no Smallcomerce para que Se retirado -1 do Estoque, fazer o retorno lancando o produto no Smallcomerce com a NF de RETORNO</p>
            <div style="height:10px;clear:both;"></div>
        </div>
    </details>

    {{-- #08 LISTAR SOLICITACOES DE RMA ARQUIVADAS - tabela de 6 colunas do 14.6.1 --}}
    <details id="panel8-details">
        <summary class="formTitlePanel">LISTAR SOLICITACOES DE RMA ARQUIVADAS</summary>

        <div style="margin-top:6px;margin-bottom:10px;max-height:280px;overflow-y:auto;">
        @if ($arquivados->isEmpty())
            <div style="margin-left:5px;margin-top:6px;font-size:11px;margin-bottom:10px;color:#aaa;">Nenhum item arquivado</div>
        @else
            <table width="100%" style="border:1px solid rgba(0,0,0,0.2);margin-bottom:10px;border-collapse:collapse;">
                <thead>
                    <tr style="background-color:rgba(0,0,0,0.4);height:30px;font-family:Arial,Tahoma,sans-serif;font-size:11px;border:0;text-align:center;">
                        <th width="10%" style="color:#FFF;padding:4px;">CHAVE</th>
                        <th width="15%" style="color:#FFF;padding:4px;">FABRICANTE</th>
                        <th width="20%" style="color:#FFF;padding:4px;">DESCRICAO</th>
                        <th width="20%" style="color:#FFF;padding:4px;">MODELO</th>
                        <th width="20%" style="color:#FFF;padding:4px;">S/N</th>
                        <th width="10%" style="color:#FFF;padding:4px;">OS</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($arquivados as $registro)
                        <tr class="trcontrole1">
                            <td class="tdcontrole1"><a href="{{ route('rmas.show', ['rma' => $registro->id]) }}"><div>{{ $registro->id }}</div></a></td>
                            <td class="tdcontrole1"><a href="{{ route('rmas.show', ['rma' => $registro->id]) }}"><div>{{ $registro->fabricante?->nome ?? '-' }}</div></a></td>
                            <td class="tdcontrole1"><a href="{{ route('rmas.show', ['rma' => $registro->id]) }}"><div>{{ $registro->descricao }}</div></a></td>
                            <td class="tdcontrole1"><a href="{{ route('rmas.show', ['rma' => $registro->id]) }}"><div>{{ $registro->modelo }}</div></a></td>
                            <td class="tdcontrole1"><a href="{{ route('rmas.show', ['rma' => $registro->id]) }}"><div>{{ $registro->sn }}</div></a></td>
                            <td class="tdcontrole1"><a href="{{ route('rmas.show', ['rma' => $registro->id]) }}"><div>{{ $registro->os }}</div></a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
        </div>
        <div style="height:10px;clear:both;"></div>
    </details>

    {{-- #09 MUDAR SENHA - seguranca moderna em layout compacto horizontal --}}
    <details id="panel9-details" {{ $errors->has('senha_atual') || $errors->has('nova_senha') ? 'open' : '' }}>
        <summary class="formTitlePanel">MUDAR SENHA</summary>

        <form method="POST" action="{{ route('identidade.perfil.senha.update') }}" style="margin-top:0px;">
            @csrf
            @method('PUT')
            <p class="fl formLabelPanel">SENHA ATUAL:</p>
            <p class="fl"><input class="formInputPanel" type="password" name="senha_atual" style="width:110px;" required></p>
            <p class="fl formLabelPanel" style="margin-left:10px;">NOVA SENHA:</p>
            <p class="fl"><input class="formInputPanel" type="password" name="nova_senha" style="width:110px;" required></p>
            <p class="fl formLabelPanel" style="margin-left:10px;">CONFIRMAR:</p>
            <p class="fl"><input class="formInputPanel" type="password" name="nova_senha_confirmation" style="width:110px;" required></p>
            <p class="fl">
                <button class="formButtonEnviarPanel" type="submit" style="margin-left:10px;">SALVAR</button>
            </p>
            <div style="height:10px;clear:both;"></div>
        </form>
    </details>

    {{-- UF-10 / UF-14 - Capacidades Promovidas da Unificacao Funcional em formato compacto 14.6.1 --}}
    <details id="panel-logs-autenticacao">
        <summary class="formTitlePanel">LOGS DE AUTENTICAÇÃO</summary>
        <div style="margin-top:0px;">
            <p class="fl formLabelPanel">HISTORICO DE ACESSOS:</p>
            <p class="fl">
                <a href="{{ rota_tema('identidade.historico-de-acesso.index') }}" class="formButtonEnviarPanel" style="display:inline-block;text-align:center;line-height:18px;text-decoration:none;color:#fff;margin-left:0;">
                    ABRIR
                </a>
            </p>
            <div style="height:10px;clear:both;"></div>
        </div>
    </details>

    <details id="panel-logs-modificacao">
        <summary class="formTitlePanel">LOGS DE MODIFICAÇÃO DE RMA</summary>
        <div style="margin-top:0px;">
            <p class="fl formLabelPanel">HISTORICO DE ALTERACOES:</p>
            <p class="fl">
                <a href="{{ rota_tema('rmas.historico.index') }}" class="formButtonEnviarPanel" style="display:inline-block;text-align:center;line-height:18px;text-decoration:none;color:#fff;margin-left:0;">
                    ABRIR
                </a>
            </p>
            <div style="height:10px;clear:both;"></div>
        </div>
    </details>

    <details id="panel-novo-usuario">
        <summary class="formTitlePanel">CADASTRAR NOVO USUÁRIO</summary>
        <div style="margin-top:0px;">
            <p class="fl formLabelPanel">FORMULARIO DE CADASTRO:</p>
            <p class="fl">
                <a href="{{ rota_tema('identidade.usuarios.create') }}" class="formButtonEnviarPanel" style="display:inline-block;text-align:center;line-height:18px;text-decoration:none;color:#fff;margin-left:0;">
                    ABRIR
                </a>
            </p>
            <div style="height:10px;clear:both;"></div>
        </div>
    </details>
@endsection
