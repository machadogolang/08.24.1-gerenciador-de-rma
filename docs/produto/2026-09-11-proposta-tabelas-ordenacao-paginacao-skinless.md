# Proposta Arquitetural - Tabelas Interativas Skinless

ID: `PROP-TABELAS-SKINLESS-01`  
Data: 2026-09-11  
Referencia de produto: Requisito do dono para ordenacao e paginacao com visual identico ao legado 14.6.1 / 15.8.1  
Status: PROPOSTO / REGISTRADO NO PLANO  

---

## 1. Motivacao e Objetivo

As listagens e tabelas dos Temas V1 e V2 possuem apresentacao visual historica restrita
(classes `Tabelinha-Table`, `trcontrole1`, `tdcontrole1`, `TrZebrada1`/`TrZebrada2`, larguras
em porcentagem e alturas fixas de linha entre 20px e 30px).

A necessidade de paginacao e ordenacao dinamica nao pode resultar em um DataTables moderno
ou Bootstrap genérico (com caixas brancas, bordas arredondadas e controles fora do padrao).

**Objetivo**: Adicionar recursos de ordenacao por coluna, paginacao fluida e filtro rapido
utilizando a arquitetura **"Skinless / Headless Table"**:
- O motor de ordenacao/paginacao roda por tras (client-side leve ou DataTables sem styling).
- O visual visivel ao operador permanece 100% identico ao Legacy 14.6.1 e 15.8.1.
- Zero vazamento de estilos externos, caixas de busca modernas ou icones desproporcionais.

---

## 2. Diagnostico de Incidentes Correlatos (BUG-PAG-SVG-001)

Na tela `http://localhost:8095/rmas-historico`, a utilizacao do metodo padrao `->links()`
do Laravel resultou na injecao de SVGs de navegacao do Tailwind sem limitacao dimensional,
gerando um icone de chevron gigante que desconfigurava a pagina.

### Correcao Aplicada:
1. Criacao da view canonica `resources/views/compartilhado/paginacao.blade.php`, sem SVGs
   externos e com estilizacao nativa dos dois temas.
2. Configuracao de `Paginator::defaultView('compartilhado.paginacao')` no `AppServiceProvider`.
3. Blindagem de CSS global (`_v1-base.scss` e `_v2-base.scss`) garantindo `max-width: 100%` e
   dimensao maxima de 14px em SVGs de paginacao.
4. Cobertura comprovada por teste Playwright `tests/Browser/PaginacaoSemVazamentoSvg.spec.ts`.

---

## 3. Arquitetura da Solucao "DataTables Skinless"

### 3.1 Camada Visual (Zero Estilo Moderno)
- Sem carregar `datatables.css` ou bibliotecas com skins opinativas.
- Preservar os elementos `<table>`, `<thead>`, `<tbody>`, `<tr>` e `<td>` com suas classes
  e atributos historicos.
- Indicador de ordenacao: caracteres tipograficos discretos (`▲` / `▼` ou `↑` / `↓`) de 10px,
  incorporados no proprio `<th>` mantendo o alinhamento da coluna.
- Seletor de quantidade por pagina: no padrao `formSelectPanel` (V1) ou `formSelect3` (V2).
- Paginacao inferior: integrada a classe `.paginacao-container`.

### 3.2 Camada de Comportamento
- Ordenacao por clique no cabecalho (`<th>`):
  - Ordena tipos numericos, datas e strings em memoria (quando a tabela estiver na pagina).
  - Suporte a ordenacao server-side via query parameters transparentes (`?order_by=...&direction=...`)
    quando houver grande volume.
- Filtro rapido inline:
  - Input discreto com a classe `formInputPanel` (V1) ou `formInputSmall` (V2).
  - Posicionado sem quebrar o layout superior, respeitando a float e a largura da tela.
- Progressive Enhancement:
  - Caso o JavaScript esteja inativo ou ocorra falha no script, a tabela continua perfeitamente
    legivel e utilizavel via paginacao tradicional do servidor.

---

## 4. Superficies Elegiveis

1. `rmas-historico` (Modificacoes de RMA nos Temas V1 e V2)
2. `historico-de-acesso` (Tentativas de Login nos Temas V1 e V2)
3. `rmas-credito` (Tabela de Creditos Disponiveis de 11 colunas)
4. Listar Solicitacoes Arquivadas (Painel Controle V1)
5. Listagens operacionais principais (Entrada, Recebidos, Encaminhados, Concluidos)

---

## 5. Fases de Execucao no Plano

- [x] Fase 0: Extincao do bug de SVGs descontrolados em paginacao e criacao da view canonica compartilhada.
- [ ] Fase 1: Spike tecnico da biblioteca / script de ordenacao client-side skinless em `rmas-historico`.
- [ ] Fase 2: Validacao de geometria e testes Playwright comparando tela ordenada vs Legacy.
- [ ] Fase 3: Expansao para `historico-de-acesso` e tabelas de relatorios/creditos.
