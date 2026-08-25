# Proposal — Migração V2→V3

Fase 9 de 10 (ver `docs/arquitetura/INV-RMA-05-arquitetura-proposta.md` §14 e
`docs/arquitetura/INV-RMA-06-estrategia-reconstrucao.md`, o mapa campo-a-campo).

## Por quê

A V3 só é uma continuação real (não uma reescrita) se carregar o dado real da V2 — não
basta o schema/comportamento estarem prontos. Esta fase é o único jeito de os 3 eixos de
paridade (funcional/visual/dados) da Fase 10 poderem ser verificados contra dado
verdadeiro, não sintético.

## Por que só agora tem detalhe file-a-file

Só ficou possível depois de (a) Fases 1-3 implementadas em código (schema real de
destino existe), e (b) leitura direta do schema legado real
(`~/github/08.24.4-legacy-gerenciador-de-rma/db/schema-only.sql`) produzir o mapa
campo-a-campo completo em `INV-RMA-06`. Os enums de domínio que faltam implementar
(`Status`/`Solucao`/`Origem`/`Prioridade`/`StatusDeLancamento`, Fases 4/5) já têm código
fechado — a migração pode ser especificada usando esse desenho mesmo antes do código
delas existir, mas **não pode rodar de verdade** até essas fases estarem implementadas.

## O que entra

- Conexão secundária só-leitura `rma_legacy` (`config/database.php`).
- Comando `php artisan rma:migrar-legado` (opções `--somente`, `--dry-run`, `--forcar`).
- 8 importadores (um por tabela legada), executados na ordem de dependência de FK (ver
  `INV-RMA-05` §14).
- `TabelaDeTraducao` — único ponto do código onde um valor cru do legado é comparado
  por igualdade (todos os outros métodos chamam esta classe).
- `RelatorioDeReconciliacao` — contagem origem×destino, anomalias, conversões
  assistidas; salvo em `storage/app/migracao/`.
- Generalização de `EncontrarOuCriarCliente` (Fase 2) para os outros 3 tipos de
  parceiro, usada **só pelos importadores** (o runtime de criação de RMA da Fase 3 não
  muda de comportamento).
- 2 migrations novas: `numero_legado` em `rmas` (idempotência) + colunas históricas de
  preservação sem regra de negócio dona (`INV-RMA-06` §1.2).

## O que não entra

- Qualquer mudança de comportamento nas Fases 1-8 já implementadas.
- Rodar a migração de verdade contra o banco de produção legado — esta fase entrega o
  migrador testado contra fixture pequena; a execução real fica para quando o usuário
  decidir (ação irreversível de dado, fora do escopo de "implementar o código").

## Pendências herdadas de `INV-RMA-06`, não decididas aqui

1. Formato de data ambíguo em campos `varchar` do legado (extensão real do problema só
   conhecida ao rodar contra o banco real).
2. Ocorrência real de `bd.status = 'retornou'`/`bd.retornou IS NOT NULL` — `LEG-RMA-016`
   afirma código morto por leitura de código, não de dado; decisão só quando/se
   ocorrência real aparecer no relatório de reconciliação.
3. Destino de `relatorio.informacaoadicional` — decisão de produto explícita necessária
   (opção A: preservar num campo; opção B: descartar, dado recuperável no backup do
   repositório Legacy) antes do migrador rodar sobre essa tabela.
4. Coordenação de `rmas.valor` (ausente da migration listada na Fase 5) — bloqueador
   técnico, não decisão de produto, a resolver antes do migrador rodar.

## Rastreabilidade

Mapa completo em `docs/arquitetura/INV-RMA-06-estrategia-reconstrucao.md`. Todo
`LEG-RMA-NNN` de origem de dado (não de comportamento, já coberto nas Fases 1-8) fecha
nesta fase.
