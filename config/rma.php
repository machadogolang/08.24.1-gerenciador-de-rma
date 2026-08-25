<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Notificações do módulo RMA
    |--------------------------------------------------------------------------
    |
    | `LEG-RMA-045` — o legado envia o e-mail de conclusão sempre para o mesmo
    | endereço hardcoded (`ezequiel()`). Aqui o destinatário é configurável via
    | `.env` (correção de manutenibilidade invisível ao comportamento percebido).
    |
    */

    'notificacoes' => [
        'conclusao' => env('RMA_NOTIFICACAO_CONCLUSAO'),
    ],

];
