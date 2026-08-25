# Tasks — Migração V2→V3

**Pré-requisito bloqueador:** Fases 4 e 5 (`Status`/`Solucao`/`Origem`/`Prioridade`/
`StatusDeLancamento`) precisam estar implementadas em código antes desta fase começar a
ser codificada — o mapa de tradução (`INV-RMA-06`) já está fechado, mas os enums de
destino ainda não existem em `app/Rma/Dominio/`.

- [x] Conexão `rma_legacy` só-leitura em `config/database.php` (`LEGACY_DB_*` em
      `.env.example`, usuário MySQL com `GRANT SELECT` apenas documentado — provisionar
      o usuário real no banco Legacy fica para quando o usuário rodar a migração de
      verdade, fora do escopo de "implementar o código")
- [x] `app/Rma/Infraestrutura/Migracao/TabelaDeTraducao.php`
- [x] `app/Rma/Infraestrutura/Migracao/ConexaoLegado.php`
- [x] `app/Rma/Infraestrutura/Migracao/RelatorioDeReconciliacao.php`
- [x] `app/Rma/Infraestrutura/Migracao/ResolverDestinatario.php`
- [x] `database/migrations/2026_09_02_000000_add_numero_legado_to_rmas_table.php`
      (deslocada de `2026_09_01_000000` — data já ocupada pela migration da Fase 7,
      commitada antes desta fase começar a ser codificada)
- [x] `database/migrations/2026_09_02_000001_add_campos_historicos_de_migracao_to_rmas_table.php`
- [x] `app/Parceiros/Aplicacao/EncontrarOuCriarFabricante.php` (+ Fornecedor,
      AssistenciaTecnica)
- [x] 8 importadores em `app/Rma/Infraestrutura/Migracao/Importadores/`
- [x] `app/Console/Commands/MigrarLegado.php` (opções `--somente`/`--dry-run`/`--forcar`)
- [x] 8 testes de importador + `MigrarLegadoComandoTest`
- [x] Resolver ou registrar decisão do usuário para as 3 pendências reais restantes de
      `INV-RMA-06` (formato de data ambíguo — parser de 3 tentativas implementado em
      `ParserDeDataLegado`, nunca lança exceção; ocorrência real de `status='retornou'`
      — tratado como anomalia caso ocorra, sem case novo no enum; destino de
      `relatorio.informacaoadicional` — opção B aplicada por omissão, tabela `relatorio`
      nunca é lida por `ConexaoLegado`, decisão registrada em `proposal.md`). `rmas.valor`
      já resolvido (Fase 5).
- [x] `sail test` verde (308/308, 265 das Fases 1-8 + 43 novos desta fase)
- [x] Rodar `--dry-run` contra fixture pequena (testes automatizados,
      `MigrarLegadoComandoTest`) — dry-run contra o Legacy REAL tentado nesta sessão e
      bloqueado por rede (porta `3309` do Legacy publicada só em `127.0.0.1` do host,
      inacessível via `host.docker.internal` de dentro do container V3 — ver
      `log-implementacao-v3.md`, Fase 9, não é um problema do migrador)
- [x] Atualizar `docs/produto/paridade-v2-v3.md` (itens de origem de dado, não de
      comportamento — esses já fecharam nas Fases 1-8)
- [x] Atualizar `docs/produto/checklist-master-v3.md` (Fase 9 concluída)
- [x] Commit `#F9 - Migracao V2-V3 (migrador, relatorio de reconciliacao)`
