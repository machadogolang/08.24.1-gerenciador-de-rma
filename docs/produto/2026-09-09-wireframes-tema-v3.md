# Wireframes textuais do Tema V3

Data: 2026-09-09. ASCII simples para explicar hierarquia. NAO sao layout
aprovado nem codigo. Fonte conceitual:
`2026-09-09-refinamento-evo-ux-001-tema-v3-console-operacional.md`.

## 1. Shell desktop

```
+---------------------------------------------------------------+
| [logo]  CellSystem RMA      [busca rapida.........] [perfil]  |
+---------+-----------------------------------------------------+
| Rail    |  conteudo                                          |
|         |                                                     |
| Inicio  |  cabecalho da area                                  |
| RMAs    |  action bar / tabs / filtros                        |
| Parceiros                                                      |
| Relatorios                                                     |
| Admin   |                                                      |
| Ajuda   |                                                      |
|         |                                                      |
| recolher|                                                      |
+---------+-----------------------------------------------------+
```

## 2. Shell mobile

```
+-------------------------------------------+
| [menu] CellSystem RMA        [perfil]     |
+-------------------------------------------+
| cabecalho da area                         |
| action bar persistente                    |
|  conteudo em 1 coluna                     |
|                                           |
|  [salvar/cancelar fixos no form]          |
+-------------------------------------------+
```

Drawer abre a esquerda com Inicio/RMAs/Parceiros/Relatorios/Admin/Ajuda.

## 3. Dashboard

```
+-----------------------------------------------+
| Novo RMA    [busca........]                   |
+-----------------------------------------------+
| FILAS                                        |
| Entrada 12 | Recebidos 8 | Encaminhados 20   |
| Aguardando 3 | Concluidos 15                 |
+-----------------------------------------------+
| ALERTAS                                      |
| [urgente] 3 | [prazo] 5 | [garantia] 1       |
+-----------------------------------------------+
| ATIVIDADE RECENTE                            |
| RMA #.. movido para Encaminhado ...          |
+-----------------------------------------------+
```

## 4. Lista RMA

```
+--------------------------------------------------+
| RMAs        [filtros] [status segmentos] [novo]  |
+--------------------------------------------------+
| Numero | Status | Descricao | Parceiro | Data | A |
| #1234  | Entrada| Camera    | Intelbras| 09/09| Ver|
| #1235  | Receb  | NVR       | D-Link   | 09/09| Ver|
+--------------------------------------------------+
| paginacao quando volume justificar                |
+--------------------------------------------------+
```

Mobile vira cartao:

```
+-----------------------------------------------+
| #1234  ENTRADA                                |
| Camera Dome                                   |
| Intelbras | 09/09                             |
| [Ver] [Proxima acao]                          |
+-----------------------------------------------+
```

## 5. Detalhe RMA

```
+------------------------------------------------------------+
| RMA #1234    ENCAMINHADO    Prioridade alta                |
| Cliente XYZ                         [CONCLUIR]            |
+------------------------------------------------------------+
| Resumo | Fiscal | Logistica | Historico                    |
+------------------------------------------------------------+
| Resumo: descricao, produto, origem, destinatario, solucao  |
+------------------------------------------------------------+
```

## 6. Novo/editar RMA

```
+------------------------------------------------+
| Novo RMA                     [salvar][cancelar] |
+------------------------------------------------+
| IDENTIFICACAO                                   |
| Descricao [..........] Modelo [..........]      |
| S/N [..........] P/N [..........]               |
| ORIGEM / PARCEIROS                              |
| Cliente [..........] Fabricante [..........]    |
| FISCAL                                          |
| NF compra [..] data [..] NF venda [..] data [..]|
| OPERACAO                                        |
| Prioridade [select] estoque [toggle]            |
| OBSERVACOES                                     |
| [textarea..........................]            |
+------------------------------------------------+
```

## 7. Parceiros

```
+------------------------------------------------+
| Parceiros > Fornecedores    [busca] [novo]      |
+------------------------------------------------+
| Nome  | Representante | Contato | RMAs | Acoes  |
| ...                                             |
+------------------------------------------------+
```

Detalhe do parceiro mostra dados + RMAs relacionados em secoes.

## 8. Usuarios

```
+----------------------------------------------------+
| Administracao > Usuarios          [novo usuario]    |
+----------------------------------------------------+
| Nome | Email | Empresa | Papel | Ultimo acesso | A  |
| ...                                                |
+----------------------------------------------------+
```

Trocar papel e resetar senha sao acoes contextuais (drawer/pagina pequena).

## 9. Relatorios

```
+---------------------------------------------------+
| Relatorios                                       |
+---------------------------------------------------+
| [RCD] Creditos disponiveis        [abrir]         |
| [RPEC] Produtos para contagem     [abrir]         |
| [RMPE] Produtos encaminhados      [abrir]         |
+---------------------------------------------------+
```

## 10. Administracao

```
+------------------------------------------------+
| Administracao                                  |
+------------------------------------------------+
| Usuarios | Historico de acesso | Ajuda         |
| Configuracoes administrativas (futuro)         |
+------------------------------------------------+
```

Controle V1 fica decomposto: cada capacidade mora na area correspondente.

## Nota

Wireframes servem apenas para comunicar hierarquia e agrupamento. A validacao
visual acontece na implementacao, com prototipos e testes em viewport real.
