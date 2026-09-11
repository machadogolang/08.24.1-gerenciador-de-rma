@extends('temas.v3.layout')

@section('conteudo')
    <div class="pagina pagina--formulario">
        <div class="pagina__cabecalho">
            <div class="pagina__cabecalho-conteudo">
                <h1 class="pagina__titulo">Novo Usuário</h1>
                <p class="pagina__resumo">
                    Cadastre um novo operador ou administrador na plataforma.
                </p>
            </div>
            <a href="{{ rota_tema('identidade.usuarios.index') }}" class="botao botao--secundario pagina__cabecalho-acao">
                Voltar para usuários
            </a>
        </div>

        @if ($errors->any())
            <div class="cartao" style="border-left: 4px solid #b3261e; background-color: #fff8f7; margin-bottom: 24px; padding: 16px;">
                <h2 style="font-size: 1rem; color: #b3261e; margin: 0 0 8px;">Por favor, verifique os campos destacados:</h2>
                <ul style="margin: 0; padding-left: 20px; color: #b3261e;">
                    @foreach ($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ rota_tema('identidade.usuarios.store') }}" class="form-v3" novalidate>
            @csrf

            <div class="form-v3__secoes">
                <section class="cartao form-v3__secao" aria-labelledby="form-secao-credenciais">
                    <h2 id="form-secao-credenciais" class="form-v3__titulo">Credenciais e Acesso</h2>
                    <div class="form-v3__grade">
                        <div class="campo">
                            <label class="campo__rotulo" for="name">Nome completo *</label>
                            <input class="campo__controle @error('name') campo__controle--invalido @enderror"
                                id="name" name="name" required maxlength="255"
                                value="{{ old('name') }}" placeholder="Ex.: Maria Souza">
                            @error('name')
                                <span class="campo__erro">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="campo">
                            <label class="campo__rotulo" for="email">E-mail corporativo *</label>
                            <input class="campo__controle @error('email') campo__controle--invalido @enderror"
                                type="email" id="email" name="email" required maxlength="255"
                                value="{{ old('email') }}" placeholder="Ex.: maria@empresa.com">
                            @error('email')
                                <span class="campo__erro">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="campo">
                            <label class="campo__rotulo" for="password">Senha inicial *</label>
                            <input class="campo__controle @error('password') campo__controle--invalido @enderror"
                                type="password" id="password" name="password" required minlength="8"
                                placeholder="Mínimo 8 caracteres">
                            @error('password')
                                <span class="campo__erro">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="campo">
                            <label class="campo__rotulo" for="papel">Papel / Permissão *</label>
                            <select class="campo__controle @error('papel') campo__controle--invalido @enderror"
                                id="papel" name="papel" required>
                                @foreach ($papeisHistoricos as $papel)
                                    <option value="{{ $papel->name }}" @selected(old('papel', \App\Identidade\Dominio\Papel::Leitura->name) === $papel->name)>
                                        {{ $papel->rotuloDePermissaoLegado() }}
                                    </option>
                                @endforeach
                                @if ($papeisModernos->isNotEmpty())
                                    @foreach ($papeisModernos as $papel)
                                        <option value="{{ $papel->name }}" @selected(old('papel') === $papel->name)>
                                            {{ $papel->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('papel')
                                <span class="campo__erro">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </section>
            </div>

            <div class="form-v3__acoes">
                <button type="submit" class="botao">Cadastrar usuário</button>
                <a href="{{ rota_tema('identidade.usuarios.index') }}" class="botao botao--secundario">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
