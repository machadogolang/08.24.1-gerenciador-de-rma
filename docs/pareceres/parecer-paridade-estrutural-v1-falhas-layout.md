# Parecer — paridade estrutural do Tema V1

Data: 2026-08-25. Escopo: auditoria independente, somente leitura, de
`INV-RMA-BUG-LAYOUT-falhas.md`, dos dez PNGs locais, da fonte 14.6.1 e do runtime
normalizado Legacy × V3.

## Conclusão

A reabertura da paridade é procedente. O par atual de Concluído comprova diferenças
estruturais, não cosméticas: o V3 não possui o ícone e o cabeçalho interno históricos,
injeta um H1 que não existia, usa a família compacta de linha, perdeu larguras de
coluna e omite o resumo inferior. Cascata e primitivas precisam ser corrigidas antes
de qualquer ajuste isolado de tela.

O plano CP1→CP5 está correto, com quatro ressalvas:

1. screenshots manuais não normalizados provam composição, mas não pixels;
2. `getComputedStyle().fontFamily` prova a pilha declarada, não a fonte rasterizada;
3. a regra CSS `height:30px` produz linha de 33 px no Chrome por borda/padding;
4. alguns estados escritos em Entrada/Encaminhado são `[BUG-LEGADO]`, pois os `SELECTs`
   não fornecem todos os campos usados nas condições.

## Evidência visual

Foram abertos e inspecionados os dez PNGs de
`docs/investigacoes-pendente/INV-RMA-BUG-LAYOUT/`.

- O par `(Legado|V3) Tela de concluido.png`, 1562×1400, confirma ícone/título/hr,
  densidade, H1 artificial e resumo ausente. A escala global do Legacy está cerca de
  1,10× maior; não serve como régua de pixels.
- `(Legado) Tela de Entrada.png` confirma a composição ícone + descrição + tabela
  compacta.
- Os oito prints feitos antes de 15:09/16:02 são evidência histórica anterior aos
  commits `6ddadde` (listagens/menu) e `91a1cfc` (Novo inline); não provam sozinhos o
  estado atual.

## Medição normalizada do Legacy

Chromium headless, zoom 100%, DPR 1, viewport 1440×1000:

| Elemento | Resultado Legacy |
|---|---:|
| `#BASE` largura CSS / caixa | 984 px / 1004 px |
| `#CONTEUDO` / tabela | 984 px / 984 px |
| header `<tr>` | 35 px |
| ícone | 50×50 px |
| `.title-comicone` | Open Sans, 14 px, peso 300 |
| linha de dados `<tr>` | 33 px renderizados |
| `.Tabelinha-TD` | regra/content-height 30 px |
| `<th>` | Open Sans, 11 px, peso 700 |
| `<td>` | Arial, 11 px, peso 300 |
| filho visível do `<td>` | Open Sans quando envolvido pelo `div` histórico |

## Achados de código

- `[CONFIRMADO-14.6.1]` `index.php` carrega `14.6.1.css` e depois `15.9.7.css`.
- `[CONFIRMADO-14.6.1]` Concluídos usa `Tabelinha-TR1/2/3`; Sem Garantia usa TR3.
- `[CONFIRMADO-14.6.1]` Aguardando crédito usa somente `Tabelinha-TR1/2`.
- `[CONFIRMADO-14.6.1]` Entrada/Encaminhado usam a família compacta `Tr*`.
- `[CONFIRMADO-14.6.1]` valores visíveis estão em `div`, frequentemente dentro de
  `a`; isso muda a fonte efetiva e a área clicável.
- `[BUG-LEGADO]` `entrada.php` não seleciona `solucao`, `prioridade`, `marcarestoque`
  nem `entrada`, embora use esses valores ao escolher a classe.
- `[BUG-LEGADO]` `encaminhados.php` seleciona `solucao`, mas omite outros valores usados
  pelas condições.
- `[DÚVIDA]` a primeira zebra varia com o estado procedural `$TR1` que pode vazar entre
  includes. A reconstrução deve ser determinística e registrar a decisão.
- `[CONFIRMADO-14.6.1]` `NF R` existe em Encaminhado/Aguardando; a camada atual possui
  `nf_remessa`, mas o objeto entregue às views ainda não o expõe.

## Parecer sobre fontes

As cópias históricas foram verificadas antes de portar. Os TTF estáticos maiores têm
tabela DSIG truncada e são rejeitados pelo Chromium (`OTS parsing error`); as cópias
webfont também apresentam tamanho interno inconsistente. A fonte variável oficial
vendorizada no V3 carrega sem rede e o CDP confirma `Open Sans` customizada nos glifos
de menu, título e cabeçalho. A aceitação final depende de comparar métricas e fonte
rasterizada, não apenas o nome declarado.

## Critério de aceite

Nenhum checkpoint fecha sem:

- abrir os prints de referência e o print posterior;
- mesma viewport, DPR, zoom e navegador;
- medidas Legacy/V3 registradas no diário operacional;
- CDP para fonte rasterizada dos glifos relevantes;
- divergência perceptível ausente ou explicitamente classificada;
- teste focado, build e suíte proporcionalmente verdes.

O checklist e o diário de comparação ficam em
`docs/produto/plano-execucao-paridade-estrutural-v1.md`, incorporado ao plano existente.
