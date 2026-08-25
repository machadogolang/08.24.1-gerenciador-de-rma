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

## Pendências herdadas de `INV-RMA-06` — resolvidas na implementação (2026-08-25)

1. **Formato de data ambíguo em campos `varchar` do legado** — resolvido com o parser de
   3 tentativas descrito em `INV-RMA-06` (`d/m/Y` → `Y-m-d` → `NULL` + anomalia com o
   valor bruto), implementado em `App\Rma\Infraestrutura\Migracao\ParserDeDataLegado`.
   Nunca lança exceção. A extensão real do problema (quantas linhas caem no caso `NULL`)
   só é conhecida ao rodar contra o banco real — não foi possível nesta sessão (ver
   `log-implementacao-v3.md`, Fase 9, bloqueio de rede documentado).
2. **Ocorrência real de `bd.status = 'retornou'`/`bd.retornou IS NOT NULL`** — o
   migrador **detecta e registra como anomalia** se ocorrer (`ImportarRmas`), sem
   inventar case novo no enum `Status`. Não foi possível confirmar contra dado real
   nesta sessão (mesmo bloqueio de rede); os testes automatizados (fixture) cobrem os
   dois casos (`status='retornou'` e `retornou` preenchido) como anomalia esperada.
3. **Destino de `relatorio.informacaoadicional`** — **decisão tomada por omissão, não
   silenciosa**: opção B aplicada (descartar). A tabela `relatorio` nunca é lida —
   `App\Rma\Infraestrutura\Migracao\ConexaoLegado` não tem (nem nunca terá, a menos que
   revisitado) um método `relatorio()`. O dado permanece recuperável no backup do
   repositório Legacy caso o financeiro precise reconstituí-lo depois.
4. Coordenação de `rmas.valor` — já resolvida antes desta fase começar a ser codificada
   (Fase 5, coluna `valor decimal(10,2) nullable` adicionada). O migrador mapeia
   `bd.valor` → `rmas.valor` sem bloqueio.

## Rastreabilidade

Mapa completo em `docs/arquitetura/INV-RMA-06-estrategia-reconstrucao.md`. Todo
`LEG-RMA-NNN` de origem de dado (não de comportamento, já coberto nas Fases 1-8) fecha
nesta fase.
