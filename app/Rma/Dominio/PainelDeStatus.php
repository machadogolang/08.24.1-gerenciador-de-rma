<?php

namespace App\Rma\Dominio;

/**
 * VIS-V1-001 — os 4 atalhos de navegação superior do TEMA V1 legado
 * (`legacy-source/14.6.1/index.php:162-168`), cada um abrindo uma listagem filtrada
 * própria (`page/{entrada,encaminhados,aguardandocredito,concluidos}.php`). Não é
 * `Status` sozinho porque "Aguardando credito" filtra por `solucao`, não por `status`.
 */
enum PainelDeStatus
{
    case Entrada;
    case Encaminhados;
    case AguardandoCredito;
    case Concluidos;

    /**
     * Texto de contexto real do legado (`page/*.php`, `<p class="title-comicone">`).
     */
    public function descricao(): string
    {
        return match ($this) {
            self::Entrada => 'Os bds recebidos abaixo estao em analise ou algo pendente documentacao p/ envio',
            self::Encaminhados => 'Os produtos abaixo ja estao encaminhados ao responsavel pela garantia',
            self::AguardandoCredito => 'Os produtos abaixo estao aguardando processo p/ gerar o credito',
            self::Concluidos => 'Os produtos abaixo ja retornaram e estao com o processo de encaminhamento de rma concluido',
        };
    }
}
