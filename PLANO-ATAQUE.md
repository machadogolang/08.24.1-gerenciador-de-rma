# Plano de ataque — CellSystem RMA

Última atualização: 2026-08-24.

## AGORA

**LEGACY-RUNTIME** — escrever e testar `compose.yaml`/`Dockerfile` do ambiente PHP 7.4 +
MariaDB 10.3 + Mailpit para rodar TEMA V1 e TEMA V2 do RMA V2 localmente (design em
`docs/legado/legacy-runtime-ambiente.md`). Objetivo: ter o legado rodando como
especificação visual/funcional viva antes de fechar `ARQ-07`/`ARQ-08`.

## DEPOIS (em paralelo quando fizer sentido)

1. **ARQ-06b** — resolver dúvidas de presença por tema (RN-12 a RN-21 em TEMA V1;
   default de `usuario.app`) — retomando leitura de código, agora autorizada, sempre
   registrando achado direto em `regras-negocio-rma-legado.md`/`inventario-funcional-
   rma-v2.md`, nunca só na conversa.
2. **ARQ-07** — interface e identidade visual: com o LEGACY-RUNTIME de pé, capturar
   evidência real (não só CSS estático) para `inventario-visual-tema-v1.md` e
   `inventario-visual-tema-v2.md` (ainda não escritos).
3. **ARQ-08** — parecer arqueológico consolidado (17 pontos), referenciando todos os
   documentos de `docs/legado/`.
4. **MIG-V3** — mapa completo legado→V3 por tabela/campo; desenho do migrador oficial
   (requisito de produto, ver `INV-RMA-00` §10); baseline antes de qualquer melhoria
   estrutural (regra ANTES/PROBLEMA/DEPOIS/MIGRAÇÃO/COMPATIBILIDADE/TESTE).
5. **INV-RMA-05** — arquitetura moderna proposta (Laravel, proporcional, linha CONAHOM,
   compartilhamento de domínio/controllers entre TEMA V1 e TEMA V2 na apresentação).
6. **INV-RMA-06** — estratégia de reconstrução/migração formal.
7. Só depois: OpenSpecs por capacidade funcional coerente, tasks, fundação técnica.

## DEPENDÊNCIAS

- ARQ-07/08 se beneficiam do LEGACY-RUNTIME rodando (evidência viva > CSS estático), mas
  não são estritamente bloqueados por ele — se o bring-up levar muito tempo, ARQ-07 pode
  avançar com o CSS/HTML já lido, marcando lacunas.
- ARQ-08 depende de ARQ-06b resolvido para as regras `[BUG-LEGADO]` de maior impacto
  (RN-17, RN-18, RN-21) não ficarem `[DÚVIDA]` por omissão.
- INV-RMA-05/06 dependem do parecer (ARQ-08).
- MIG-V3 (migrador real) depende de INV-RMA-05/06 (schema da V3 precisa existir).
- Implementação da V3 depende de OpenSpec madura por funcionalidade — nunca "legado →
  interpretação rápida → código".

## CRITÉRIO DE SAÍDA da arqueologia

- Todas as 48 funcionalidades de `inventario-funcional-rma-v2.md` com situação conhecida
  nos dois temas (crítica: as `[BUG-LEGADO]` de alto impacto não podem ficar `[DÚVIDA]`
  por omissão).
- Árvore RMA V2/TEMA V1/TEMA V2 documentada sem contradição (feito — ARQ-05).
- Inventário de segurança cobrindo os dois temas + camada compartilhada (feito).
- Identidade visual dos dois temas registrada o suficiente para reconstrução fiel
  (pendente — ARQ-07).
- Mapa legado→V3 por tabela/campo existente (pendente — MIG-V3).

## NÃO FAZER AINDA

- Não criar migration, model, controller, view Laravel da V3.
- Não fixar schema novo, enums finais, máquina de estado final — tudo
  `PROVISÓRIO — AGUARDANDO VALIDAÇÃO` até o parecer.
- Não implementar nenhum item do `backlog-evolutivo.md`.
- Não fazer melhoria estrutural de banco antes da baseline V3 validada (ver regra de
  evolução do banco, `INV-RMA-00` §10).
- Não editar o código-fonte legado dentro de `_rma-arqueologia/backup-15.9.7/extracted/`
  — qualquer adaptação de ambiente é documentada como configuração de container, nunca
  como edição da fonte histórica.
- Não publicar o LEGACY-RUNTIME fora de `localhost`; não usar credencial real; não
  permitir envio de e-mail real (Mailpit obrigatório).
- Não `git push`, não PR, não merge remoto, não alterar os repositórios/backup
  históricos.

## Checkpoints ARQ-RMA / LEGACY-RUNTIME / MIG-V3

| Código | Título | Critério de fechamento | Status |
|---|---|---|---|
| ARQ-00 | Inventário do backup 15.9.7 | SHA-256, estrutura, tecnologias registrados | `[X]` |
| ARQ-01 | Arqueologia TEMA V2/15.8.1 | Regras/entidades/segurança documentadas | `[X]` |
| ARQ-02 | Arqueologia TEMA V1/14.6.1 | Idem | `[X]` |
| ARQ-03 | Arqueologia 14.10.2 | Confirmado protótipo sem banco, descartado como fonte principal | `[X]` |
| ARQ-04 | Camada compartilhada 15.9.7 | `metodo.php`/`conexao.php`/`trocarapp.php` documentados | `[X]` |
| ARQ-05 | Árvore RMA V2/TEMA V1/TEMA V2 | Matriz completa, ordem histórica confirmada pelo autor | `[X]` |
| ARQ-06a | Inventário funcional catalogado | 48 itens `LEG-RMA-NNN` + matriz de paridade | `[X]` |
| ARQ-06b | Resolver dúvidas de presença por tema | RN-12 a RN-21 verificadas em TEMA V1 | `[ ]` |
| ARQ-07 | Interface/identidade visual | `inventario-visual-tema-{v1,v2}.md` escritos | `[ ]` |
| ARQ-08 | Parecer arqueológico consolidado | 17 pontos respondidos | `[ ]` |
| LR-01 | Design do LEGACY-RUNTIME | `legacy-runtime-ambiente.md` escrito, compat. PHP verificada | `[X]` |
| LR-02 | `compose.yaml`/`Dockerfile` escritos e testados | TEMA V1 e TEMA V2 respondem em `localhost` | `[ ]` |
| LR-03 | Login e navegação básica validados nos dois temas | Evidência registrada | `[ ]` |
| MIG-01 | Mapa legado → V3 por tabela/campo | Documento completo, nascido da arqueologia | `[ ]` |
| MIG-02 | Migrador oficial implementado | Repetível, testável, auditável, idempotente | `[ ]` |
