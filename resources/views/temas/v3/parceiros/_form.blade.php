@extends('temas.v3.layout')

@php
    $existe = $registro->exists;
    $completo = $comEnderecoEContato ?? false;
    $action = $existe
        ? rota_tema('parceiros.' . $tipo . '.update', $registro)
        : rota_tema('parceiros.' . $tipo . '.store');
    $cancelarUrl = $existe
        ? rota_tema('parceiros.' . $tipo . '.show', $registro)
        : rota_tema('parceiros.' . $tipo . '.index');
    $nomeSingular = match ($tipo) {
        'clientes' => 'cliente',
        'fornecedores' => 'fornecedor',
        'fabricantes' => 'fabricante',
        'assistencias-tecnicas' => 'assistência técnica',
        default => 'parceiro',
    };
@endphp

@section('conteudo')
    <div class="pagina pagina--formulario">
        <div class="pagina__cabecalho">
            <div class="pagina__cabecalho-conteudo">
                <h1 class="pagina__titulo">
                    {{ $existe ? 'Editar ' . $nomeSingular . ': ' . $registro->nome : 'Novo ' . $nomeSingular }}
                </h1>
                <p class="pagina__resumo">
                    {{ $existe ? 'Atualize as informacoes cadastrais deste parceiro.' : 'Preencha os dados cadastrais para incluir na base operacional.' }}
                </p>
            </div>
            <a href="{{ $cancelarUrl }}" class="botao botao--secundario pagina__cabecalho-acao">
                Voltar
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

        <form method="POST" action="{{ $action }}" class="form-v3" novalidate>
            @csrf
            @if ($existe)
                @method('PUT')
            @endif

            <div class="form-v3__secoes">
                {{-- Identificacao --}}
                <section class="cartao form-v3__secao" aria-labelledby="form-secao-identificacao">
                    <h2 id="form-secao-identificacao" class="form-v3__titulo">Identificacao</h2>
                    <div class="form-v3__grade">
                        <div class="campo">
                            <label class="campo__rotulo" for="nome">Nome *</label>
                            <input class="campo__controle @error('nome') campo__controle--invalido @enderror"
                                id="nome" name="nome" required maxlength="255"
                                value="{{ old('nome', $registro->nome) }}">
                            @error('nome')
                                <span class="campo__erro">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="campo">
                            <label class="campo__rotulo" for="representante">Representante</label>
                            <input class="campo__controle @error('representante') campo__controle--invalido @enderror"
                                id="representante" name="representante" maxlength="255"
                                value="{{ old('representante', $registro->representante) }}">
                            @error('representante')
                                <span class="campo__erro">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="campo">
                            <label class="campo__rotulo" for="cpf_cnpj">CPF / CNPJ</label>
                            <input class="campo__controle @error('cpf_cnpj') campo__controle--invalido @enderror"
                                id="cpf_cnpj" name="cpf_cnpj" maxlength="20"
                                value="{{ old('cpf_cnpj', $registro->cpf_cnpj) }}">
                            @error('cpf_cnpj')
                                <span class="campo__erro">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="campo">
                            <label class="campo__rotulo" for="rgie">RG / IE</label>
                            <input class="campo__controle @error('rgie') campo__controle--invalido @enderror"
                                id="rgie" name="rgie" maxlength="20"
                                value="{{ old('rgie', $registro->rgie) }}">
                            @error('rgie')
                                <span class="campo__erro">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </section>

                {{-- Contato --}}
                <section class="cartao form-v3__secao" aria-labelledby="form-secao-contato">
                    <h2 id="form-secao-contato" class="form-v3__titulo">Contato</h2>
                    <div class="form-v3__grade">
                        <div class="campo">
                            <label class="campo__rotulo" for="telefone">Telefone</label>
                            <input class="campo__controle @error('telefone') campo__controle--invalido @enderror"
                                id="telefone" name="telefone" maxlength="20"
                                value="{{ old('telefone', $registro->telefone) }}">
                            @error('telefone')
                                <span class="campo__erro">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="campo">
                            <label class="campo__rotulo" for="telefone2">Telefone 2</label>
                            <input class="campo__controle @error('telefone2') campo__controle--invalido @enderror"
                                id="telefone2" name="telefone2" maxlength="20"
                                value="{{ old('telefone2', $registro->telefone2) }}">
                            @error('telefone2')
                                <span class="campo__erro">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="campo">
                            <label class="campo__rotulo" for="email">E-mail</label>
                            <input class="campo__controle @error('email') campo__controle--invalido @enderror"
                                type="email" id="email" name="email" maxlength="255"
                                value="{{ old('email', $registro->email) }}">
                            @error('email')
                                <span class="campo__erro">{{ $message }}</span>
                            @enderror
                        </div>

                        @if ($completo)
                            <div class="campo">
                                <label class="campo__rotulo" for="email_secundario">E-mail secundario</label>
                                <input class="campo__controle @error('email_secundario') campo__controle--invalido @enderror"
                                    type="email" id="email_secundario" name="email_secundario" maxlength="255"
                                    value="{{ old('email_secundario', $registro->email_secundario) }}">
                                @error('email_secundario')
                                    <span class="campo__erro">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="campo">
                                <label class="campo__rotulo" for="www">Website (WWW)</label>
                                <input class="campo__controle @error('www') campo__controle--invalido @enderror"
                                    id="www" name="www" maxlength="255"
                                    value="{{ old('www', $registro->www) }}">
                                @error('www')
                                    <span class="campo__erro">{{ $message }}</span>
                                @enderror
                            </div>
                        @endif
                    </div>
                </section>

                {{-- Endereco --}}
                <section class="cartao form-v3__secao" aria-labelledby="form-secao-endereco">
                    <h2 id="form-secao-endereco" class="form-v3__titulo">Endereco</h2>
                    <div class="form-v3__grade">
                        <div class="campo">
                            <label class="campo__rotulo" for="cep">CEP</label>
                            <input class="campo__controle @error('cep') campo__controle--invalido @enderror"
                                id="cep" name="cep" maxlength="10"
                                value="{{ old('cep', $registro->cep) }}">
                            @error('cep')
                                <span class="campo__erro">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="campo">
                            <label class="campo__rotulo" for="logradouro">Logradouro</label>
                            <input class="campo__controle @error('logradouro') campo__controle--invalido @enderror"
                                id="logradouro" name="logradouro" maxlength="255"
                                value="{{ old('logradouro', $registro->logradouro) }}">
                            @error('logradouro')
                                <span class="campo__erro">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="campo">
                            <label class="campo__rotulo" for="numero">Numero</label>
                            <input class="campo__controle @error('numero') campo__controle--invalido @enderror"
                                id="numero" name="numero" maxlength="20"
                                value="{{ old('numero', $registro->numero) }}">
                            @error('numero')
                                <span class="campo__erro">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="campo">
                            <label class="campo__rotulo" for="complemento">Complemento</label>
                            <input class="campo__controle @error('complemento') campo__controle--invalido @enderror"
                                id="complemento" name="complemento" maxlength="100"
                                value="{{ old('complemento', $registro->complemento) }}">
                            @error('complemento')
                                <span class="campo__erro">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="campo">
                            <label class="campo__rotulo" for="bairro">Bairro</label>
                            <input class="campo__controle @error('bairro') campo__controle--invalido @enderror"
                                id="bairro" name="bairro" maxlength="100"
                                value="{{ old('bairro', $registro->bairro) }}">
                            @error('bairro')
                                <span class="campo__erro">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="campo">
                            <label class="campo__rotulo" for="cidade">Cidade</label>
                            <input class="campo__controle @error('cidade') campo__controle--invalido @enderror"
                                id="cidade" name="cidade" maxlength="100"
                                value="{{ old('cidade', $registro->cidade) }}">
                            @error('cidade')
                                <span class="campo__erro">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="campo">
                            <label class="campo__rotulo" for="uf">UF</label>
                            <select class="campo__controle @error('uf') campo__controle--invalido @enderror"
                                id="uf" name="uf">
                                <option value="">Selecione...</option>
                                @foreach (\App\Compartilhado\Uf::cases() as $uf)
                                    <option value="{{ $uf->value }}" @selected(old('uf', $registro->uf?->value) === $uf->value)>
                                        {{ $uf->value }}
                                    </option>
                                @endforeach
                            </select>
                            @error('uf')
                                <span class="campo__erro">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </section>

                {{-- Comercial e Fiscal (se aplicavel) --}}
                @if ($completo)
                    <section class="cartao form-v3__secao" aria-labelledby="form-secao-fiscal">
                        <h2 id="form-secao-fiscal" class="form-v3__titulo">Comercial e Fiscal</h2>
                        <div class="form-v3__grade">
                            <div class="campo">
                                <label class="campo__rotulo" for="frete">Frete</label>
                                <input class="campo__controle @error('frete') campo__controle--invalido @enderror"
                                    id="frete" name="frete" maxlength="100"
                                    value="{{ old('frete', $registro->frete) }}">
                                @error('frete')
                                    <span class="campo__erro">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="campo">
                                <label class="campo__rotulo" for="cfop">CFOP</label>
                                <input class="campo__controle @error('cfop') campo__controle--invalido @enderror"
                                    id="cfop" name="cfop" maxlength="20"
                                    value="{{ old('cfop', $registro->cfop) }}">
                                @error('cfop')
                                    <span class="campo__erro">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </section>
                @endif

                {{-- Observacoes e Garantia --}}
                <section class="cartao form-v3__secao form-v3__secao--larga" aria-labelledby="form-secao-observacoes">
                    <h2 id="form-secao-observacoes" class="form-v3__titulo">Observacoes e Garantia</h2>
                    <div class="campo">
                        <label class="campo__rotulo" for="observacao">Observacoes adicionais</label>
                        <textarea class="campo__controle campo__controle--texto @error('observacao') campo__controle--invalido @enderror"
                            id="observacao" name="observacao" rows="4">{{ old('observacao', $registro->observacao) }}</textarea>
                        @error('observacao')
                            <span class="campo__erro">{{ $message }}</span>
                        @enderror
                    </div>

                    @if ($completo)
                        <div class="campo" style="margin-top: 16px;">
                            <label class="campo__rotulo" for="politica_de_garantia">Politica de garantia</label>
                            <textarea class="campo__controle campo__controle--texto @error('politica_de_garantia') campo__controle--invalido @enderror"
                                id="politica_de_garantia" name="politica_de_garantia" rows="4">{{ old('politica_de_garantia', $registro->politica_de_garantia) }}</textarea>
                            @error('politica_de_garantia')
                                <span class="campo__erro">{{ $message }}</span>
                            @enderror
                        </div>
                    @endif
                </section>
            </div>

            <div class="form-v3__acoes">
                <button type="submit" class="botao">Salvar</button>
                <a href="{{ $cancelarUrl }}" class="botao botao--secundario">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
