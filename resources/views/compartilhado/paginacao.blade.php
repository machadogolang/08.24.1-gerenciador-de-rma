@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navegação entre páginas" class="paginacao-container">
        <div class="paginacao-info">
            Exibindo <strong>{{ $paginator->firstItem() }}</strong> a <strong>{{ $paginator->lastItem() }}</strong> de <strong>{{ $paginator->total() }}</strong> registros
        </div>

        <ul class="paginacao-lista">
            {{-- Link Página Anterior --}}
            @if ($paginator->onFirstPage())
                <li class="paginacao-item paginacao-desabilitado" aria-disabled="true">
                    <span>&laquo; Anterior</span>
                </li>
            @else
                <li class="paginacao-item">
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="paginacao-link">&laquo; Anterior</a>
                </li>
            @endif

            {{-- Elementos de Paginação (números e reticências) --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="paginacao-item paginacao-desabilitado" aria-disabled="true">
                        <span>{{ $element }}</span>
                    </li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="paginacao-item paginacao-atual" aria-current="page">
                                <span>{{ $page }}</span>
                            </li>
                        @else
                            <li class="paginacao-item">
                                <a href="{{ $url }}" class="paginacao-link">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Link Próxima Página --}}
            @if ($paginator->hasMorePages())
                <li class="paginacao-item">
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="paginacao-link">Próximo &raquo;</a>
                </li>
            @else
                <li class="paginacao-item paginacao-desabilitado" aria-disabled="true">
                    <span>Próximo &raquo;</span>
                </li>
            @endif
        </ul>
    </nav>
@endif
