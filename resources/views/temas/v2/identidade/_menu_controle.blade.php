{{-- PAR15-AUD-001/005 - menu/breadcrumb historico do Controle do TEMA V2, fonte
`15.8.1/inc/menu_controle.php`: Logs de autenticacao, Logs de modificacao, Alterar
senha, Novo usuario, Usuarios. Reutiliza endpoints modernos; so a organizacao e do
Legacy. O item "Novo usuario" so aparece quando a rota existir (para nao gerar link
quebrado antes do equivalente moderno). --}}
@php
    $subpAtual = $subpAtual ?? '';

    $urlNovoUsuario = null;
    try {
        $urlNovoUsuario = rota_tema('identidade.usuarios.create');
    } catch (\Throwable) {
        $urlNovoUsuario = null;
    }

    $itens = [
        ['id' => 'logs_de_autenticacao', 'rotulo' => 'Logs de autenticacao', 'url' => rota_tema('identidade.historico-de-acesso.index')],
        ['id' => 'logs_de_modificacao', 'rotulo' => 'Logs de modificacao', 'url' => rota_tema('rmas.historico.index')],
        ['id' => 'senha', 'rotulo' => 'Alterar senha', 'url' => rota_tema('identidade.perfil.senha')],
    ];

    if ($urlNovoUsuario !== null) {
        $itens[] = ['id' => 'novo_usuario', 'rotulo' => 'Novo usuario', 'url' => $urlNovoUsuario];
    }

    $itens[] = ['id' => 'usuarios', 'rotulo' => 'Usuarios', 'url' => rota_tema('identidade.usuarios.index')];
@endphp

<div class="menu-subp">
    <ol class="breadcrumb">
        @foreach ($itens as $item)
            <li>
                <a href="{{ $item['url'] }}" @if ($subpAtual === $item['id']) class="active" @endif>{{ $item['rotulo'] }}</a>
            </li>
        @endforeach
    </ol>
</div>
