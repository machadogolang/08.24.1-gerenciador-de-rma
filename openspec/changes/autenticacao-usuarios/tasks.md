# Tasks — Identidade

- [ ] `database/migrations/2026_08_25_000000_add_identidade_fields_to_users_table.php`
- [ ] `database/migrations/2026_08_25_000001_create_tentativas_de_acesso_table.php`
- [ ] `app/Identidade/Dominio/Papel.php`
- [ ] `app/Identidade/Dominio/TemaPreferido.php`
- [ ] `app/Identidade/Dominio/ResultadoDeAcesso.php` (enum: Permitido/Negado/Bloqueado)
- [ ] `app/Identidade/Aplicacao/AutenticarUsuario.php`
- [ ] `app/Identidade/Aplicacao/AlternarTemaPreferido.php`
- [ ] `app/Models/User.php` — cast `papel`/`tema_preferido`
- [ ] `app/Models/TentativaDeAcesso.php`
- [ ] `app/Policies/UserPolicy.php`
- [ ] `app/Http/Controllers/Identidade/SessaoController.php`
- [ ] `app/Http/Controllers/Identidade/TemaPreferidoController.php`
- [ ] Rotas em `routes/web.php`
- [ ] `resources/views/auth/login.blade.php` (mínimo funcional, sem fidelidade visual)
- [ ] `database/factories/UserFactory.php` — ajustar para gerar `papel`/`tema_preferido`
- [ ] `database/seeders/UserSeeder.php` — pelo menos 1 usuário por papel, para QA manual
- [ ] `tests/Feature/Identidade/AutenticacaoTest.php`
- [ ] `tests/Feature/Identidade/PermissaoTest.php`
- [ ] `tests/Feature/Identidade/AlternarTemaTest.php`
- [ ] `tests/Unit/Identidade/PapelTest.php` (os 4 métodos do enum, sem banco)
- [ ] `composer test` / `sail test` verde
- [ ] Atualizar `docs/produto/paridade-v2-v3.md`: `LEG-RMA-001`, `LEG-RMA-006`,
      `LEG-RMA-043` passam de `PENDENTE` para `EM IMPLEMENTAÇÃO`/`PARIDADE` conforme
      o critério de aceite for validado
- [ ] Atualizar `docs/produto/checklist-master-v3.md` (Fase 1 concluída)
- [ ] Commit `#F1 - Identidade (autenticação, papéis, tema preferido)`
