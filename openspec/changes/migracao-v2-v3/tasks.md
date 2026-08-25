# Tasks — Migração V2→V3

**Pré-requisito bloqueador:** Fases 4 e 5 (`Status`/`Solucao`/`Origem`/`Prioridade`/
`StatusDeLancamento`) precisam estar implementadas em código antes desta fase começar a
ser codificada — o mapa de tradução (`INV-RMA-06`) já está fechado, mas os enums de
destino ainda não existem em `app/Rma/Dominio/`.

- [ ] Conexão `rma_legacy` só-leitura em `config/database.php` (usuário MySQL com
      `GRANT SELECT` apenas)
- [ ] `app/Rma/Infraestrutura/Migracao/TabelaDeTraducao.php`
- [ ] `app/Rma/Infraestrutura/Migracao/ConexaoLegado.php`
- [ ] `app/Rma/Infraestrutura/Migracao/RelatorioDeReconciliacao.php`
- [ ] `app/Rma/Infraestrutura/Migracao/ResolverDestinatario.php`
- [ ] `database/migrations/2026_09_01_000000_add_numero_legado_to_rmas_table.php`
- [ ] `database/migrations/2026_09_01_000001_add_campos_historicos_de_migracao_to_rmas_table.php`
- [ ] `app/Parceiros/Aplicacao/EncontrarOuCriarFabricante.php` (+ Fornecedor,
      AssistenciaTecnica)
- [ ] 8 importadores em `app/Rma/Infraestrutura/Migracao/Importadores/`
- [ ] `app/Console/Commands/MigrarLegado.php` (opções `--somente`/`--dry-run`/`--forcar`)
- [ ] 8 testes de importador + `MigrarLegadoComandoTest`
- [ ] Resolver ou registrar decisão do usuário para as 3 pendências reais restantes de
      `INV-RMA-06` (formato de data ambíguo — implementar o parser de 3 tentativas
      descrito lá, nunca lançar exceção; ocorrência real de `status='retornou'` — só
      decidir se o relatório de reconciliação encontrar de verdade; destino de
      `relatorio.informacaoadicional` — aplicar a opção B, descartar, por omissão, já
      que o dado continua recuperável no backup do Legacy, e registrar isso como decisão
      tomada por omissão, não silenciosa). `rmas.valor` já resolvido (Fase 5).
- [ ] `sail test` verde
- [ ] Rodar `--dry-run` contra fixture pequena, revisar relatório manualmente antes de
      considerar o migrador pronto para uso real
- [ ] Atualizar `docs/produto/paridade-v2-v3.md` (itens de origem de dado, não de
      comportamento — esses já fecharam nas Fases 1-8)
- [ ] Atualizar `docs/produto/checklist-master-v3.md` (Fase 9 concluída)
- [ ] Commit `#F9 - Migracao V2-V3 (migrador, relatorio de reconciliacao)`
