<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Previa do Tema V3 (QA/desenvolvimento)
    |--------------------------------------------------------------------------
    |
    | T3-17 / adendo de 2026-09-10 (bloco C) - enquanto o T3-GATE nao libera o
    | Tema V3 no seletor publico, o V3 so e alcancavel pela rota prefixada
    | (`/v3/...`). Esta flag habilita apenas uma ENTRADA discreta ("Previa V3")
    | nos shells V1 e V2, util para QA manual.
    |
    | Ela NUNCA grava `tema_preferido`: a preferencia persistida segue V1/V2 e o
    | usuario volta a ela pela acao "Voltar ao sistema" do shell V3. Com a flag
    | desligada (default), nenhum usuario normal ve qualquer entrada para o V3.
    |
    | `env()` fica somente aqui; as views leem `config('temas.v3_preview_enabled')`.
    |
    */

    'v3_preview_enabled' => (bool) env('RMA_TEMA_V3_PREVIEW', false),

];
