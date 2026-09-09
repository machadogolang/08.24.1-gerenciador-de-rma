# Auditoria - tranche inicial V3 e detalhe RMA V1/V2

Data: 2026-09-09. Baseline: HEAD `b2795bd`; origin/main `b7f04d9`; working tree
limpa (inicio da rodada).

## 1. Bugs/achados da tranche T3-08/09/10

| ID | Rota/tela | Sintoma | Evidencia | Classificacao |
|---|---|---|---|---|
| AUD-V3-01 | /v3/rmas em 768px | overflow horizontal do body com tabela desktop | `document.body.scrollWidth > clientWidth` = true somente em 768 | BUG-CONFIRMADO |
| AUD-V3-02 | shell V3 | topo nao oferece Logout nem acesso de sessao | inspecao DOM: somente Menu/marca/nome | BUG-CONFIRMADO (capacidade de sessao minima) |
| AUD-V3-03 | navegacao V3 | apenas Dashboard e RMAs | nav tem 2 itens | DOCUMENTADO-INTENCIONAL |
| AUD-V3-04 | dashboard V3 | sem atalho Novo RMA | botao ausente | DOCUMENTADO-INTENCIONAL (T3-12) |
| AUD-V3-05 | V3 listagem | sem acao Ver/detalhe | linha sem link | DOCUMENTADO-INTENCIONAL (T3-11) |
| AUD-V3-06 | V3 390/1440/1600 | sem overflow; alvos 44px; drawer funcional | metrics de runtime | OK |

Viewports medidos: 390/768/1440/1600. V1/V2 continuam com bundles e menus
inalterados.

## 2. Menu superior/navegacao

- V1 e V2: preservados, sem regressao confirmada.
- V3: Dashboard + RMAs refletem apenas telas existentes. Parceiros, Relatorios,
  Administracao e Perfil entram quando as views V3 existirem (T3-13/15/16).
- Logout deve existir no shell V3 desde ja (rota POST compartilhada).

## 3. Troca entre temas (T3-17)

Estado atual:

- Enum tem V1/V2/V3.
- `alternar()` e binaria; V3 lanca excecao.
- V3 nao aparece em nenhum seletor publico.

Auditoria:

- Persistir V3 como `tema_preferido` do usuario quebra rotas canonicas sem view
  V3 (parceiros, creditos, perfil, etc.) e, portanto, nao pode ser liberado antes
  do T3-GATE.
- Selecao explicita deve existir para V1/V2 (substituir alternar por escolher) e,
  em ambiente local/QA, incluir V3 **somente com escopo de sessao ou flag de QA**
  ate a matriz fechar.

Classificacao: [R]/[DECISAO-PENDENTE]. Implementacao parcial e possivel (seletor
V1/V2 explicito + V3 por sessao local) sem liberar V3 persistente.

## 4. Contrato de acoes/botoes

Varredura amostral em V1/V2/V3:

- V1/V2 seguem o contrato `.acao` ja testado (9/9 no spec de consistencia).
- V3 usa `.botao`/segmentos com 44px, cursor pointer e focus-visible.
- Residuos nao confirmados alem de V3 ainda nao oferecer acoes de detalhe.

## 5. Matriz do detalhe RMA - Legacy V1 x V3 Tema V1

Referencia Legacy: `legacy-source/14.6.1/page/detalhes.php`.

| Campo/grupo | Legacy V1 | V3 Tema V1 | Status |
|---|---|---|---|
| Titulo BOLETIM DE DEFEITO | sim | nao (pagina "RMA #id" com tabela plana) | LAYOUT INCORRETO |
| Numero do BD | sim | `Status/Descricao/...` sem numero visivel destacado | FALTA INFORMACAO/LAYOUT |
| Grade produto (fabricante/descricao/modelo/OS/origem/S-N/empresa) | sim | fabricante/fornecedor/modelo/SN/OS/... sim | PARCIAL |
| P/N, SNID, tempo, cliente | sim | P/N e SNID sim; tempo nao | FALTA INFORMACAO |
| NF entrada/saida cliente e rastreios | sim | rastreio nao exibido | FALTA INFORMACAO |
| NF compra/venda + datas + chaves | sim | nfcompra/nfvenda sim; datas e chaves nao | FALTA INFORMACAO |
| NF remessa/retorno + datas + chaves | sim | nao | FALTA INFORMACAO |
| Destinatario, email/fone/protocolo | sim | protocolo/destinatario parciais | PARCIAL |
| NF devolucao venda, valor, S/N retorno | sim | valor e snretorno? nao na view atual | FALTA INFORMACAO |
| Defeito/observacao | sim | defeito/observacao sim | OK parcial |
| Datas entrada/recebido/encaminhado/concluido | sim | recebido/encaminhado/concluido/arquivado sim | OK |
| Politicas de garantia | sim | nao | FALTA INFORMACAO |
| Solucao/resolucao | sim | solucao sim | OK |

Veredito V1: simplificacao excessiva. Precisa de restauracao guiada pelo Legacy,
sem virar layout moderno.

## 6. Matriz do detalhe RMA - Legacy V2 x V3 Tema V2

Referencia Legacy: `legacy-source/15.8.1/page/rma.php`.

| Campo/grupo | Legacy V2 | V3 Tema V2 | Status |
|---|---|---|---|
| Numero do BD | sim | tabela plana sem numero destacado | LAYOUT INCORRETO |
| S/N, SNID | sim | sim | OK parcial |
| NF compra/venda/remessa/retorno | sim | parcial | FALTA INFORMACAO |
| NF devolucao, entrada/retorno cliente | sim | parcial | FALTA INFORMACAO |
| Destinatario/fornecedor/fabricante/modelo/OS/status | sim | sim | OK parcial |
| Solucao | sim | sim | OK |
| Defeito/observacao | sim | sim | OK parcial |

Veredito V2: tambem simplificado em relacao a fonte; precisa de restauracao de
informacao sem copiar grade do V1.

## 7. Direcao T3-11

- Usar toda informacao confirmada disponivel no agregado/read model.
- Estrutura: cabecalho operacional + secoes (Resumo, Produto, Parceiros/Origem,
  Fiscal, Destinatario/Logistica, Solucao/Credito, Historico).
- Mobile em secoes; desktop denso sem pagina gigante label/valor.
- So incluir campo se o dado existir no dominio.

## 8. Prioridades

- A1 - Reconciliar T3-08/09/10 (feito no plano/OpenSpec).
- A2 - Corrigir AUD-V3-01 (overflow 768) e AUD-V3-02 (logout V3).
- A3 - T3-17 seletor explicito (R/DECISAO-PENDENTE; implementar V1/V2 explicito +
  QA de V3 por sessao em ciclo futuro).
- A4 - Residuos de botoes (sem novos confirmados).
- A5/A6 - Restaurar paridade de detalhe V1/V2 (matriz acima).
- A7 - T3-11 detalhe V3.
