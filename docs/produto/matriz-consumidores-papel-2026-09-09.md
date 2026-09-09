# Matriz de consumidores de `users.papel` - S9.1 (EVO-SAAS-001)

Data: 2026-09-09. Levantamento dirigido por `rg` sobre `app/`, `resources/`,
`routes/`, `database/` e `tests/` (padrões: `->papel`, `'papel'`, `Papel::`,
`where('papel'`, `papel(`). O papel passa a ser lido do vínculo ativo
`company_user`; `users.papel` fica como compatibilidade/transição até S9.8.

| Categoria / arquivo | Uso antes | Uso agora (S9) | Status |
|---|---|---|---|
| `app/Policies/{User,Rma,Cliente,Fabricante,Fornecedor,AssistenciaTecnica}Policy.php` | `$ator->papel` | `$ator->papelAtivo()` (alvo: `papelNaEmpresa()`) | Adaptado |
| `app/Http/Controllers/Identidade/{SessaoController,UsuarioController}.php` | `$usuario/$ator->papel`; `$usuario->update(['papel'])` | `papelAtivo()`; grava no vínculo; espelho global só single-company | Adaptado |
| `app/Identidade/Aplicacao/AutenticarUsuario.php` | `$usuario->papel->podeAutenticar()` | vínculos ativos; fallback sem vínculo | Adaptado (semântica registrada) |
| `app/Identidade/Aplicacao/ResetarSenhaDeUsuario.php` | `$ator->papel`, `$alvo->papel` | `papelAtivo()` + alvo `papelNaEmpresa()` | Adaptado |
| `app/Rma/Aplicacao/{Receber,Encaminhar,Concluir,Arquivar,Reverter,MarcarCredito,RegistrarSolucao}.php` | `$ator->papel` | `$ator->papelAtivo()` | Adaptado |
| `app/Rma/Aplicacao/EnviarNotificacaoDeTentativaNaoPermitida.php` | `$evento->ator->papel` | `$evento->ator->papelAtivo()` | Adaptado |
| Views de perfil (V1/V2/fallback) | `$usuario->papel->name` | `$usuario->papelAtivo()->name` | Adaptado |
| Views administrativas de usuários | `@selected($usuario->papel === $papel)` | papel do alvo na empresa (`papelNaEmpresa`) | Adaptado |
| `database/seeders/QaSeeder.php` | `where('papel', Papel::Operador)` | `whereHas` vínculo ativo | Adaptado |
| `database/factories/UserFactory.php` | cria `papel` global | cria global + vínculo CellSystem espelhando | Compatibilidade |
| `database/migrations/000004`/`ImportarUsuarios` | leitura/escrita legada | preserva origem histórica | Compatibilidade |
| `tests/**` | muitos `factory(['papel'=>...])` | continuam válidos via factory + vínculo | Compatibilidade |

## Uso restante intencional de `users.papel`

- `UserFactory` e `UserSeeder`: espelho do papel inicial para o vínculo.
- Migração `000004` e `ImportarUsuarios`: origem histórica/backfill.
- Fallbacks sem contexto web (console/teste) em `User::papelAtivo()`, policies com
  alvo sem vínculo e autenticação sem vínculo.
- Coluna e cast em `User` permanecem até S9.8 provar zero consumidores de runtime.

## Decisão de login (S9)

Sem produto exigindo seletor no login, adotou-se semântica reversível e documentada:
autentica se houver pelo menos um vínculo ativo com papel que permite autenticar;
nega se todos ativos estiverem `Bloqueado`; usuário sem vínculo usa o fallback legado.
`[DECISAO-PENDENTE-S9-AUTH]` registrada caso a regra de produto precise ser outra.
