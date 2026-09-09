# Auditoria de menus - Legacy V1/V2 × V3 V1/V2 (P4)

Data: 2026-09-09. Comparação item a item com base em `menuright.php` (V1 Legacy),
`15.8.1/inc/menu.php` (V2 Legacy), layouts V3 e runtime.

## V1 - menu superior

| Item Legacy | Item V3 | Status |
|---|---|---|
| Pag. Inicial / Home | Pag. Inicial | A |
| Novo | Novo | A |
| Localizar | Localizar | A |
| Entrada | Entrada | A |
| Encaminhado | Encaminhado | A |
| Aguardando crédito | Aguardando credito | A |
| Concluído | Concluido! | A |

## V1 - MENU lateral/sessão

| Item Legacy | Item V3 | Status |
|---|---|---|
| Fornecedores | Fornecedores | A |
| Fabricantes | Fabricantes | A |
| Assistências | Assistências | A |
| Clientes | Clientes | A |
| Controle | Controle (@gerenciar) | A |
| Créditos | Créditos | A |
| Relatórios (submenu RCD/RPEC/RMPE) | Relatórios (RCD) + **RPEC/RMPE agora no menu** | A/D corrigido |
| Usuários | Usuários (@gerenciar) | A |
| Trocar p/ 15.8.1 | Trocar p/ 15.8.1 (POST/CSRF) | A/D corrigido |
| Temporario (item oculto) | - | J (morto) |

## V2 - tabs/header

| Item Legacy | Item V3 | Status |
|---|---|---|
| Inicio | Inicio | A |
| Pesquisar | Pesquisar | A |
| Novo | Novo | A |
| Entrada | Entrada | A |
| Recebido | Recebido | A |
| Encaminhado | Encaminhado | A |
| Concluido | Concluido | A |
| Menu | Menu | A |
| Logout | Logout (POST seguro) | A/G |

## V2 - dropdown

| Item Legacy | Item V3 | Status |
|---|---|---|
| Creditos | Creditos | A |
| Assistencias | Assistencias | A |
| Fabricantes | Fabricantes | A |
| Fornecedores | Fornecedores | A |
| Clientes | Clientes | A |
| Relatorios | **Relatorio RCD + RPEC + RMPE** | A/D corrigido |
| Anotacoes | Anotacoes → Perfil (sem página dedicada) | C (P8) |
| Controle | Controle (@gerenciar) | A |
| Trocar p/ 14.6.1 | Trocar p/ 14.6.1 (POST/CSRF) | A |
| Usuários | Usuários (@gerenciar, adição consciente V3) | G |

## V2 - painel lateral

V3 `shell-v2__sidebar` reproduz as seções do Legacy (`rightmenu.php`) com cabeçalhos
colapsáveis e links para RMA. Status: A (já coberto por FRONT-003/QA).

## Conclusão P4

- Itens operacionais dos dois temas estão descobríveis no V3.
- Corrigido nesta onda: RPEC/RMPE agora têm entrada própria nos menus V1/V2.
- Resta P8 (página dedicada de anotações V2) e P9 (rotas residuais).
