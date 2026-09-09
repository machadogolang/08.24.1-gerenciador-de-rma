# Mapa tenant-scoped — EVO-SAAS-001 (inventário por evidência)

Data: 2026-09-09. Complementa `openspec/changes/saas-multiempresa/design.md` e
`INV-RMA-07` §4 com o inventário real de models/consumidores do código atual.

## Tabelas tenant-scoped (recebem `tenant_id`)

| Tabela | Model | Consumidores diretos relevantes |
|---|---|---|
| `clientes` | `App\Models\Cliente` | `ClienteController`, `EncontrarOuCriarCliente`, `PainelLateral\Clientes`, views/`@can` indiretas |
| `fabricantes` | `App\Models\Fabricante` | `FabricanteController`, `EncontrarOuCriarFabricante`, `View::composer` de `_form_novo`, `RmaController` (mapas), `ImportarFabricantes`, `ResolverDestinatario` |
| `fornecedores` | `App\Models\Fornecedor` | `FornecedorController`, `EncontrarOuCriarFornecedor`, `CicloDeVidaController`, `CriarRma`/`EditarRma`, `RmaController` mapas, migrador |
| `assistencias_tecnicas` | `App\Models\AssistenciaTecnica` | `AssistenciaTecnicaController`, `EncontrarOuCriarAssistenciaTecnica`, `CicloDeVidaController`, `ConsolidarFretePorCidade`, `TransportePortoAlegre`, migrador |
| `rmas` | `App\Models\Rma` | `RmasEmBanco` (fonte única de escrita/leitura do agregado), alertas, painéis laterais, relatórios, histórico/logística/controle, `RmaController`, `ListagensPorStatusController`, `RelatorioController`, `CreditoController`, migrador |
| `modificacoes_de_rma` | `App\Models\ModificacaoDeRma` | `RegistrarModificacaoDeRma` (listener), `HistoricoDeModificacaoController`, migrador |

## Globais nesta fase

- `users` (multi-tenant via vínculo `company_user`; não recebe `tenant_id`).
- `companies` e `company_user` (novas, próprias da fundação).
- `tentativas_de_acesso` — decisão S8.3: sem escopo por tenant nesta fase (registro por
  usuário; agregação cross-tenant é fase futura).
- `password_reset_tokens`, `sessions`, `cache`, `jobs` (framework).

## Pontos de aplicação por construção

1. **Eloquent direto (Parceiros e leituras de RMA):** trait `PertenceATenant` + Global
   Scope nos models acima cobre controllers, casos de uso e composições de view.
2. **Fronteira Rma:** `RmasEmBanco` é o único ponto que materializa o agregado; além do
   escopo no model, o repositório deve ser blindado no próprio `buscar()`/`criar()`/
   `atualizar()` (defesa em profundidade exigida para o agregado central).
3. **Migrador:** todos os `Importadores` que tocam tabela tenant-scoped e o
   `ResolverDestinatario` devem receber/garantir `CellSystem` como tenant destino.
4. **Views/policies:** políticas usam `User`/classes de model como âncora e não mudam
   com tenant; o vínculo ativo (S9) fornece o papel.

## Regra de não-uso

`tenant_id` jamais entra em `$fillable` nem em `request->validate()`. Qualquer empresa
em rota/query é resolvida a partir do vínculo do usuário autenticado via
`TenantContext`, nunca do input.
