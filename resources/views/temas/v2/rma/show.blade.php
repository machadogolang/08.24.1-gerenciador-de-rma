@extends('temas.v2.layout')

@php
    // PAR-V2-DETAIL-02 - detalhe RMA V2 e formulario operacional editavel como o
    // `15.8.1/page/rma.php`, por cima dos casos de uso modernos. O partial
    // `_form_detalhe` carrega os grupos/controles; show e edit continuam rotas
    // separadas (a edicao dedicada continua existindo como fallback).
    $l = $legado ?? [];
    $fabricanteNome = $fabricante?->nome ?? '';
    $fornecedorNome = $fornecedor?->nome ?? '';
    $clienteNome = $cliente?->nome ?? '';
    $destinatarioNome = $destinatario['nome'] ?? '';
    $solucaoNome = $registro->solucao?->value ?? '';
    $prioridadeNome = match ($registro->prioridade) {
        \App\Rma\Dominio\Prioridade::Alta => 'Alta',
        \App\Rma\Dominio\Prioridade::Media => 'Normal',
        default => 'Baixa',
    };
    $lancamentoNome = match ($registro->lancadoretorno) {
        \App\Rma\Dominio\StatusDeLancamento::Pendente => 'PENDENTE',
        \App\Rma\Dominio\StatusDeLancamento::NfDevolucao => 'NF DE DEVOLUCAO',
        \App\Rma\Dominio\StatusDeLancamento::SemMovimentacao => 'SEM MOVIMENTACAO',
        \App\Rma\Dominio\StatusDeLancamento::Nao => 'NAO',
        \App\Rma\Dominio\StatusDeLancamento::Sim => 'SIM',
        default => '',
    };
    $politica = $politicaDeGarantia ?? null;
    $rotuloPolitica = match ($politica['tipo'] ?? null) {
        'destinatario' => 'Politica de Garantia com ' . ($politica['nome'] ?? '') . ' (Destinatario)',
        'fabricante' => 'Politica de Garantia com ' . ($politica['nome'] ?? '') . ' (Fabricante)',
        'fornecedor' => 'Politica de Garantia com ' . ($politica['nome'] ?? '') . ' (Fornecedor)',
        default => '',
    };
    $numeroExibicaoV2 = $numeroExibicao ?? $registro->id;
@endphp

@section('conteudo')
    @if (session('status'))
        <p class="centrodeavisos">{{ session('status') }}</p>
    @endif

    @if ($errors->any())
        <ul class="text-danger">
            @foreach ($errors->all() as $erro)
                <li>{{ $erro }}</li>
            @endforeach
        </ul>
    @endif

    <div class="detalhe-rma-v2">
        @include('temas.v2.rma._form_detalhe')
    </div>

@endsection
