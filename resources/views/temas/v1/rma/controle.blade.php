@extends('temas.v1.layout')

{{-- UI-V1-CONTROLE-01 - painel "Controle" do TEMA V1, fonte real `14.6.1/page/controle.php`
(= `menujs-right/controle.php`).
Preserva a fidelidade estrita ao Legacy 14.6.1:
- Formulario unico de Adicionar Representante (Nome + Select tipo + Adicionar)
- Arquivar RMA sem inline JS via rota segura dedicada
- Supressao de documentacao interna na interface do usuario
- Informacao do Procedimento fechada por padrao com link discreto para Ajuda
- Mudar Senha preservando a seguranca moderna dos 3 campos alinhados no grid V1
- Recursos promovidos da Capability Union na linguagem nativa V1
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

    {{-- #01 ADICIONAR REPRESENTANTE - Legacy 14.6.1: 1 form compacto com select de tipo --}}
    <details {{ session('painel_aberto') === 'representante' || $errors->has('nome') || $errors->has('tipo') ? 'open' : '' }}>
        <summary class="formTitlePanel">ADICIONAR REPRESENTANTE</summary>

        <form method="POST" action="{{ route('rmas.controle.representante.store') }}" style="margin-top:6px;">
            @csrf
            <span class="fl formLabelPanel">NOME:</span>
            <span class="fl">
                <input class="formInputPanel" type="text" name="nome" value="{{ old('nome') }}" maxlength="255" style="width:220px;" required>
            </span>
            <select class="formSelectPanel fl" name="tipo" style="margin-left:10px;">
                <option value="assistencia_tecnica" {{ old('tipo') === 'assistencia_tecnica' ? 'selected' : '' }}>ASSISTENCIA</option>
                <option value="fornecedor" {{ old('tipo') === 'fornecedor' ? 'selected' : '' }}>FORNECEDOR</option>
                <option value="fabricante" {{ old('tipo') === 'fabricante' ? 'selected' : '' }}>FABRICANTE</option>
            </select>
            <span class="fl">
                <button class="formButtonEnviarPanel" type="submit" style="margin-left:10px;">ADICIONAR</button>
            </span>
            <div style="height:12px;clear:both;"></div>
        </form>
    </details>

    {{-- #04 ARQUIVAR UMA SOLICITACAO DE RMA - Legacy 14.6.1: NUMERO + ARQUIVAR sem inline JS --}}
    <details {{ session('painel_aberto') === 'arquivar' || $errors->has('numero') ? 'open' : '' }}>
        <summary class="formTitlePanel">ARQUIVAR UMA SOLICITACAO DE RMA</summary>

        <form method="POST" action="{{ route('rmas.controle.arquivar') }}" style="margin-top:6px;">
            @csrf
            <span class="fl formLabelPanel">NUMERO:</span>
            <span class="fl">
                <input class="formInputPanel" type="text" name="numero" value="{{ old('numero') }}" inputmode="numeric" style="width:120px;" required>
            </span>
            <span class="fl">
                <button class="formButtonEnviarPanel" type="submit" style="margin-left:10px;">ARQUIVAR</button>
            </span>
            <div style="height:12px;clear:both;"></div>
        </form>
    </details>

    {{-- #05 DELETAR UMA SOLICITACAO DE RMA - acao destrutiva indisponivel nesta versao --}}
    <details>
        <summary class="formTitlePanel">DELETAR UMA SOLICITACAO DE RMA</summary>
        <div style="padding:4px 0 8px 0;">
            <p style="font-size:11px;color:#aaa;margin:0;">Operação indisponível nesta versão.</p>
        </div>
        <div style="height:6px;clear:both;"></div>
    </details>

    {{-- #06 DELETAR UM USUARIO - acao destrutiva indisponivel nesta versao --}}
    <details>
        <summary class="formTitlePanel">DELETAR UM USUARIO</summary>
        <div style="padding:4px 0 8px 0;">
            <p style="font-size:11px;color:#aaa;margin:0;">Operação indisponível nesta versão.</p>
        </div>
        <div style="height:6px;clear:both;"></div>
    </details>

    {{-- #07 INFORMACAO DO PROCEDIMENTO DE RMA - texto operacional fechado por padrao --}}
    <details>
        <summary class="formTitlePanel">INFORMACAO DO PROCEDIMENTO DE RMA</summary>
        <div style="font-size:12px;font-family:'Fira Mono','Open Sans','Arial',sans-serif;line-height:1.6;max-width:780px;padding:6px 0 10px 0;">
            <p class="title-comicone">Olá {{ auth()->user()?->name }}, você está na Central de Ajuda</p>
            <hr class="both" style="border:0;border-top:1px solid rgba(255,255,255,0.15);margin:6px 0 10px 0;">
            <p>Nome da ferramenta: <strong>FERRAMENTA INTRANET DE RMA</strong></p>
            <p><strong>Versão:</strong> 14.6.1</p>
            <br>
            <p><strong>3 ETAPAS:</strong> Entrada, Processamento e Saída</p>
            <br>
            <p><strong>Entrada:</strong> Dados do produto</p>
            <p><strong>Processamento:</strong> Encaminhamento do produto</p>
            <p><strong>Saída:</strong> O produto reparado</p>
            <br>
            <p>Todo o processo é feito pelo próprio responsável pelos RMAs, desde adicionar uma nova solicitação para ele próprio. Vamos entender então: primeiro é adicionada uma nova solicitação de RMA, este vai para a ENTRADA e fica lá até que o setor de RMA assinale como RECEBIDO.</p>
            <br>
            <p>Para o RMA ser recebido, precisa estar com o produto em mãos e identificado. Agora é identificada a nota fiscal e para quem vai ENCAMINHAR (destinatário), assim é inserido os dados necessários para fazer o ENCAMINHAMENTO.</p>
            <br>
            <p>Para sair do RECEBIDO é necessário ter entrado em contato com o DESTINATÁRIO, recebido formulários e informações do outro lado, para então enviar novamente as informações e aguardar receber a autorização, para logo fazer a nota fiscal de remessa e ENCAMINHAR ao setor essa NF de remessa e aguardar AUTORIZAÇÃO da NF de remessa - se está correta - para então encaminhar este produto ao setor de solução.</p>
            <br>
            <p>Quando encaminhado para o DESTINATÁRIO, a solicitação vai para os ENCAMINHADOS e só sai de lá quando o produto retornar ao nosso SETOR DE RMA.</p>
            <br>
            <p>Então, quando ele retornar ao setor de RMA, ele será assinalado como CONCLUIDO, e nessa hora o produto pode retornar para a sua ORIGEM, como por exemplo o CLIENTE ou ESTOQUE.</p>
            <br>
            <p>Importante acompanhar o produto no Smallcomerce para que, se retirado -1 do estoque, seja feito o retorno lançando o produto no Smallcomerce com a NF de RETORNO.</p>
            <p style="margin-top:14px;">
                <a href="{{ route('rmas.ajuda') }}" class="formButtonEnviarPanel" style="display:inline-block;width:auto;padding:2px 12px;text-decoration:none;color:#fff;line-height:20px;text-align:center;">
                    ABRIR CENTRAL DE AJUDA
                </a>
            </p>
        </div>
        <div style="height:8px;clear:both;"></div>
    </details>

    {{-- #08 LISTAR SOLICITACOES DE RMA ARQUIVADAS - tabela de 6 colunas do 14.6.1 --}}
    <details>
        <summary class="formTitlePanel">LISTAR SOLICITACOES DE RMA ARQUIVADAS</summary>

        <div class="controle-arquivados-scroll" style="margin-top:6px;margin-bottom:12px;">
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
        <div style="height:6px;clear:both;"></div>
    </details>

    {{-- #09 MUDAR SENHA - seguranca moderna dos 3 campos alinhados no grid V1 --}}
    <details {{ $errors->has('senha_atual') || $errors->has('nova_senha') ? 'open' : '' }}>
        <summary class="formTitlePanel">MUDAR SENHA</summary>

        <form method="POST" action="{{ route('identidade.perfil.senha.update') }}" style="margin-top:6px;">
            @csrf
            @method('PUT')
            <div style="margin-bottom:6px;">
                <span class="fl formLabelPanel" style="width:160px;">SENHA ATUAL:</span>
                <span class="fl"><input class="formInputPanel" type="password" name="senha_atual" style="width:200px;" required></span>
                <div style="clear:both;"></div>
            </div>
            <div style="margin-bottom:6px;">
                <span class="fl formLabelPanel" style="width:160px;">NOVA SENHA:</span>
                <span class="fl"><input class="formInputPanel" type="password" name="nova_senha" style="width:200px;" required></span>
                <div style="clear:both;"></div>
            </div>
            <div style="margin-bottom:8px;">
                <span class="fl formLabelPanel" style="width:160px;">CONFIRMAR NOVA SENHA:</span>
                <span class="fl"><input class="formInputPanel" type="password" name="nova_senha_confirmation" style="width:200px;" required></span>
                <span class="fl" style="margin-left:10px;">
                    <button class="formButtonEnviarPanel" type="submit">SALVAR</button>
                </span>
                <div style="clear:both;"></div>
            </div>
        </form>
        <div style="height:8px;clear:both;"></div>
    </details>

    {{-- UF-10 (GAP-V1-03) - LOGS DE AUTENTICAÇÃO: capacidade promovida no padrao visual V1 --}}
    <details>
        <summary class="formTitlePanel">LOGS DE AUTENTICAÇÃO</summary>
        <div style="font-size:12px;padding:6px 0 8px 0;max-width:780px;">
            <p style="margin:0 0 8px 0;">Histórico completo de tentativas de login, acessos bem-sucedidos e falhas de autenticação.</p>
            <p style="margin:0;">
                <a href="{{ rota_tema('identidade.historico-de-acesso.index') }}" class="formButtonEnviarPanel" style="display:inline-block;width:auto;padding:2px 12px;text-decoration:none;color:#fff;line-height:20px;text-align:center;">
                    ABRIR LOGS DE AUTENTICAÇÃO
                </a>
            </p>
        </div>
        <div style="height:8px;clear:both;"></div>
    </details>

    {{-- UF-10 (GAP-V1-04/05) - LOGS DE MODIFICAÇÃO DE RMA: capacidade promovida no padrao visual V1 --}}
    <details>
        <summary class="formTitlePanel">LOGS DE MODIFICAÇÃO DE RMA</summary>
        <div style="font-size:12px;padding:6px 0 8px 0;max-width:780px;">
            <p style="margin:0 0 8px 0;">Histórico detalhado de alterações nos RMAs, incluindo usuário responsável, ação e atalho para visualização do produto.</p>
            <p style="margin:0;">
                <a href="{{ rota_tema('rmas.historico.index') }}" class="formButtonEnviarPanel" style="display:inline-block;width:auto;padding:2px 12px;text-decoration:none;color:#fff;line-height:20px;text-align:center;">
                    ABRIR LOGS DE MODIFICAÇÃO
                </a>
            </p>
        </div>
        <div style="height:8px;clear:both;"></div>
    </details>

    {{-- UF-14 (GAP-V1-02) - CADASTRAR NOVO USUÁRIO: capacidade promovida no padrao visual V1 --}}
    <details>
        <summary class="formTitlePanel">CADASTRAR NOVO USUÁRIO</summary>
        <div style="font-size:12px;padding:6px 0 8px 0;max-width:780px;">
            <p style="margin:0 0 8px 0;">Cadastrar um novo usuário no sistema com atribuição de permissão de acesso.</p>
            <p style="margin:0;">
                <a href="{{ rota_tema('identidade.usuarios.create') }}" class="formButtonEnviarPanel" style="display:inline-block;width:auto;padding:2px 12px;text-decoration:none;color:#fff;line-height:20px;text-align:center;">
                    ABRIR FORMULÁRIO DE NOVO USUÁRIO
                </a>
            </p>
        </div>
        <div style="height:8px;clear:both;"></div>
    </details>
@endsection
