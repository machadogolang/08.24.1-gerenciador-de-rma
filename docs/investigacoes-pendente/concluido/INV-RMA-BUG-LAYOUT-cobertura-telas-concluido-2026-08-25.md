# INV-RMA-BUG-LAYOUT — sub-frente "cobertura de telas" — concluída

**Data de conclusão:** 2026-08-25.

**Resumo objetivo:** sub-frente da investigação `INV-RMA-BUG-LAYOUT` (arquivo-mãe em
`docs/investigacoes-pendente/INV-RMA-BUG-LAYOUT-problemas-no-layout.md`, que continua
aberta por outras frentes). Esta sub-frente auditou sistematicamente se o MESMO tipo de
gap achado nas 4 listagens por status do TEMA V1 (ausência total de rota+controller+view,
não um problema de CSS) existe em outra tela do legado, cruzando cada arquivo
`page/*.php`/`subp/*.php`/`pp/*.php` dos dois temas do legado (`14.6.1` e `15.8.1`)
contra `routes/web.php` e os Controllers reais da V3.

**Conclusão alcançada:** inventariadas 14 telas reais no legado V1 (14.6.1) e as
equivalentes no V2 (15.8.1); a maioria já coberta pela V3 (28 telas plenamente
cobertas). Identificadas lacunas parciais no painel `controle.php` dos dois temas
(3 sub-lacunas no V1, 1 no V2) e 7 arquivos totalmente ausentes na V3, agrupados em 3
achados novos registrados no checklist operacional:
`VIS-V1-009` (5 telas de detalhe de parceiro), `VIS-V1-014` (tela de ajuda),
`VIS-V2-001` (aba "Recebido" do TEMA V2 legado). Também gerou o refinamento
`VIS-V1-010` (o "Controle" do MENU V1 apontava para a tela errada — do V2, não do V1).

**Pendências/desdobramentos:** nenhum achado foi corrigido nesta sub-frente (escopo era
só o levantamento). Desdobramentos, todos já rastreados em
`docs/produto/checklist-paridade-visual-v1-runtime.md`:
- `VIS-V1-010` — **corrigido** em sessão posterior (commit `873e88a`).
- `VIS-V1-013` (listar arquivados, uma das 3 sub-lacunas do Controle V1) —
  **corrigido** junto de `VIS-V1-010`.
- `VIS-V1-011`/`VIS-V1-012` (hard delete de RMA/usuário) — pendentes, decisão de
  produto/segurança ainda não tomada.
- `VIS-V1-009`, `VIS-V1-014`, `VIS-V2-001` — pendentes, não corrigidos ainda.

**Parecer completo correspondente:**
`docs/pareceres/2026-08-25-parecer-cobertura-telas-legado-x-v3.md`.
