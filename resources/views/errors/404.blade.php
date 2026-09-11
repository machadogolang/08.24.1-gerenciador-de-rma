@extends('errors.layout')

@section('codigo', '404')
@section('titulo', 'Página ou Registro Não Encontrado')
@section('mensagem')
    O endereço acessado não existe ou o registro solicitado (RMA, usuário ou parceiro) não foi localizado no banco de dados.
@endsection
