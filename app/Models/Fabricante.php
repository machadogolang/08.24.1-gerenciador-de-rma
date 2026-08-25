<?php

namespace App\Models;

use App\Parceiros\Concerns\TemEnderecoEContato;
use Database\Factories\FabricanteFactory;
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
class Fabricante extends Model
{
    /** @use HasFactory<FabricanteFactory> */
    use HasFactory, TemEnderecoEContato;
}
