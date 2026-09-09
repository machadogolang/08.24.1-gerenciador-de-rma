# Design — Auditoria e fechamento de fluxos Legacy × V3

## Fontes

- Legacy read-only: `legacy-source/{14.6.1,15.8.1,index.php,metodo.php,trocarapp.php}`,
  `.htaccess`, runtime `:8094`.
- V3: código, testes, runtime `:8095`, OpenSpecs existentes e documentos arqueológicos.

## Método

1. Mapa de fluxos (`docs/produto/2026-09-09-mapa-fluxos-legado-v3.md`).
2. Matriz de cobertura (`docs/produto/2026-09-09-matriz-cobertura-legacy-v3.md`).
3. Classificação A–J por fluxo; fechar apenas o seguro e objetivo; deixar `I`
   registrado para o dono sem bloquear os demais.
4. Regra dos quatro quadrantes: a versão Legacy funcional define o comportamento
   consolidado; bug Legacy não é reproduzido.
5. Cada correção em ciclo pequeno: testes dirigidos → docs/task → diff check → commit.

## Escopo por onda

P0 baseline+mapa · P1 troca de tema V1 · P2 relatórios shell · P3 secundárias shell ·
P4 navegação/descobribilidade · P5 parceiro show/RMAs · P6 busca contrapartes ·
P7 ciclo de vida UX (destinatário, conclusão) · P8 anotações/usuários/policy/
validação · P9 rotas/plugins residuais · P10 reconciliação documental · P11 regressão
funcional · P12 Playwright quatro quadrantes · P13 full PHPUnit/build · P14 handoff.

Ondas P2/P3 já foram concluídas no contexto FRONT-003 e são auditadas/reconciliadas
aqui, não reimplementadas.
