# Tasks - Paridade total dirigida por fluxos (Legacy × V3)

- [x] P0 - baseline V3 (`75c110d`/local `a254d58`) e Legacy `f83542c`; mapa de fluxos
  e matriz de cobertura criados.
- [x] P1 - Trocar tema no menu do Tema V1 (POST/CSRF) - `ed30fbc`.
- [x] P2/P3 - Relatórios e secundárias já integrados ao shell (FRONT-003); falta apenas
  reconciliação de evidência/print (segue aberto no registro).
- [x] P4 - Navegação/descobribilidade: auditoria de menus e RPEC/RMPE nos menus
  V1/V2 - `2f1d983` + doc `auditoria-menus-legado-v3.md`.
- [x] P5 - Detalhe de parceiro + RMAs associados (tenant/policy/prova A×B) -
  `12b74f0`.
- [x] P6 - Busca textual integral por contrapartes (fabricante/fornecedor/cliente/
  destinatário) com prova A×B - `13e4c3a`.
- [ ] P7 - Encaminhar por seleção validada e conclusão Legacy (UX-003/PAR-RMA-008).
- [ ] P8 - Anotações V2 dedicada, confirmação de remoção, UX-001/UX-004.
- [ ] P9 - Inventário de rotas/plugins residuais (avisar, enviar_email,
  representantes, marcarcomo, pomodoro já classificado J).
- [ ] P10 - Reconciliação documental (checklist, paridade, matriz temas, roteiro).
- [ ] P11 - Regressão funcional por fluxo.
- [ ] P12 - Playwright quatro quadrantes em `tests/Browser/Fluxos/`.
- [ ] P13 - PHPUnit completo + build (atual 515 testes / 1421 assertions).
- [ ] P14 - Fechamento/handoff.


## Addendum 2026-09-09 - paridade dos detalhes RMA e residuos V2 (A5/A6)

- [x] A5 - Detalhe RMA Tema V1 (PAR-DET-V1-01): BOLETIM DE DEFEITO, grupos 4
  colunas e informacao restaurados (e91cdae/1fc2a86).
  - [x] A5.1 - Reproducao documentada com metricas/screenshots (PAR-DET-V1-01).
  - [x] A5.2 - Blade/CSS do show V1.
  - [x] A5.3 - Campos com prova e lacunas documentadas.
  - [x] A5.4 - Regression browser V1.
- [x] A6 - Detalhe RMA Tema V2 (PAR-DET-V2-01): cabecalho operacional + grupos do
  show V2 read-only (6b78ac3/10f786f).
  - [x] A6.1 - Reproducao documentada com metricas/screenshots (PAR-DET-V2-01).
  - [x] A6.2 - Blade/CSS do show V2.
  - [x] A6.3 - Acoes/cabecalho integrados.
  - [x] A6.4 - Regression browser V2.
- [x] PAR-V2-GEO-01 - geometria das abas/listagens V2 (c030e87/a71a3d8).
- [x] PAR-V2-PESQ-01 - composicao Pesquisar V2 (c030e87/a71a3d8).
