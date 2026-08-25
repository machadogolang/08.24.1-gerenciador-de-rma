# Diagnóstico dos dumps históricos 15.9.7

Data: 2026-08-25. **[CONFIRMADO-BANCO]** por importação em três databases temporários
isolados e consultas de contagem/checksum. Nenhum registro pessoal foi copiado ao Git.

O original em `~/Downloads` e a cópia de arqueologia têm SHA-256
`d3811daa79087e04927613505069a7a81221691d93cca9ee37b7f0096ba354df`.

| Snapshot | Tabelas | RMAs (`bd`) | Clientes | Maior `bd.dtains` |
|---|---:|---:|---:|---|
| `app/1maiode2019.sql` | 10 | 1.332 | 165 | 2019-04-26 11:42:06 |
| `app/2maiode2019.sql` | 10 | 1.333 | 165 | 2019-05-02 11:52:50 |
| `dump-cellsyst_rma-201912161213.sql` | 10 | 1.379 | 165 | 2019-12-12 12:33:09 |

**[CONFIRMADO-BANCO]** Os três têm `DROP TABLE`, `CREATE TABLE`, limpeza e `INSERT`
para as mesmas dez tabelas. São snapshots completos, não incrementais; cada um
substitui o anterior. O snapshot de dezembro é o mais recente e completo e foi escolhido
para o modo `historical`. SHA-256:
`a7b3cca003e64c3e71f491f3b34ea6091dcde728400da073da7e425394385791`.

**Correção de arqueologia:** documentação anterior o chamava de “schema-only”. Isso
estava errado: ele contém 1.379 RMAs. O `db/schema-only.sql` do Legacy é uma derivação
sanitizada somente das estruturas; não é cópia integral.
