# Padrão canônico de status do plano

Válido para `PLANO-ATAQUE.md`, checklists executáveis e listas de ondas/tasks do
repositório. Regra fixa do dono desde 2026-09-09.

## Marcadores obrigatórios

- `[ ]` **PENDENTE** - ainda não investigado suficientemente ou ainda não executado.
- `[R]` **REVISADO** - investigado/auditado e com direção confirmada, mas ainda NÃO
  totalmente implementado/testado/fechado.
- `[x]` **CONCLUÍDO** - implementado ou resolvido, com evidência suficiente e
  critérios de saída cumpridos.

## Regras de uso

- Nunca usar apenas texto solto como “pendente”, “feito”, “em andamento” ou
  “revisado”, sem o marcador correspondente.
- Se `PLANO-ATAQUE.md` não estiver no padrão, normalizá-lo antes de continuar a
  execução, preservando conteúdo e histórico.
- Planos novos já nascem neste formato.
- Investigação fechada sem implementação = `[R]`.
- Sem diagnóstico suficiente = `[ ]`.
- `[x]` exige evidência real (código/teste/runtime/documentação conforme o tipo
  da tarefa), não apenas “foi investigado”.

## Fronteira com documentos técnicos

Documentos de investigação/parecer podem continuar detalhados, sem checkbox em
cada parágrafo. Seções do tipo “Plano de correção por ondas” dentro desses
documentos também devem usar os marcadores canônicos.

## Sequência padrão de trabalho

INVESTIGAR → DOCUMENTAR → COMMIT DOCUMENTAL → EXECUTAR EM CICLOS PEQUENOS →
TESTAR/COMMITAR → RECONCILIAR → HANDOFF → COMMIT DO HANDOFF POR ÚLTIMO.
