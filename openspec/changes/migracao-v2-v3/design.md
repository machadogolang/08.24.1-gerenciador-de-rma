# Design — Migração V2→V3

Detalhe completo já escrito em `docs/arquitetura/INV-RMA-05-arquitetura-proposta.md`
§14 (arquitetura do migrador, ordem de execução, lista de arquivos) e
`docs/arquitetura/INV-RMA-06-estrategia-reconstrucao.md` (mapa campo-a-campo, a "tabela
de tradução explícita" exigida pelo princípio "sem número mágico", `INV-RMA-05` §1.1).
Este `design.md` não duplica o conteúdo — referencia e resume as decisões-chave.

## Decisões-chave

- **Migrador é infraestrutura, não domínio novo** — vive em
  `app/Rma/Infraestrutura/Migracao/`, coerente com a fronteira já justificada na Fase 3
  (`INV-RMA-05` §8): só o módulo `Rma` tem a fronteira completa, exatamente porque
  precisaria ler `rma_legacy` sem vazar esse conhecimento pro resto do app.
- **Conexão só-leitura reforçada em nível de banco** (`GRANT SELECT`, não só disciplina
  de código) — o migrador nunca escreve no legado.
- **Idempotência**: `numero_legado` (RMA) e dedup por email/nome (demais tabelas) via
  `updateOrCreate` — rodar o comando 2x não duplica.
- **Transação por importador, não uma transação gigante** — uma tabela com erro não
  trava as outras.
- **`TabelaDeTraducao`** é o único lugar do código que compara um valor cru do legado
  por igualdade — todo o resto do migrador (e do app) só vê os enums já implementados
  nas Fases 1/4/5.

## Arquivos (lista completa em `INV-RMA-05` §14)

```
app/Rma/Infraestrutura/Migracao/
├── TabelaDeTraducao.php
├── ConexaoLegado.php
├── RelatorioDeReconciliacao.php
├── ResolverDestinatario.php
└── Importadores/
    ├── ImportarUsuarios.php
    ├── ImportarClientes.php
    ├── ImportarFabricantes.php
    ├── ImportarFornecedores.php
    ├── ImportarAssistenciasTecnicas.php
    ├── ImportarRmas.php
    ├── ImportarLogsDeAcesso.php
    └── ImportarModificacoesDeRma.php

app/Parceiros/Aplicacao/
├── EncontrarOuCriarFabricante.php
├── EncontrarOuCriarFornecedor.php
└── EncontrarOuCriarAssistenciaTecnica.php

app/Console/Commands/MigrarLegado.php

database/migrations/
├── 2026_09_01_000000_add_numero_legado_to_rmas_table.php
└── 2026_09_01_000001_add_campos_historicos_de_migracao_to_rmas_table.php
```

## Ordem de execução (dependência de FK)

```
1. Usuarios → 2. Clientes → 3. Fabricantes → 4. Fornecedores →
5. AssistenciasTecnicas → 6. Rmas → 7. LogsDeAcesso → 8. ModificacoesDeRma
```

`ModificacoesDeRma` só roda se a Fase 7 (Auditoria) já estiver implementada — o comando
detecta a ausência e pula esse passo com aviso, não falha.

## Testes

- `tests/Feature/Migracao/Importar{Usuarios,Clientes,Fabricantes,Fornecedores,
  AssistenciasTecnicas,Rmas,LogsDeAcesso,ModificacoesDeRma}Test.php` — um por
  importador, banco `rma_legacy` de teste com fixture pequena e conhecida (não o dump
  de produção): caso feliz, anomalia (valor fora do domínio), idempotência (rodar 2x
  não duplica), soft-match de e-mail.
- `tests/Feature/Migracao/MigrarLegadoComandoTest.php` — comando completo contra a
  fixture, valida ordem de execução e relatório final.
