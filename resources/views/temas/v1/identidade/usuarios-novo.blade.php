@extends('temas.v1.layout')

@section('omitirTituloPadrao')
@endsection

@section('conteudo')
    {{-- UF-14 (GAP-V1-02) - Novo Usuario sob o TEMA V1 (14.6.1).
    Uniao funcional com Tema V2 (15.8.1/subp/novo_usuario.php): permite cadastro de usuario
    com nome, e-mail, senha e permissao, mantendo a identidade visual do 14.6.1. --}}
    <p class="title-icone fl" style="margin-left:0px;margin-top:8px;">
        <img src="{{ asset('images/rma/novo_usuario.png') }}" alt="" width="50" height="50">
    </p>
    <p class="title-comicone fl">{{ $titulo }}</p>
    <a href="{{ rota_tema('identidade.usuarios.index') }}" style="float:right;font-size:12px;margin-top:20px;color:#333;text-decoration:none;">&larr; Voltar para Usuários</a>
    <hr class="both">

    @if ($errors->any())
        <ul style="color:#b00;margin:10px 0;">
            @foreach ($errors->all() as $erro)
                <li>{{ $erro }}</li>
            @endforeach
        </ul>
    @endif

    <div style="max-width:600px;margin-top:15px;">
        <form action="{{ rota_tema('identidade.usuarios.store') }}" method="POST">
            @csrf

            <p class="formLabelPanel">NOME COMPLETO:</p>
            <p><input class="formInputPanel" style="width:100%;max-width:450px;" type="text" name="name" value="{{ old('name') }}" placeholder="Nome completo" required></p>
            <div style="height:10px;clear:both;"></div>

            <p class="formLabelPanel">E-MAIL:</p>
            <p><input class="formInputPanel" style="width:100%;max-width:450px;" type="email" name="email" value="{{ old('email') }}" placeholder="email@exemplo.com" required></p>
            <div style="height:10px;clear:both;"></div>

            <p class="formLabelPanel">SENHA (MÍNIMO 8 CARACTERES):</p>
            <p><input class="formInputPanel" style="width:100%;max-width:450px;" type="password" name="password" placeholder="Senha" required></p>
            <div style="height:10px;clear:both;"></div>

            <p class="formLabelPanel">PERMISSÃO:</p>
            <p>
                <select class="novo_formInput" style="width:100%;max-width:450px;height:28px;" name="papel" required>
                    <optgroup label="Permissões padrão">
                        @foreach ($papeisHistoricos as $papel)
                            <option value="{{ $papel->name }}" @selected($papel === \App\Identidade\Dominio\Papel::Operador)>
                                {{ $papel->rotuloDePermissaoLegado() }}
                            </option>
                        @endforeach
                    </optgroup>
                    @if ($papeisModernos->isNotEmpty())
                        <optgroup label="Papeis modernos">
                            @foreach ($papeisModernos as $papel)
                                <option value="{{ $papel->name }}">
                                    {{ $papel->name }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endif
                </select>
            </p>
            <div style="height:15px;clear:both;"></div>

            <p><button class="formButtonEnviarPanel" type="submit">CADASTRAR USUÁRIO</button></p>
        </form>
    </div>
@endsection
