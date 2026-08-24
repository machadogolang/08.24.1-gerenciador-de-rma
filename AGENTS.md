# Agentes — CellSystem RMA

Reconstrução do CellSystem RMA como produto moderno, em duas trilhas separadas:

- **Trilha A (agora):** restauração fiel do produto 15.9.7 — mesmas regras, fluxos,
  identidade visual, com arquitetura/segurança/tecnologia atuais (linha CONAHOM,
  proporcional ao domínio).
- **Trilha B (depois):** evolução — SaaS multiempresa, automações, IA. Registrada em
  `docs/produto/backlog-evolutivo.md`, nunca implementada durante a Trilha A.

## Regras fixas

- **A conversa não é fonte de verdade — o repositório é.** Todo achado de arqueologia
  entra imediatamente em `docs/legado/` ou `docs/investigacoes-pendente/`, nunca só na
  resposta ao usuário.
- **Fonte histórica principal:** o backup `Sistema de RMA CellSystem 15.9.7`
  (`~/github/_rma-arqueologia/backup-15.9.7/`, SHA-256 registrado em
  `docs/legado/inventario-tecnico-15.9.7.md`). **Nunca** alterar/mover o `.tar.gz`
  original em `~/Downloads`, nunca versioná-lo, nunca reproduzir credenciais nele
  encontradas.
- **Nunca** alterar os repositórios históricos (`14.10.2`, `15.10.1` em
  `~/github/_rma-arqueologia/`).
- 15.9.7 é um **container** com dois apps coexistentes (14.6.1, 15.8.1) + camada
  compartilhada (`metodo.php`) — não uma linhagem sequencial provada. Ver
  `docs/legado/matriz-comparacao-apps-rma.md` antes de assumir qualquer ordem histórica.
- Toda regra de negócio preservada da Trilha A precisa de rastreabilidade por versão
  (14.6.1 / 15.8.1 / camada compartilhada), usando as tags
  `[CONFIRMADO-*]`/`[HIPOTESE-HISTORICA]`/`[BUG-LEGADO]`/`[CODIGO-MORTO]`/`[DÚVIDA]`.
- Não inventar comportamento histórico — o que não está comprovado no código fica
  `[DÚVIDA]`, nunca vira decisão.
- **Nunca** `git push`, PR ou merge remoto sem autorização explícita nesta conversa.
- Commits locais pequenos e coerentes por checkpoint (`#ARQ-RMA - ...`).

## Onde está cada coisa

- `docs/legado/inventario-tecnico-15.9.7.md` — backup, tecnologias, bibliotecas, código
  morto/duplicado.
- `docs/legado/matriz-comparacao-apps-rma.md` — arquitetura multi-app, comparação
  funcional 14.6.1 × 15.8.1.
- `docs/legado/cronologia-rma.md` — pistas de datação (hipótese, não fato).
- `docs/legado/modelo-dominio-rma-legado.md` — entidades e relacionamentos do domínio.
- `docs/legado/regras-negocio-rma-legado.md` — regras de negócio catalogadas (RN-01...).
- `docs/investigacoes-pendente/INV-RMA-00-arqueologia-cellsystem-15.9.7.md` —
  investigação viva, move para `concluido/` só quando realmente terminada.
- `docs/produto/backlog-evolutivo.md` — Trilha B, nunca implementada agora.
- `docs/pareceres/` — conclusões executivas, geradas só quando a investigação madura.
- `PLAN.md` — estado macro. `PLANO-ATAQUE.md` — operacional (AGORA/DEPOIS/DEPENDÊNCIAS/
  CRITÉRIO DE SAÍDA/NÃO FAZER AINDA).

## Formato de retorno ao usuário

Checkpoints curtos: o que foi documentado, achados principais (3-5 pontos), estado,
commit local, próximo passo. Detalhe completo fica nos arquivos, não na resposta.
