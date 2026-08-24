# Backlog evolutivo — CellSystem RMA (pós-reconstrução)

Data: 2026-08-24. **Trilha B.** Nada aqui é implementado durante a Trilha A
(reconstrução fiel). Cada item nasce de uma evidência concreta da arqueologia — nunca de
uma ideia solta. Formato: ID · Título · Origem · Problema · Legado · Evolução ·
Benefício · Impacto · Complexidade · Risco · Dependências · Prioridade · Fase.

## EVO-SAAS

### EVO-SAAS-001 — Plataforma multiempresa (multi-tenant) real

- **Origem:** achado central de `docs/legado/modelo-dominio-rma-legado.md` §Empresa —
  `bd.empresa` já é um embrião de tenant, sem isolamento nenhum.
- **Problema observado:** um único banco já opera múltiplas empresas do grupo
  (Cellsystem, Expert, Registros Ativos, Informática) sem qualquer segmentação de
  acesso, dado ou numeração.
- **Legado:** campo texto livre, normalizado por `str_replace` ad-hoc (`RA`→`R A`),
  nunca usado para filtrar nenhuma query, relatório ou alerta.
- **Evolução:** o pedido original desta iniciativa (antes da correção de prioridade
  desta sessão) — plataforma SaaS multiempresa, shared database + shared schema +
  `tenant_id`, modelo Usuário↔Membership↔Empresa (papel contextual por empresa),
  numeração de RMA por tenant (`UNIQUE(empresa_id, numero)`), isolamento de storage,
  testes dedicados de cross-tenant leak. Todo o conteúdo técnico já levantado sobre
  isolamento de tenant, resolução de tenant corrente, autorização, route model binding
  seguro, storage por tenant, superadmin de plataforma vs. admin de empresa, e a decisão
  de não adotar biblioteca de tenancy no automático (avaliar caso a caso depois da
  reconstrução) — tudo isso fica preservado aqui, não descartado, só adiado.
- **Benefício:** transforma um sistema interno em produto comercializável para N
  empresas.
- **Impacto:** altíssimo — é uma segunda geração do produto.
- **Complexidade:** alta.
- **Risco:** alto se implementado antes da reconstrução fiel — mistura escopo.
- **Dependências:** reconstrução fiel completa e validada (Trilha A).
- **Prioridade sugerida:** alta, mas só depois da Trilha A.
- **Fase:** pós-reconstrução (Trilha B), primeira grande iniciativa.

## EVO-DOMINIO

### EVO-DOM-001 — Relacionamento por FK real entre RMA e contrapartes

- **Origem:** `docs/legado/modelo-dominio-rma-legado.md` §Relacionamentos — zero FK em
  todo o schema legado.
- **Problema:** nomes duplicados/digitados diferente geram RMAs "órfãos" que não
  aparecem em nenhuma listagem de contraparte; sem dedução de duplicidade de cliente.
- **Legado:** tudo por comparação de string de nome.
- **Evolução:** entidade `Parceiro` polimórfica (papel: cliente/fornecedor/fabricante/
  assistência) com FK real, herdeira conceitual da tabela `assistencias(tipo)` que o
  próprio app 14.6.1 tentou introduzir e abandonou.
- **Nota:** esta correção pode ser tratada como parte da RECONSTRUÇÃO (não muda
  comportamento percebido pelo usuário, só a integridade dos dados) — decisão final cabe
  ao parecer/arquitetura (`INV-RMA-05`, ainda não escrito), não necessariamente backlog
  evolutivo puro.
- **Fase:** avaliar se entra na Trilha A (correção estrutural) ou B (mudança de produto).

### EVO-DOM-002 — Entidade Equipamento separada do RMA

- **Origem:** `docs/legado/modelo-dominio-rma-legado.md` — não existe hoje; cada retorno
  do mesmo item físico vira uma linha nova sem vínculo.
- **Evolução:** entidade `Equipamento` (fabricante/modelo/SN) reutilizável entre
  múltiplos RMAs, habilitando histórico de recorrência de defeito por item físico (não
  só por nome de contraparte, como o achado "boletins relacionados" já faz hoje de forma
  limitada).
- **Benefício:** inteligência de recorrência de defeito por equipamento específico, não
  só por fornecedor/fabricante.
- **Fase:** pós-reconstrução — muda a experiência percebida (nova tela/relação), não é
  correção invisível.

### EVO-DOM-003 — Política de garantia estruturada por fabricante

- **Origem:** RN-02 (regra MARKVISION hardcoded), `regras-negocio-rma-legado.md`.
- **Problema:** conhecimento de negócio valioso (regras de garantia por fabricante)
  preso em `if` no código-fonte em vez de configuração.
- **Evolução:** tabela de regras de garantia configurável por
  (fabricante × origem × janela de tempo), com o campo `politicadegarantia` (hoje texto
  morto) alimentando a regra de verdade em vez de só aparecer como leitura.
- **Fase:** pós-reconstrução.

## EVO-AUTOMACAO

### EVO-AUT-001 — Alertas automáticos de prazo/garantia (não só painel manual)

- **Origem:** as 10 regras de alerta (RN-01 a RN-10) hoje só aparecem se o operador abrir
  a home e olhar o painel.
- **Problema:** usuário depende de checar manualmente para descobrir urgência.
- **Evolução:** notificações configuráveis (e-mail/push) antes do vencimento de prazo/
  garantia, por regra.
- **Benefício:** redução de perda de prazo/janela de garantia.
- **Fase:** pós-reconstrução.

### EVO-AUT-002 — Automação do fluxo de crédito

- **Origem:** RN atual de crédito (`modelo-dominio-rma-legado.md` §Estoque/Financeiro) —
  hoje `solucao='GERADO CREDITO'` e o checkbox `creditodisponivel` são preenchidos
  manualmente, sem vínculo automático entre os dois.
- **Evolução:** transição de estado de crédito automatizada e auditável.
- **Fase:** pós-reconstrução.

## EVO-RELATORIOS

### EVO-REL-001 — Relatórios reais (não impressão via Ctrl+P)

- **Origem:** `inventario-tecnico-15.9.7.md` — os 3 relatórios legados geram HTML para
  impressão manual no navegador, sem PDF server-side.
- **Evolução:** geração de PDF/export real, filtros configuráveis.
- **Fase:** pós-reconstrução.

### EVO-REL-002 — Inteligência de recorrência de defeito por fornecedor/fabricante

- **Origem:** achado "boletins de defeito relacionados" (RN correlata), 15.8.1 já lista
  todos os RMAs do mesmo destinatário/fabricante/fornecedor no rodapé da tela — sem
  `LIMIT`, sem agregação, é uma lista bruta.
- **Evolução:** dashboard de taxa de defeito por fabricante/fornecedor, com paginação e
  agregação real.
- **Fase:** pós-reconstrução.

## EVO-SEGURANCA

Nota: correções de segurança que **não mudam comportamento percebido** (hashing forte de
senha, prepared statements em 100% das queries, CSRF token, escoping de include sem
LFI) são tratadas como parte da **Trilha A** (reconstrução), não backlog evolutivo — o
"regra de ouro" do usuário já classifica segurança como eixo a reconstruir, não a
evoluir depois. Os itens abaixo são melhorias que **vão além** de restaurar o
comportamento original com segurança correta.

### EVO-SEG-001 — Autenticação multifator / SSO

- **Origem:** ausência total de 2FA no legado (era mono-empresa, um único nível de
  risco).
- **Evolução:** MFA opcional, especialmente relevante quando virar multiempresa
  (EVO-SAAS-001).
- **Fase:** pós-reconstrução, provavelmente junto com EVO-SAAS-001.

## EVO-AUDITORIA

### EVO-AUD-001 — Histórico de transição com diff real (não snapshot)

- **Origem:** tabela `modificacao` do legado grava um snapshot desnormalizado dos campos
  no momento da edição, não um diff nem a ação específica realizada.
- **Evolução:** histórico estruturado com "de → para" por campo, e nome da ação
  (encaminhar/concluir/etc.), não só um retrato do estado final.
- **Nota:** ter histórico forte e rastreável já é requisito da Trilha A (regra de ouro do
  usuário: "preservar rastreabilidade"); o que fica para a Trilha B é a **melhoria** do
  formato (diff em vez de snapshot), não a existência do histórico em si.
- **Fase:** avaliar se o diff estruturado entra na Trilha A (é auditoria melhor, não
  produto novo) ou B — provável candidato à Trilha A por ser invisível ao usuário final.

## EVO-PERFORMANCE

### EVO-PERF-001 — Eliminar as ~25 queries full-scan da home

- **Origem:** achado do agente de arqueologia do 15.8.1 — a home dispara os 10 alertas +
  14 widgets do menu direito, cada um com `SELECT` sem `LIMIT` sobre `bd` inteira,
  acumulando result sets em memória (statements nunca fechados, `close()` inalcançável
  após `return`).
- **Evolução:** paginação, cache, índices adequados, fechamento correto de conexões —
  parte natural de uma reconstrução em Laravel/Eloquent, não é feature de produto nova.
- **Fase:** Trilha A (é reconstrução com tecnologia atual, não evolução de produto) —
  registrado aqui só para não perder o achado até a arquitetura ser decidida.

## EVO-IA

### EVO-IA-001 — Sugestão de diagnóstico/prioridade assistida por IA

- **Origem:** nenhuma automação de triagem existe no legado — toda classificação
  (defeito, prioridade, garantia) é manual.
- **Evolução:** IA sugere categoria/prioridade/resposta ao cliente; usuário confirma;
  alteração fica auditada (nunca decide sozinha).
- **Fase:** pós-reconstrução, fase avançada.

---

## Como este documento é usado

Cada achado novo durante a arqueologia que pareça uma oportunidade de melhoria (não uma
regra a preservar) entra aqui, imediatamente, com origem concreta. Revisão e priorização
formal só acontecem depois que a Trilha A (reconstrução fiel) estiver validada — ver
`PLANO-ATAQUE.md`.
