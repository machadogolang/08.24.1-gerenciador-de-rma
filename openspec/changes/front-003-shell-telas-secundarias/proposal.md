# Proposal — FRONT-003: shell dos temas em telas secundárias

Superfícies administrativas/operacionais fora do núcleo RMA atualmente retornam
documentos HTML standalone (`view('rma.*')`) sem shell V1/V2, com estilo browser
default. Referências: H-013/FRONT-003, INV-RMA-10,
`docs/produto/checklist-paridade-temas.md` (histórico: "funcional sem estilo").

Decisão do dono (2026-09-09): lacuna real de produto. Sem reabrir Fase 8, os
controllers permanecem únicos e passam a `view_do_tema()`; o conteúdo funcional fica
em partial compartilhado; shell/composição/identidade respeitam V1 e V2.
