@php
    $atual = $relatorioAtual ?? 'painel';
    $itens = [
        ['slug' => 'painel', 'rotulo' => 'Painel Geral', 'rota' => 'rmas.relatorios.index'],
        ['slug' => 'rcd', 'rotulo' => '[RCD] Créditos Disponíveis', 'rota' => 'rmas.relatorios.rcd'],
        ['slug' => 'rpec', 'rotulo' => '[RPEC] Contagem de Estoque', 'rota' => 'rmas.relatorios.rpec'],
        ['slug' => 'rmpe', 'rotulo' => '[RMPE] Produtos Encaminhados', 'rota' => 'rmas.relatorios.rmpe'],
    ];
@endphp

<nav class="segmentos" aria-label="Relatorios do sistema" style="margin-bottom: 24px;">
    @foreach ($itens as $item)
        <a class="segmento"
            href="{{ rota_tema($item['rota']) }}"
            @if ($item['slug'] === $atual) aria-current="true" @endif>
            {{ $item['rotulo'] }}
        </a>
    @endforeach
</nav>
