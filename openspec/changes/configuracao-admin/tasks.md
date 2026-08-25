# Tasks — Configuração de admin

**Pré-requisito:** Fase 10/QA de paridade da Trilha A concluída e commitada
(`INV-RMA-05` §15). Não iniciar nenhuma task abaixo antes disso.

- [ ] `database/migrations/{data}_create_configuracoes_table.php`
- [ ] `app/Configuracao/Dominio/ConfiguracaoDeNotificacaoDeRma.php`
- [ ] `app/Configuracao/Dominio/ConfiguracaoDeAlertaDeUrgencia.php`
- [ ] `app/Configuracao/Dominio/ConfiguracaoDeConsolidacaoDeFrete.php`
- [ ] `app/Configuracao/Dominio/ConfiguracaoEfetivaDeNotificacao.php` (DTO)
- [ ] `app/Configuracao/Dominio/ConfiguracaoEfetivaDeAlertaDeUrgencia.php` (DTO)
- [ ] `app/Configuracao/Dominio/ConfiguracaoEfetivaDeConsolidacaoDeFrete.php` (DTO)
- [ ] `app/Configuracao/Dominio/RepositorioDeConfiguracoes.php` (interface)
- [ ] `app/Configuracao/Infraestrutura/ConfiguracoesEmBanco.php` (implementa a interface)
- [ ] `app/Configuracao/Infraestrutura/ConfiguracaoServiceProvider.php` (bindings
      opcionais nos 3 consumidores — ver `design.md` §Desacoplamento)
- [ ] Registrar `ConfiguracaoServiceProvider` em `bootstrap/providers.php`
- [ ] `app/Models/Configuracao.php` (Eloquent, uso interno da Infraestrutura só)
- [ ] `app/Configuracao/Aplicacao/PublicarConfiguracaoDeNotificacao.php`
- [ ] `app/Configuracao/Aplicacao/PublicarConfiguracaoDeAlertaDeUrgencia.php`
- [ ] `app/Configuracao/Aplicacao/PublicarConfiguracaoDeConsolidacaoDeFrete.php`
- [ ] `app/Configuracao/Aplicacao/ObterConfiguracaoEfetivaDeNotificacao.php`
- [ ] `app/Configuracao/Aplicacao/ObterConfiguracaoEfetivaDeAlertaDeUrgencia.php`
- [ ] `app/Configuracao/Aplicacao/ObterConfiguracaoEfetivaDeConsolidacaoDeFrete.php`
- [ ] Alterar `app/Rma/Aplicacao/EnviarNotificacaoDeConclusao.php` — parâmetro opcional
      `?string $destinatario = null`, fallback `?? config('rma.notificacoes.conclusao')`
- [ ] Alterar `app/Rma/Aplicacao/Alertas/UrgenciaPorThreshold.php` — parâmetro opcional
      `?float $thresholdEmReais = null`, fallback `?? 75.00`
- [ ] Alterar `app/Rma/Aplicacao/ConsolidarFretePorCidade.php` — parâmetro opcional
      `?string $cidade = null`, fallback `?? 'PORTO ALEGRE'` (a constante `CIDADE` vira
      o default do parâmetro, não é removida do arquivo — continua sendo a verdade de
      fallback)
- [ ] `app/Policies/ConfiguracaoPolicy.php`
- [ ] `app/Http/Controllers/Configuracao/ConfiguracaoController.php` — `edit`/`update`
- [ ] `resources/views/layouts/admin.blade.php` (layout mínimo, scaffold Tailwind
      padrão, sem tema V1/V2/V3)
- [ ] `resources/views/configuracao/edit.blade.php`
- [ ] Rotas em `routes/web.php` (`configuracao.edit`, `configuracao.update`, dentro do
      grupo `auth`, autorizadas por `ConfiguracaoPolicy`)
- [ ] `tests/Feature/Configuracao/PublicarConfiguracaoDeNotificacaoTest.php`
- [ ] `tests/Feature/Configuracao/PublicarConfiguracaoDeAlertaDeUrgenciaTest.php`
- [ ] `tests/Feature/Configuracao/PublicarConfiguracaoDeConsolidacaoDeFreteTest.php`
- [ ] `tests/Feature/Configuracao/ConfiguracaoControllerTest.php` (autorização —
      `Papel` sem `podeGerenciarUsuarios()` recebe 403)
- [ ] `tests/Unit/Rma/Alertas/UrgenciaPorThresholdComOverrideTest.php` — prova de que o
      threshold publicado sobrepõe o fallback `75.00`
- [ ] `tests/Unit/Rma/ConsolidarFretePorCidadeComOverrideTest.php` — mesma prova para
      cidade
- [ ] `tests/Feature/Rma/EnviarNotificacaoDeConclusaoComOverrideTest.php` — mesma prova
      para destinatário
- [ ] **Regressão obrigatória:** rodar `sail test` completo — confirmar que
      `UrgenciaPorThresholdTest.php` (Fase 5) e qualquer teste de
      `EnviarNotificacaoDeConclusao` (Fase 7) já existentes **não são alterados** e
      continuam verdes sem argumento passado
- [ ] **Prova de desacoplamento:** comentar/remover o registro de
      `ConfiguracaoServiceProvider` em `bootstrap/providers.php` temporariamente e rodar
      `sail test` de novo — deve continuar 100% verde (prova de que `Rma` funciona sem
      `Configuracao` ativo); reverter o comentário antes de commitar
- [ ] Atualizar `docs/produto/backlog-evolutivo.md` (`EVO-CONF-001` — marcar ponteiro
      para esta change como especificada)
- [ ] Commit `#CONFIG-ADMIN - Painel de configuração de admin (Trilha B)`
