# Proposal — Configuração de admin (Trilha B, Fase A)

Fase A das duas evoluções de Trilha B detalhadas nesta rodada (ver
`docs/produto/roadmap-evolucao-admin-arquivos.md`). Não é fase da Trilha A — não tem
número `Fase N de 10`. Depende da baseline de paridade da Trilha A estar validada
(`INV-RMA-05` §15, Fase 10/QA) antes de qualquer linha de código de produto.

## Por quê

Três parâmetros de negócio reais, confirmados por leitura direta do código já
implementado nas Fases 1-9, estão hoje hardcoded ou só editáveis via `.env`/redeploy:

1. **Destinatário de notificação de conclusão**
   (`app/Rma/Aplicacao/EnviarNotificacaoDeConclusao.php:17`,
   `config('rma.notificacoes.conclusao')`).
2. **Threshold de urgência R$75** (RN-12,
   `app/Rma/Aplicacao/Alertas/UrgenciaPorThreshold.php:41`, literal `75.00` na query).
3. **Cidade "PORTO ALEGRE" hardcoded**
   (`app/Rma/Aplicacao/ConsolidarFretePorCidade.php:21`, `private const CIDADE`).

Nenhum dos três é um achado de arqueologia do legado — o legado nunca teve tela de
configuração administrativa (`INV-RMA-09` §0). É evolução de produto pura, registrada
como `EVO-CONF-001` em `docs/produto/backlog-evolutivo.md`, com investigação e
proporcionalidade já fechadas em `docs/arquitetura/INV-RMA-09-arquivos-e-configuracao-
admin.md` Parte B. Esta fase detalha, arquivo por arquivo, como codificar.

## O que entra

- Módulo novo `App\Configuracao`, fronteira `Dominio/Aplicacao/Infraestrutura` própria
  (proporcional — 3 objetos de valor com regra de validação real, mesmo critério de
  `INV-RMA-05` §2 que já deu fronteira completa a `Rma` e negou a `Parceiros`).
- Um objeto de valor `readonly` por configuração, com autoria/data de publicação como
  campo do próprio objeto (padrão `publicar`/`efetivo` do CONAHOM, `INV-RMA-09` §B.1).
- Um caso de uso de leitura "efetivo" por configuração, chamado pelos 3
  Controllers/Aplicacao existentes (`EnviarNotificacaoDeConclusao`,
  `UrgenciaPorThreshold`, `ConsolidarFretePorCidade`) **sem que eles passem a depender
  do módulo `Configuracao` existir** — ver `design.md` §Desacoplamento.
- Uma tela única de admin (`/admin/configuracoes`), scaffold Tailwind padrão do
  Laravel, sem fidelidade a nenhum tema V1/V2/V3.
- Autorização: `Papel::podeGerenciarUsuarios()` — ver `design.md` §Autorização (não
  novo método no enum).

## O que não entra

- Hub de múltiplas seções estilo CONAHOM (identidade institucional, e-mail/SMTP,
  comunicação, carteira) — só 1 tela com os 3 campos reais (`INV-RMA-09` §B.3).
- Segredo separado do valor configurável (`SegredoDoEnvioDeEmail`-like) — nenhum dos 3
  candidatos é sensível.
- `EVO-DOM-003` (política de garantia por fabricante) — candidato futuro ao mesmo
  módulo, não especificado em detalhe agora (`INV-RMA-09` §B.5), fora de escopo desta
  fase.
- Qualquer código de produto antes da baseline de paridade da Trilha A validada.

## Rastreabilidade

| Configuração | Consumidor atual (Fases 5/7) | Fallback preservado |
|---|---|---|
| Destinatário de notificação | `EnviarNotificacaoDeConclusao.php:17` | `config('rma.notificacoes.conclusao')` (`.env`) |
| Threshold de urgência R$75 | `UrgenciaPorThreshold.php:41` | `75.00` (constante) |
| Cidade de consolidação de frete | `ConsolidarFretePorCidade.php:21` | `'PORTO ALEGRE'` (constante) |

## Critério de pronto

Ver `docs/produto/roadmap-evolucao-admin-arquivos.md` §Critério de pronto para começar
a codificar — resumo: Fase 10/QA da Trilha A concluída e commitada, nenhum teste das
Fases 5/7 quebrado por esta fase (roda `sail test` completo antes e depois).
