# Plano de ataque — CellSystem RMA

Última atualização: 2026-08-24.

## AGORA

**Arquitetura decidida** (`docs/arquitetura/INV-RMA-05-arquitetura-proposta.md` —
monólito modular, referência CONAHOM real). **Fase 1 (Identidade) em especificação**:
OpenSpec escrita em `openspec/changes/autenticacao-usuarios/`, ainda não implementada.
Checklist granular por fase (1 a 10) em `docs/produto/checklist-master-v3.md` — usar
esse documento como mapa operacional a partir de agora, este arquivo continua sendo só
o resumo de fase/dependência/critério de saída.

## DEPOIS

1. **INV-RMA-05** — arquitetura moderna proposta (Laravel, proporcional, linha CONAHOM,
   investigar granularidade real de compartilhamento de domínio/controllers entre
   TEMA V1 e TEMA V2 antes de fixar).
2. **INV-RMA-06** — estratégia de reconstrução/migração formal (mapa legado→V3 por
   tabela/campo, ver `MIG-V3` abaixo).
3. Primeira OpenSpec real do catálogo proposto (`autenticacao-usuarios` é o candidato
   mais simples/seguro — schema estável, regra já validada nos dois temas).
4. Só depois: tasks, primeira fatia de implementação.
5. Item residual de baixo risco, não bloqueante: RN-12 (threshold R$75) — confirmar
   ausência/presença em TEMA V1 com leitura linha a linha completa, se necessário.
6. ARQ-07c — screenshots/telas internas (novo RMA, detalhes) dos dois temas.

## DEPENDÊNCIAS

- INV-RMA-05/06 dependem do parecer — **já disponível** (ARQ-08 concluído).
- MIG-V3 (migrador real) depende de INV-RMA-05/06 (schema da V3 precisa existir).
- Implementação da V3 depende de OpenSpec madura por funcionalidade — nunca "legado →
  interpretação rápida → código".

## CRITÉRIO DE SAÍDA da arqueologia — ATINGIDO

- [X] 48 funcionalidades com situação conhecida nos dois temas (1 residual de baixo
      risco: RN-12).
- [X] Árvore RMA V2/TEMA V1/TEMA V2 documentada sem contradição.
- [X] Inventário de segurança cobrindo os dois temas + camada compartilhada.
- [X] Identidade visual dos dois temas registrada (1ª passada, com evidência de
      runtime real — faltam só screenshots/telas internas, não bloqueante).
- [ ] Mapa legado→V3 por tabela/campo — pendente, é o próximo passo (`MIG-V3`/
      `INV-RMA-06`), não faz parte do critério de saída da arqueologia em si.

## Catálogo inicial de OpenSpec proposto (ainda nenhuma escrita — proposta de agrupamento)

Nascido do inventário funcional (48 itens `LEG-RMA-NNN`), agrupado por capacidade
coerente, não por botão nem num bloco só:

| Change proposta | Itens `LEG-RMA` cobertos |
|---|---|
| `autenticacao-usuarios` | 001–006, 043 |
| `rma-cadastro-e-localizacao` | 007–010 |
| `rma-ciclo-de-vida` | 011–017 (receber/encaminhar/concluir/arquivar/rollback) |
| `rma-alertas-e-prioridade` | 018–029 (as 10 regras + classificação visual + threshold) |
| `parceiros` (cliente/fabricante/fornecedor/assistência) | 030–035 |
| `rma-creditos-e-relatorios` | 036–039, 048 |
| `rma-logistica-e-historico` | 040–041, 044–047 |
| `temas-v1-v2` | apresentação — decisão de arquitetura de tema, não itens LEG-RMA específicos |
| `migracao-v2-v3` | MIG-01/02, não itens LEG-RMA |

Cada change só é escrita quando houver decisão de arquitetura madura o bastante
(`INV-RMA-05`) para descrever "como será na V3", não só "como era na V2".

## NÃO FAZER AINDA

- Não criar migration, model, controller, view Laravel da V3 sem OpenSpec correspondente.
- Não fixar schema novo, enums finais, máquina de estado final até `INV-RMA-05` decidir.
- Não implementar nenhum item do `backlog-evolutivo.md`.
- Não fazer melhoria estrutural de banco antes da baseline V3 validada (ver regra de
  evolução do banco, na investigação arquivada §10).
- Não editar o código-fonte legado dentro de `08.24.4-legacy-gerenciador-de-rma/
  legacy-source/` — qualquer adaptação de ambiente é documentada como configuração de
  container, nunca como edição da fonte histórica.
- Não publicar o LEGACY-RUNTIME fora de `localhost`; não usar credencial real; não
  permitir envio de e-mail real (Mailpit obrigatório — já validado).
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
| ARQ-06b | Resolver dúvidas de presença por tema | RN-13 a RN-18/RN-21 comparadas linha a linha (RN-12 residual) | `[X]` |
| ARQ-07a | Interface/identidade visual (1ª passada) | `inventario-visual-tema-{v1,v2}.md` escritos com evidência de runtime | `[X]` |
| ARQ-07b | Inventário de banco dedicado | `inventario-banco-rma-v2.md` escrito | `[X]` |
| ARQ-07c | Screenshots + telas internas | Imagem real dos dois temas, novo RMA e detalhes | `[ ]` |
| ARQ-08 | Parecer arqueológico consolidado | 17 pontos respondidos | `[X]` |
| LR-01 | Design do LEGACY-RUNTIME | Escrito, compat. PHP verificada | `[X]` |
| LR-02 | `compose.yaml`/`Dockerfile` escritos e testados | TEMA V1 e TEMA V2 respondem em `localhost:8094` | `[X]` |
| LR-03 | Login validado nos dois temas | Smoke test real: login, home, listagem, RMA de fixture, transições, troca de tema | `[X]` |
| LR-04 | Ambiente separado em repositório próprio + porta oficial | `08.24.4-legacy-gerenciador-de-rma`, porta 8094, `scripts/legacy-reset.sh` | `[X]` |
| LR-05 | Mailpit validado de verdade | `mail()` capturado, nada saiu para a internet | `[X]` |
| LR-06 | Execução simultânea com V3 | 6 containers ativos ao mesmo tempo, sem conflito | `[X]` |
| V3-01 | Fundação técnica executável | Laravel 13/PHP 8.3, porta 8095, migrations + testes básicos passando | `[X]` |
| MIG-01 | Mapa legado → V3 por tabela/campo | Documento completo, nascido da arqueologia | `[ ]` |
| MIG-02 | Migrador oficial implementado | Repetível, testável, auditável, idempotente | `[ ]` |
