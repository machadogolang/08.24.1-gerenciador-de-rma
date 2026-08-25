<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entrar — CellSystem RMA</title>
    {{-- Gateway único de login (decisão 2026-08-25): não é nem TEMA V1 nem TEMA V2 —
    fonte real `http://localhost:8094/` (login-gateway compartilhado, Bootstrap 3 +
    `pattern/15.9.7.css`, SEM CSS de tema próprio). Reaproveita o bundle V2 só porque é
    ele que carrega o Bootstrap 3 self-hostado (grid/`.form-control`) já usado aqui —
    não implica que esta tela pertença ao TEMA V2. --}}
    @vite(['resources/js/temas/v2.js'])
    <style>
        body.login-page {
            background-color: #262626;
        }
        .login-box {
            width: 360px;
            margin: 60px auto;
        }
        .login-box-body {
            background: #fff;
            padding: 20px;
            border-top: 3px solid #224A5D;
        }
        .login-box-msg {
            text-align: center;
            padding-bottom: 15px;
            font-weight: 300;
        }
    </style>
</head>
<body class="login-page">
    <div class="login-box">
        <div class="login-box-body">
            <div class="login-box-msg">
                <h3>CellSystem RMA</h3>
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

                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input id="email" type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Senha</label>
                    <input id="password" type="password" name="password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-flat">Entrar</button>
            </form>
        </div>
    </div>
</body>
</html>
