<?php

namespace App\Identidade\Aplicacao;

use RuntimeException;

final class SenhaAtualIncorretaException extends RuntimeException
{
    protected $message = 'A senha atual informada está incorreta.';
}
