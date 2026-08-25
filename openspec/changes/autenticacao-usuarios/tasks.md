# Tasks — Identidade

- [x] `database/migrations/2026_08_25_000000_add_identidade_fields_to_users_table.php`
- [x] `database/migrations/2026_08_25_000001_create_tentativas_de_acesso_table.php`
- [x] `app/Identidade/Dominio/Papel.php`
- [x] `app/Identidade/Dominio/TemaPreferido.php`
- [x] `app/Identidade/Dominio/ResultadoDeAcesso.php` (enum: Permitido/Negado/Bloqueado)
- [x] `app/Identidade/Aplicacao/AutenticarUsuario.php`
- [x] `app/Identidade/Aplicacao/AlternarTemaPreferido.php`
- [x] `app/Models/User.php` — cast `papel`/`tema_preferido`
- [x] `app/Models/TentativaDeAcesso.php`
- [x] `app/Policies/UserPolicy.php`
- [x] `app/Http/Controllers/Identidade/SessaoController.php`
- [x] `app/Http/Controllers/Identidade/TemaPreferidoController.php`
- [x] Rotas em `routes/web.php`
- [x] `resources/views/auth/login.blade.php` (mínimo funcional, sem fidelidade visual)
- [x] `database/factories/UserFactory.php` — ajustar para gerar `papel`/`tema_preferido`
- [x] `database/seeders/UserSeeder.php` — pelo menos 1 usuário por papel, para QA manual
- [x] `tests/Feature/Identidade/AutenticacaoTest.php`
- [x] `tests/Feature/Identidade/PermissaoTest.php`
- [x] `tests/Feature/Identidade/AlternarTemaTest.php`
- [x] `tests/Unit/Identidade/PapelTest.php` (os 4 métodos do enum, sem banco)
- [x] `app/Identidade/Aplicacao/TrocarPropriaSenha.php` (TEMA V1 como especificação, RN-21)
- [x] `app/Identidade/Aplicacao/ResetarSenhaDeUsuario.php`
- [x] `app/Identidade/Aplicacao/AtualizarAnotacaoPessoal.php`
- [x] `app/Http/Controllers/Identidade/UsuarioController.php`
- [x] `app/Http/Controllers/Identidade/AnotacaoPessoalController.php`
- [x] `resources/views/identidade/usuarios/index.blade.php`
- [x] `resources/views/identidade/perfil/senha.blade.php`
- [x] `tests/Feature/Identidade/TrocarPropriaSenhaTest.php`
- [x] `tests/Feature/Identidade/ResetarSenhaDeUsuarioTest.php`
- [x] `tests/Feature/Identidade/GerenciarUsuariosTest.php`
- [x] `tests/Feature/Identidade/AnotacaoPessoalTest.php`
- [ ] Registrar pendência de `LEG-RMA-002` (autocadastro com convite) sem decidir —
      perguntar ao usuário antes de implementar (ver `proposal.md`). **PENDENTE,
      deliberadamente não implementado nesta change — decisão de produto, não de
      arquitetura, aguardando o usuário escolher opção A ou B (ver `proposal.md`).**
- [x] `composer test` / `sail test` verde (36/36 testes, 91 assertions)
- [x] Atualizar `docs/produto/paridade-v2-v3.md`: `LEG-RMA-001`, `LEG-RMA-006`,
      `LEG-RMA-043` passam de `PENDENTE` para `EM IMPLEMENTAÇÃO`/`PARIDADE` conforme
      o critério de aceite for validado
- [x] Atualizar `docs/produto/checklist-master-v3.md` (Fase 1 concluída)
- [x] Commit `#F1 - Identidade (autenticação, papéis, tema preferido)`
