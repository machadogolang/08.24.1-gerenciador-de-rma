<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('codigo', 'Erro') - CellSystem RMA</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #12283a;
            color: #dbe7f0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .erro-card {
            background-color: #1d364d;
            border: 1px solid #2a4b67;
            border-radius: 8px;
            max-width: 520px;
            width: 100%;
            padding: 32px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            text-align: center;
        }
        .erro-codigo {
            font-size: 3.5rem;
            font-weight: 700;
            color: #f6e3a3;
            line-height: 1;
            margin-bottom: 12px;
            letter-spacing: -0.02em;
        }
        .erro-titulo {
            font-size: 1.35rem;
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 16px;
        }
        .erro-mensagem {
            font-size: 0.95rem;
            line-height: 1.6;
            color: #b0c4d4;
            margin-bottom: 28px;
        }
        .erro-acoes {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .botao {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 0 20px;
            border-radius: 6px;
            font-size: 0.95rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            border: 0;
            transition: background-color 0.15s ease;
        }
        .botao--primario {
            background-color: #145a9e;
            color: #ffffff;
        }
        .botao--primario:hover {
            background-color: #1a6fbe;
        }
        .botao--secundario {
            background-color: transparent;
            border: 1px solid #486a87;
            color: #dbe7f0;
        }
        .botao--secundario:hover {
            background-color: rgba(255, 255, 255, 0.08);
            border-color: #dbe7f0;
        }
    </style>
</head>
<body>
    <main class="erro-card">
        <div class="erro-codigo">@yield('codigo')</div>
        <h1 class="erro-titulo">@yield('titulo')</h1>
        <p class="erro-mensagem">@yield('mensagem')</p>
        <div class="erro-acoes">
            <a href="{{ url('/') }}" class="botao botao--primario">Voltar à Página Inicial</a>
            @hasSection('acao_secundaria')
                @yield('acao_secundaria')
            @else
                <button type="button" class="botao botao--secundario" onclick="window.history.back()">Página Anterior</button>
            @endif
        </div>
    </main>
</body>
</html>
