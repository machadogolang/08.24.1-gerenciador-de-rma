@extends('temas.v2.layout')

@section('conteudo')
    @include('temas.v2.identidade._menu_controle', ['subpAtual' => 'novo_usuario'])

    {{-- PAR15-USR-007 - "Novo usuario" do TEMA V2, fonte `15.8.1/subp/novo_usuario.php`
    (nome, e-mail, senha, permissao). Implementacao moderna: POST + CSRF + validacao +
    Policy + vinculo `company_user` do tenant ativo; sem SHA1 nem validacao insegura. --}}
    <ol class="breadcrumb submenutitulo">
        <li class="fl" style="margin-top:0px;">Novo usuario</li>
        <li style="clear:both;"></li>
    </ol>

    @if ($errors->any())
        <ul class="text-danger">
            @foreach ($errors->all() as $erro)
                <li>{{ $erro }}</li>
            @endforeach
        </ul>
    @endif

    <div class="col-md-6">
        <form action="{{ rota_tema('identidade.usuarios.store') }}" method="post">
            @csrf
            <div class="form-group">
                <label style="float:left;color:#EEE;" for="name">Quem voce quer cadastrar?</label>
                <input style="clear:both;" type="text" class="form-control Input1" name="name" id="name" placeholder="Nome completo" required>
            </div>
            <div class="form-group">
                <label style="float:left;color:#EEE;" for="email">E-mail</label>
                <input style="clear:both;" type="email" class="form-control Input1" name="email" id="email" placeholder="Email" required>
            </div>
            <div class="form-group">
                <label style="float:left;color:#EEE;" for="password">Senha</label>
                <input style="clear:both;" type="password" class="form-control Input1" name="password" id="password" placeholder="Senha (minimo 8)" required>
            </div>
            <div class="form-group">
                <label style="float:left;color:#EEE;" for="papel">Permissao</label>
                <select style="clear:both;" class="form-control Input1" name="papel" id="papel" required>
                    @foreach (\App\Identidade\Dominio\Papel::cases() as $papel)
                        <option value="{{ $papel->name }}" @selected($papel === \App\Identidade\Dominio\Papel::Leitura)>
                            {{ $papel->rotuloDePermissaoLegado() }} ({{ $papel->name }})
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-default" style="float:right;">Cadastrar</button>
        </form>
    </div>
    <div style="clear:both;"></div>
@endsection
