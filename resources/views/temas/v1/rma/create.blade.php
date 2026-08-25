{{-- VIS-V1-002 — fallback funcional de `/rmas/create` (rota direta, fora do painel
inline do header). Mesmo partial que `#JS-Novo` em `temas.v1.layout` — formulário não
duplicado. --}}
@extends('temas.v1.layout')

@section('conteudo')
    <div class="JS-Novo tam" id="JS-Novo">
        @include('temas.v1.rma._form_novo')
    </div>
@endsection
