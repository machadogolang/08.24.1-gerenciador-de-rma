# Checklist de paridade funcional — TEMA V1 x TEMA V2

Auditoria de paridade FUNCIONAL (não só visual) entre os dois temas do RMA V3, feita em
2026-08-25 sobre `rma-v3-laravel.test-1` (`:8095`). Método: `sail artisan route:list
--json` para a lista real de rotas (112 rotas, não confiei só na leitura de
`routes/*.php`); leitura de cada Controller para achar a view retornada
(`view_do_tema()` vs. `view()` direto); leitura par-a-par das views `resources/views/
temas/{v1,v2}/**`; teste HTTP real com 2 usuários (`tema_preferido=v1` e `=v2`,
`SuperAdministrador`) acessando as mesmas rotas sem prefixo, via um teste Feature
temporário (`AuditoriaParidadeTemasTest`, criado e removido só para esta auditoria —
não faz parte da suíte permanente).

**Decisão de arquitetura confirmada (`INV-RMA-05` §13, `design.md`):** Controllers são
ÚNICOS — só a view muda por tema via `view_do_tema()`/`ResolverTemaAtivo`. O escopo
ORIGINAL da Fase 8 (`tasks.md` linha 90-93) já documentava que **alertas, crédito,
relatórios, histórico e logística ficam SEM estilização por tema** (views genéricas das
Fases 1-7) — isso não é uma lacuna nova, é um limite de escopo conhecido e aceito.

## Resumo

- **Funcionalidades auditadas:** 24 (agrupadas abaixo por módulo; rotas de CRUD contam
  como 1 funcionalidade cada, não 6 rotas separadas). Migração (Fase 9) não tem UI —
  fora do escopo desta auditoria, como orientado.
- **PARIDADE VISUAL** (view própria nos dois temas, mesmos campos/ações): **12**
  (Login/logout — gateway compartilhado, ver nota —, Gerenciar usuários, Perfil/senha/
  anotação/alternar tema, 4 CRUDs de parceiros, RMA índice+busca, Novo RMA, Editar RMA,
  Ver detalhe do RMA, Ações de ciclo de vida do RMA).
- **FUNCIONAL SEM ESTILO** (funciona nos dois temas, view genérica não-temeada — dentro
  do escopo já documentado como fora da Fase 8): **8** (Painel de alertas, Fluxo de
  crédito, Relatório RCD, Relatório RMPE, Relatório RPEC, Histórico de modificação de
  RMA, Frete Porto Alegre, Boletins relacionados, Histórico de acesso — 9 itens, ver
  tabela).
- **INCONSISTENTE (achado nesta auditoria):** **0** funcionalidades com um tema tendo a
  ação e o outro não, e **0** rotas `/v1/...`/`/v2/...` trocadas por erro de cópia
  (`routes/tema-v1.php` e `routes/tema-v2.php` espelham exatamente a mesma árvore,
  diferindo só no comentário de topo).
- **1 pendência ATIVA fora do meu escopo de correção** (login-gateway — ver "Pendências
  registradas, não corrigidas" abaixo): outro agente está corrigindo em paralelo
  `resources/views/identidade/login.blade.php` e o bundle Vite associado; durante a
  auditoria, `sail test` completo pegou 1 falha transitória (`Unable to locate file in
  Vite manifest: resources/js/identidade/login.js`) que **não se repetiu** ao rodar de
  novo (308/308 passou na segunda vez, e a suíte `RenderizaTemaV1Test` isolada também
  passou) — condizente com o manifest do Vite sendo reescrito no meio da corrida por
  aquele outro trabalho, não um bug de tema. Registrado aqui, não corrigido por mim.
- **1 achado secundário, não bloqueante:** `resources/views/identidade/perfil/
  senha.blade.php` é uma view genérica ÓRFÃ — nenhum Controller a referencia mais
  (`UsuarioController::perfil` usa `view_do_tema('identidade.perfil', ...)`, que já
  inclui o formulário de troca de senha embutido nos dois temas). Está dentro de
  `resources/views/identidade/**`, área protegida nesta sessão (outro agente mexendo
  ali) — não removi, só registro.

## Tabela de paridade

| Funcionalidade | Rota(s) | LEG-RMA | View TEMA V1 | View TEMA V2 | Status |
|---|---|---|---|---|---|
| Login / logout | `login` (GET/POST), `logout` (POST) | LEG-RMA-001 | — (gateway compartilhado, não pertence a nenhum tema por decisão de produto) | — (idem) | PARIDADE VISUAL — `resources/views/identidade/login.blade.php` único, redirect pós-login sempre respeita `tema_preferido` (confirmado por teste `RenderizaTemaV{1,2}Test`) |
| Alternar tema | `tema/alternar` (POST) | LEG-RMA-006 | botão em `temas/v1/identidade/perfil.blade.php` | botão em `temas/v2/identidade/perfil.blade.php` | PARIDADE VISUAL |
| Gerenciar usuários (papel + resetar senha) | `usuarios` (GET), `usuarios/{u}` (PUT), `usuarios/{u}/resetar-senha` (POST) | LEG-RMA-005 | `temas/v1/identidade/usuarios.blade.php` | `temas/v2/identidade/usuarios.blade.php` | PARIDADE VISUAL — mesmas colunas/ações, `Papel::cases()` idêntico |
| Perfil: trocar própria senha | `perfil/senha` (PUT) | LEG-RMA-004 | embutido em `temas/v1/identidade/perfil.blade.php` | embutido em `temas/v2/identidade/perfil.blade.php` | PARIDADE VISUAL |
| Perfil: anotação pessoal | `perfil/anotacao` (PUT) | LEG-RMA-042 | embutido em `temas/v1/identidade/perfil.blade.php` | embutido em `temas/v2/identidade/perfil.blade.php` | PARIDADE VISUAL |
| Parceiros: Clientes (CRUD) | `parceiros/clientes*` (index/create/store/edit/update/destroy) | LEG-RMA-030 | `temas/v1/parceiros/index.blade.php` + `_form.blade.php` | `temas/v2/parceiros/index.blade.php` + `_form.blade.php` | PARIDADE VISUAL — mesmos campos (nome/representante/cpf_cnpj/email/telefone…) |
| Parceiros: Fabricantes (CRUD) | `parceiros/fabricantes*` | LEG-RMA-031 | idem (partial compartilhado por `$tipo`) | idem | PARIDADE VISUAL |
| Parceiros: Fornecedores (CRUD) | `parceiros/fornecedores*` | LEG-RMA-032 | idem | idem | PARIDADE VISUAL |
| Parceiros: Assistências técnicas (CRUD) | `parceiros/assistencias-tecnicas*` | LEG-RMA-033 | idem | idem | PARIDADE VISUAL |
| RMA: índice + busca | `rmas` (GET) | LEG-RMA-008 | `temas/v1/rma/index.blade.php` (lista única + busca) | `temas/v2/rma/index.blade.php` (7 abas Bootstrap, `_tabela.blade.php` reutilizada) | PARIDADE VISUAL — mecanismo de navegação DIFERE por fidelidade ao legado real (V1 sem abas, V2 com abas nativas Bootstrap 3, ver `design.md` "Mecanismo de navegação por tema"), mas busca/colunas/ações (`Ver`/`Editar`) são idênticas nos dois; classes de alerta corretamente diferenciadas por `classe_css_de_alerta()` (RN-11: V1 sem `TrSemGarantia1/2`, V2 com) |
| RMA: cadastrar novo | `rmas/create` (GET), `rmas` (POST) | LEG-RMA-007 | `temas/v1/rma/create.blade.php` + `_campos.blade.php` | `temas/v2/rma/create.blade.php` + `_campos.blade.php` | PARIDADE VISUAL — mesmos 11 campos (`name=` idênticos nos dois, conferido campo a campo) |
| RMA: editar | `rmas/{rma}/edit` (GET), `rmas/{rma}` (PUT) | LEG-RMA-010 | `temas/v1/rma/edit.blade.php` | `temas/v2/rma/edit.blade.php` | PARIDADE VISUAL |
| RMA: ver detalhe | `rmas/{rma}` (GET) | LEG-RMA-009 | `temas/v1/rma/show.blade.php` | `temas/v2/rma/show.blade.php` | PARIDADE VISUAL — mesmos 17 campos exibidos |
| RMA: ações de ciclo de vida (receber/encaminhar/concluir/arquivar/reverter/registrar solução) | `rmas/{rma}/receber`, `/encaminhar`, `/concluir`, `/arquivar`, `/reverter`, `/solucao` (todas POST) | LEG-RMA-011/012/013/014/015/017/047 | botões via `@include('rma._acoes_de_transicao')` dentro de `temas/v1/rma/show.blade.php` | idem, dentro de `temas/v2/rma/show.blade.php` | PARIDADE VISUAL (presença/condições idênticas nos 2 temas — mesmo `$registro->status->podeX()` visível dos 2 lados) — **nota:** o partial `resources/views/rma/_acoes_de_transicao.blade.php` em si não tem CSS de tema próprio (documentado no arquivo: "sem fidelidade visual, ver Fase 8"), então os botões aparecem sem estilo dentro de uma página que, fora deles, é totalmente temeada. Simétrico nos 2 temas — não é uma inconsistência entre V1/V2, é uma lacuna de estilo pontual dentro de uma tela que por outro lado tem paridade visual completa |
| Painel de alertas | `rmas-alertas` (GET) | LEG-RMA-018 a 029 | — | — | FUNCIONAL SEM ESTILO — `PainelDeAlertasController@index` usa `view('rma._painel_de_alertas')`, genérica; **dentro do escopo já documentado como fora da Fase 8** (`tasks.md` linha 92). Confirmado 200 nos 2 temas via teste HTTP |
| Fluxo de crédito (marcar disponível) | `rmas-credito` (GET), `rmas-credito/marcar` (POST) | LEG-RMA-036/048 | — | — | FUNCIONAL SEM ESTILO — `CreditoController` usa `view('rma.credito.index')`; fora do escopo Fase 8. 200 confirmado nos 2 temas |
| Relatório RCD (créditos disponíveis) | `rmas-relatorios/rcd` (GET) | LEG-RMA-037 | — | — | FUNCIONAL SEM ESTILO — `view('rma.relatorios.rcd')`; fora do escopo Fase 8. 200 confirmado nos 2 temas |
| Relatório RMPE (produtos encaminhados) | `rmas-relatorios/rmpe` (GET) | LEG-RMA-039 | — | — | FUNCIONAL SEM ESTILO — `view('rma.relatorios.rmpe')`; fora do escopo Fase 8. 302 nos 2 temas sem parâmetros de filtro (comportamento simétrico do Controller, não é assunto de tema — precisa de query string válida) |
| Relatório RPEC (produtos em estoque p/ contagem) | `rmas-relatorios/rpec` (GET) | LEG-RMA-038 | — | — | FUNCIONAL SEM ESTILO — `view('rma.relatorios.rpec')`; fora do escopo Fase 8. 200 confirmado nos 2 temas |
| Histórico de modificação de RMA | `rmas-historico` (GET) | LEG-RMA-044 | — | — | FUNCIONAL SEM ESTILO — `HistoricoDeModificacaoController` usa `view('rma.historico.index')`; fora do escopo Fase 8. 200 confirmado nos 2 temas |
| Frete Porto Alegre (logística) | `rmas-logistica/frete-porto-alegre` (GET) | LEG-RMA-040 | — | — | FUNCIONAL SEM ESTILO — `LogisticaController@fretePortoAlegre` usa `view('rma.logistica.frete-porto-alegre')`; fora do escopo Fase 8. 200 confirmado nos 2 temas |
| Boletins relacionados | `rmas/{rma}/boletins-relacionados` (GET) | LEG-RMA-041 | — | — | FUNCIONAL SEM ESTILO — `LogisticaController@boletinsRelacionados` usa `view('rma.logistica.boletins-relacionados')`; fora do escopo Fase 8 |
| Histórico de acesso (auditoria de autenticação) | `historico-de-acesso` (GET) | LEG-RMA-043 | — | — | FUNCIONAL SEM ESTILO — `HistoricoDeAcessoController@index` usa `view('identidade.historico-de-acesso.index')`; fora do escopo Fase 8. 200 confirmado nos 2 temas |
| Rotas de QA prefixadas (`/v1/...`, `/v2/...`) | espelham toda a árvore acima | — | `routes/tema-v1.php` | `routes/tema-v2.php` | PARIDADE VISUAL — os 2 arquivos têm a MESMA árvore de rotas (mesmos nomes de rota com prefixo trocado), sem inversão/cópia errada entre eles; usadas só para forçar `temaAtivo` independente de `tema_preferido`, conferido por diff estrutural dos 2 arquivos |

## Verificação HTTP real (não só leitura de código)

Teste Feature temporário (criado e apagado só para esta auditoria) autenticou 2
usuários (`SuperAdministrador`, um com `tema_preferido=v1`, outro `=v2`) e bateu nas
mesmas 18 rotas sem prefixo. Resultado — **200 nos dois temas em todas**, exceto RMPE
(302 nos dois, simétrico, por falta de parâmetro de filtro — não é um problema de
tema):

```
rmas.index                                    V1=200 V2=200
rmas.create                                   V1=200 V2=200
identidade.usuarios.index                     V1=200 V2=200
identidade.perfil.show                        V1=200 V2=200
parceiros.clientes.index                      V1=200 V2=200
parceiros.fabricantes.index                   V1=200 V2=200
parceiros.fornecedores.index                  V1=200 V2=200
parceiros.assistencias-tecnicas.index         V1=200 V2=200
rmas.alertas                                  V1=200 V2=200
rmas.credito.index                            V1=200 V2=200
rmas.historico.index                          V1=200 V2=200
rmas.logistica.frete-porto-alegre             V1=200 V2=200
rmas.relatorios.rcd                           V1=200 V2=200
rmas.relatorios.rmpe                          V1=302 V2=302
rmas.relatorios.rpec                          V1=200 V2=200
identidade.historico-de-acesso.index          V1=200 V2=200
rmas.show                                     V1=200 V2=200
rmas.edit                                     V1=200 V2=200
```

`view_do_tema()` não tem nenhum caso onde só um tema tem entrada — todo `view_do_tema('X', …)`
resolve para `temas.v1.X` ou `temas.v2.X`, e as duas árvores (`resources/views/temas/v1/**`,
`resources/views/temas/v2/**`) têm exatamente os mesmos 11 arquivos (`layout`,
`parceiros/_form`, `parceiros/index`, `rma/create`, `rma/edit`, `rma/index`, `rma/show`,
`rma/_campos`, `identidade/usuarios`, `identidade/perfil`, + `rma/_tabela` só em V2, que
é um partial interno de `rma/index` do V2, não uma tela própria).

## Pendências registradas, não corrigidas (fora da minha alçada nesta sessão)

1. **Falha transitória de `Vite manifest` no login-gateway** (`Unable to locate file in
   Vite manifest: resources/js/identidade/login.js`, vista uma vez em `sail test`
   completo, não reproduzida ao rodar de novo) — área `resources/views/identidade/**` e
   `resources/js/**` protegida nesta sessão (outro agente mexendo em paralelo no login
   gateway/painéis de aviso). Sem ação minha; registrado para acompanhamento.
2. **View órfã** `resources/views/identidade/perfil/senha.blade.php` — não referenciada
   por nenhum Controller (o fluxo real usa `view_do_tema('identidade.perfil', …)`, que
   já embute troca de senha + anotação nos dois temas). Também dentro da área
   protegida — não removida.
3. **Botões de ciclo de vida sem CSS de tema** (`rma._acoes_de_transicao.blade.php`) —
   já documentado no próprio arquivo como decisão consciente ("sem fidelidade visual,
   ver Fase 8"); simétrico nos 2 temas, não é uma inconsistência V1×V2, mas fica
   registrado aqui como candidato a uma Fase 8.1 se algum dia a fidelidade visual for
   estendida para essas ações.

## Conclusão

Nenhuma funcionalidade do sistema está genuinamente quebrada ou inacessível em um tema
e disponível no outro. As 12 telas cobertas pelo escopo original da Fase 8
(identidade/RMA núcleo/parceiros) têm paridade visual completa e comportamento
idêntico nos dois temas. As 9 funcionalidades fora desse escopo (alertas, crédito,
relatórios, histórico, logística) são igualmente acessíveis e funcionais nos dois temas
— só compartilham a mesma view genérica não-estilizada, exatamente como já estava
documentado em `openspec/changes/temas-v1-v2/tasks.md` antes desta auditoria. Não há
rota `/v1/...`/`/v2/...` trocada por erro de cópia entre `routes/tema-v1.php` e
`routes/tema-v2.php`.
