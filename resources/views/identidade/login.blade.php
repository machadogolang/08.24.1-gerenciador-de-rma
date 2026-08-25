<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entrar — CellSystem RMA</title>
    {{-- Gateway único de login (decisão 2026-08-25, `openspec/changes/temas-v1-v2/design.md`
    "Login-gateway compartilhado"): não é nem TEMA V1 nem TEMA V2 — fonte real
    `http://localhost:8094/` (AdminLTE 2.2.0 `login-page`/`login-box` + Bootstrap 3.3.5,
    confirmado por `curl` na correção desta fase). Tem bundle Vite PRÓPRIO
    (`identidade/login.js`/`login.scss`) — reaproveitar o bundle de um tema seria
    incorreto, essa tela não pertence a nenhum dos dois. --}}
    @vite(['resources/js/identidade/login.js'])
</head>
<body class="login-page">
    <div class="login-box">
        <div class="login-box-body">
            <div class="login-box-msg">
                {{-- Fonte real: `images/logomark.png` do legado (vendorizado aqui em
                `public/images/identidade/logomark.png`, mesmo bytes — não é um recurso
                externo carregado em runtime). --}}
                <img src="{{ asset('images/identidade/logomark.png') }}" alt="CellSystem RMA">
            </div>

            @if ($errors->any())
                <ul class="text-danger">
                    @foreach ($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            @endif

            <form method="POST" action="{{ route('login.store') }}">
                @csrf

                <div class="form-group has-feedback">
                    <input id="email" type="email" name="email" class="form-control"
                        placeholder="E-mail" value="{{ old('email') }}" required autofocus>
                    <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                </div>

                <div class="form-group has-feedback">
                    <input id="password" type="password" name="password" class="form-control"
                        placeholder="Senha" required>
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                </div>

                <div class="row">
                    <div class="col-xs-8"></div>
                    <div class="col-xs-4">
                        <button type="submit" class="btn btn-primary btn-block btn-flat">Iniciar</button>
                    </div>
                </div>
            </form>

            <a href="#">Eu esqueci minha senha</a>
        </div>
    </div>

    {{-- Banner "NÃO É O QUE PROCURA?" — presente no gateway real, linkando para o
    login PRÓPRIO do TEMA V1 (`14.6.1/index.php`, uma segunda função `SignIn()`
    independente, ver design.md). A V3 já decidiu unificar o pós-login: não existe (nem
    deve existir) um segundo formulário/rota de login exclusivo de TEMA V1 — o mesmo
    `identidade.login` sempre respeita `tema_preferido`. O banner é reproduzido aqui
    como elemento visual + atalho de navegação pré-login (não uma segunda tela de
    login): linka de volta para o próprio gateway, que é o equivalente funcional real
    na arquitetura unificada da V3. --}}
    <div class="jumbotron">
        <h5>
            <a href="{{ route('login') }}">
                NÃO É O QUE PROCURA? CLIQUE AQUI PARA ABRIR A FERRAMENTA 14.6.1 DE RMA
            </a>
        </h5>
    </div>
</body>
</html>
