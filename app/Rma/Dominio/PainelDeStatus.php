<?php

namespace App\Rma\Dominio;

/**
 * VIS-V1-001 — os 4 atalhos de navegação superior do TEMA V1 legado
 * (`legacy-source/14.6.1/index.php:162-168`), cada um abrindo uma listagem filtrada
 * própria (`page/{entrada,encaminhados,aguardandocredito,concluidos}.php`). Não é
 * `Status` sozinho porque "Aguardando credito" filtra por `solucao`, não por `status`.
 *
 * CP23 (paridade visual V2) — `EntradaSomente`/`RecebidoSomente` adicionados para as
 * abas Entrada/Recebido do TEMA V2 (`15.8.1/page/{entrada,recebido}.php`), que ao
 * contrário do atalho `Entrada` do TEMA V1 NÃO combinam os dois status na mesma
 * listagem — são duas telas históricas distintas. `descricao()` não cobre os dois
 * casos novos porque o TEMA V2 não usa esse texto de contexto (usa o texto literal
 * de `page/*.php` diretamente na view, achado do CP20/CP23).
 */
enum PainelDeStatus
{
    case Entrada;
    case Encaminhados;
    case AguardandoCredito;
    case Concluidos;
    case EntradaSomente;
    case RecebidoSomente;

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
            self::EntradaSomente, self::RecebidoSomente => '',
        };
    }
}
