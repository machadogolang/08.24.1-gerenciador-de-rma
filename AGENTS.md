# Agentes - CellSystem RMA

Reconstrução do CellSystem RMA como produto moderno, em duas trilhas separadas:

- **Trilha A (agora):** restauração fiel do produto 15.9.7 - mesmas regras, fluxos,
  identidade visual, com arquitetura/segurança/tecnologia atuais (linha CONAHOM,
  proporcional ao domínio).
- **Trilha B (depois):** evolução - SaaS multiempresa, automações, IA. Registrada em
  `docs/produto/backlog-evolutivo.md`, nunca implementada durante a Trilha A. A Trilha A
  foi encerrada em 2026-09-04 (`F10-GATE-07`); a partir de 2026-09-09 a Trilha B está
  liberada para execução controlada por ondas pequenas com OpenSpec, testes e commits.

## Regras fixas

- **A conversa não é fonte de verdade - o repositório é.** Todo achado de arqueologia
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
  compartilhada (`metodo.php`) - não uma linhagem sequencial provada. Ver
  `docs/legado/matriz-comparacao-apps-rma.md` antes de assumir qualquer ordem histórica.
- Toda regra de negócio preservada da Trilha A precisa de rastreabilidade por versão
  (14.6.1 / 15.8.1 / camada compartilhada), usando as tags
  `[CONFIRMADO-*]`/`[HIPOTESE-HISTORICA]`/`[BUG-LEGADO]`/`[CODIGO-MORTO]`/`[DÚVIDA]`.
- Não inventar comportamento histórico - o que não está comprovado no código fica
  `[DÚVIDA]`, nunca vira decisão.
- **Nunca** `git push`, PR ou merge remoto sem autorização explícita nesta conversa.
- Commits locais pequenos e coerentes por checkpoint (`#ARQ-RMA - ...`).

## Onde está cada coisa

- `docs/legado/inventario-tecnico-15.9.7.md` - backup, tecnologias, bibliotecas, código
  morto/duplicado.
- `docs/legado/matriz-comparacao-apps-rma.md` - arquitetura multi-app, comparação
  funcional 14.6.1 × 15.8.1.
- `docs/legado/cronologia-rma.md` - pistas de datação (hipótese, não fato).
- `docs/legado/modelo-dominio-rma-legado.md` - entidades e relacionamentos do domínio.
- `docs/legado/regras-negocio-rma-legado.md` - regras de negócio catalogadas (RN-01...).
- `docs/investigacoes-pendente/INV-RMA-00-arqueologia-cellsystem-15.9.7.md` -
  investigação viva, move para `concluido/` só quando realmente terminada.
- `docs/produto/backlog-evolutivo.md` - Trilha B (liberada para execução controlada
  pós-gate; primeira iniciativa: EVO-SAAS-001).
- `docs/pareceres/` - conclusões executivas, geradas só quando a investigação madura.
- `PLAN.md` - estado macro. `PLANO-ATAQUE.md` - operacional (AGORA/DEPOIS/DEPENDÊNCIAS/
  CRITÉRIO DE SAÍDA/NÃO FAZER AINDA).

## Formato de retorno ao usuário

Checkpoints curtos: o que foi documentado, achados principais (3-5 pontos), estado,
commit local, próximo passo. Detalhe completo fica nos arquivos, não na resposta.

## Regra operacional permanente - assinatura de sandbox `bwrap: loopback` (regra do dono, 2026-09-10)

Assinatura conhecida (aparece antes de qualquer saida util, mesmo em `pwd`/`printf`):

```
bwrap: loopback: Failed RTM_NEWADDR: Operation not permitted
```

Incidente ja registrado em
`docs/operacao/incidentes/2026-09-09-sandbox-bwrap-loopback.md`. Aplicar esta regra
desde o inicio de cada sessao, antes da primeira rodada de comandos.

- Esse erro e CONHECIDO. Nao e evidencia de problema no projeto, no repositorio, na
  rede, em namespace, em loopback, no Docker nem no ambiente.
- Nao reinvestigar a causa. Nao iniciar diagnostico de sandbox, rede, namespace,
  loopback, Docker ou ambiente.
- Nao executar varias tentativas equivalentes. Confirmada a mesma assinatura, fazer no
  maximo UMA tentativa equivalente adicional e mudar de mecanismo.
- Nao ficar testando `find`, `ls`, `grep` etc. por mecanismos diferentes apenas para
  redescobrir o problema.
- Priorizar desde o inicio os comandos/prefixos ja permitidos pelo ambiente. Para
  leitura, preferir o que ja funciona: comandos aprovados (`cat`, `git log`,
  `git status --short --branch`), leitura direta de arquivos e prefixos estreitos.
- Se um comando necessario cair exatamente nesse erro, mudar imediatamente para o
  mecanismo estreito/escalado ja previsto no documento do incidente: leitura pontual
  com `sandbox_permissions: require_escalated` e prefixo estreito.
- Nunca usar escalada ampla quando uma escalada/prefixo estreito resolve.
- Nao interromper a tarefa principal para diagnosticar o sandbox.
- Nao criar novo incidente/documento para a mesma falha.
- So mencionar o problema no retorno ao usuario se ele realmente impedir a execucao.
- Havendo caminho alternativo, continuar o trabalho normalmente.
- Comunicacao esperada: "Assinatura conhecida de bwrap detectada; aplicando
  procedimento ja documentado." e seguir trabalhando. Nao narrar reinvestigacao nem
  "vou descobrir qual mecanismo funciona".
- Nota pratica deste harness: `apply_patch` tambem passa pelo wrapper e falha ao ler
  arquivos existentes quando a assinatura esta ativa. Criar arquivos novos continua
  funcionando; editar arquivos existentes deve usar edicao escalada pontual e estreita.

## Marcadores canônicos de status do plano (regra do dono, 2026-09-09)

Todo `PLANO-ATAQUE.md`, checklist executável e lista de ondas/tasks deve usar
obrigatoriamente os marcadores `[ ]` (PENDENTE), `[R]` (REVISADO) e
`[x]` (CONCLUÍDO), conforme definição e regras em
`docs/operacao/padrao-status-plano.md`.

- Investigação fechada sem implementação = `[R]`.
- Sem diagnóstico suficiente = `[ ]`.
- `[x]` exige evidência real (código/teste/runtime/documentação).
- Antes de continuar execução, normalize `PLANO-ATAQUE.md` se ele não estiver
  no padrão; planos novos já nascem no padrão.
- Documentos técnicos detalhados podem continuar em prosa, mas seções do tipo
  “Plano de correção por ondas” também usam os marcadores.
- Sequência padrão: INVESTIGAR → DOCUMENTAR → COMMIT DOCUMENTAL → EXECUTAR EM
  CICLOS PEQUENOS → TESTAR/COMMITAR → RECONCILIAR → HANDOFF → COMMIT DO HANDOFF
  POR ÚLTIMO.

## Regra do hifen curto (regra do dono, 2026-09-09)

Nunca usar hifen longo (em dash, U+2014); usar sempre hifen simples ("-") em todo
conteudo do projeto, incluindo documentacao, planos, OpenSpec, interface
operacional e mensagens. Referencia: `docs/operacao/regra-hifen.md`. A regra
tambem foi registrada no AGENTS global do Codex.
