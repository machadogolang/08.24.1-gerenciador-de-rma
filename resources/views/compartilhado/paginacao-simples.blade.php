@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navegação entre páginas" class="paginacao-container">
        <ul class="paginacao-lista">
            @if ($paginator->onFirstPage())
                <li class="paginacao-item paginacao-desabilitado" aria-disabled="true">
                    <span>&laquo; Anterior</span>
                </li>
            @else
                <li class="paginacao-item">
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="paginacao-link">&laquo; Anterior</a>
                </li>
            @endif

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
