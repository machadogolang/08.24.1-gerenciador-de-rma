# Inventário de rotas e resíduos (P9) - 2026-09-10

Baseline: `1274ebc`. Método: `php artisan route:list --except-vendor` (150 rotas) +
leitura de `routes/{web,tema-v1,tema-v2,tema-v3}.php` e `package.json`.

## 1. Rotas por família

| Família | Rotas | Observação |
|---|---|---|
| Sessão | `login` (GET/POST), `logout` (POST), `/` e `/dashboard` (redirect) | fora de prefixo de tema |
| Núcleo RMA | `rmas` (index/store/show/update/create/edit) | canônicas; resolvem o tema por `tema_preferido` |
| Ciclo de vida | `rmas/{rma}/receber|encaminhar|concluir|arquivar|reverter|solucao` | POST + CSRF + Policy |
| Listagens por status | `rmas-entrada`, `rmas-encaminhados`, `rmas-aguardando-credito`, `rmas-concluidos` | tema V1 |
| Financeiro/relatórios | `rmas-credito` (+`/marcar`), `rmas-relatorios/rcd|rpec|rmpe` | folha de impressão validada na ONDA F |
| Auditoria/painéis | `rmas-historico`, `rmas-controle`, `rmas-alertas`, `historico-de-acesso` | |
| Logística | `rmas-logistica/frete-porto-alegre`, `rmas/{rma}/boletins-relacionados` | |
| Identidade | `usuarios` (+`update`, `resetar-senha`), `perfil` (+`senha` GET/PUT, `anotacao` PUT), `anotacoes`, `tema/alternar` | `anotacoes`/`perfil/senha` são superfícies V2 (PAR-RES-E-04) |
| Parceiros | 4 recursos completos (clientes/fabricantes/fornecedores/assistências) | Policy por recurso (UX-001 na listagem) |
| Prefixo V1 | 5 recursos de parceiros + `rmas` + `usuarios` + `perfil` | QA/paridade |
| Prefixo V2 | idem V1 + `anotacoes` + `perfil/senha` | QA/paridade |
| Prefixo V3 | `v3` (dashboard), `v3/rmas`, `v3/rmas/novo`, `v3/rma/{id}`, `v3/rma/{id}/editar` (+store/update) | **oculto**: T3-GATE pendente |

## 2. Resíduos e conclusões

- **Sem rotas órfãs**: toda rota canônica tem controller; `/` e `/dashboard` só
  redirecionam. As rotas de tema (`/v1`, `/v2`) existem para QA e paridade e reusam
  os mesmos controllers.
- **Tema V3** é o único conjunto "residual planejado": as rotas existem e funcionam,
  mas o tema continua oculto e não selecionável até `T3-GATE` (T3-13..T3-20
  pendentes). Nada em `routes/web.php` expõe V3 ao seletor público.
- **Plugins/bibliotecas**: `package.json` mantém apenas `bootstrap@3.3.5` e
  `jquery@4` (paridade do Tema V2) + Tailwind 4 e Vite para o V3. AdminLTE, iCheck e
  `lib/`/`framework/` do legado **não** foram portados (sem consumidor no V3); o
  inventário técnico do backup continua em `docs/legado/inventario-tecnico-15.9.7.md`.
- **Views órfãs**: removidas em UI-07.2, com guarda
  `tests/Feature/Temas/ViewsOrfasRemovidasTest`. Nenhuma nova candidata foi criada
  nesta rodada (as superfícies novas - `anotacoes`, `perfil/senha` - têm rota,
  controller e teste).
- **Rotas duplicadas por tema**: nenhuma duplica lógica; `rota_tema()` só escolhe o
  prefixo quando a rota atual está sob `/v1`/`/v2`.
