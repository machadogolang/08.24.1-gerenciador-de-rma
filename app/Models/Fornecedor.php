<?php

namespace App\Models;

use App\Parceiros\Concerns\TemEnderecoEContato;
use Database\Factories\FornecedorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'nome',
    'representante',
    'cpf_cnpj',
    'email',
    'email_secundario',
    'telefone',
    'telefone2',
    'cep',
    'logradouro',
    'numero',
    'complemento',
    'bairro',
    'cidade',
    'uf',
    'www',
    'frete',
    'cfop',
    'observacao',
    'politica_de_garantia',
])]
class Fornecedor extends Model
{
    /** @use HasFactory<FornecedorFactory> */
    use HasFactory, TemEnderecoEContato;

    protected $table = 'fornecedores';
}
