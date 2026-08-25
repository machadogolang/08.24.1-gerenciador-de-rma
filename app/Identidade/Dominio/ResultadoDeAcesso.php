<?php

namespace App\Identidade\Dominio;

enum ResultadoDeAcesso
{
    case Permitido;
    case Negado;
    case Bloqueado;
}
