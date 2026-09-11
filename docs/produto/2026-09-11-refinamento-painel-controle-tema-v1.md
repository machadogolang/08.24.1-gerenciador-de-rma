# Refinamento do Painel "Controle" - Tema V1 (Legacy 14.6.1)

Data: 2026-09-11
Referência histórica: `legacy-source/14.6.1/page/controle.php` (= `menujs-right/controle.php`) e `legacy-source/pattern/14.6.1.css`
URL operacional: `http://localhost:8095/rmas-controle`

---

## 1. Baseline e Diagnóstico Forense

O painel **Controle** do Tema V1 foi validado manualmente pelo dono, que constatou que a tela funciona, mas está **visualmente longa, pouco refinada e possui diferenças desnecessárias em relação ao Controle histórico**.

A análise forense automatizada com Playwright e comparação direta com o runtime Legacy 14.6.1 (`http://localhost:8094/14.6.1/index.php?page=controle`) revelou as seguintes discrepâncias:

| Aspecto | Legacy 14.6.1 (`controle.php`) | Novo V1 Anterior (`rmas-controle`) | Diagnóstico & Decisão |
| :--- | :--- | :--- | :--- |
| **Título da Tela** | Nenhum título `<h1>`. Começa direto no primeiro painel. | `<h1 class="titulo-v1">Controle</h1>` gerado pelo layout base. | **Desnecessário**. Empurra o conteúdo 31px para baixo. Usar `@section('omitirTituloPadrao', true)`. |
| **Marcadores de Painel** | Texto puro maiúsculo sem setas (`▶` ou `▼`). | Setas nativas de `<summary>` visíveis. | **Desnecessário**. Adicionar reset de `list-style: none` e `::-webkit-details-marker { display: none; }`. |
| **Hover e Cor dos Títulos** | Branco padrão, amarelo no `:hover`, negrito quando aberto. | Branco estático com negrito nativo. | **Ajustar**. Replicar `:hover { color: yellow; }` e transição de negrito. |
| **Painéis 5 e 6 (Deletar)** | Formulários completos com inputs `NUMERO:` / `E-MAIL:` e botões `DELETAR`. | Texto cinza solto: "Operação indisponível nesta versão." | **Degradação visual**. Restaurar os inputs e botões históricos com desabilitação segura. |
| **Painel 7 (Central de Ajuda)** | Texto corrido com fonte `Fira Mono` / `Open Sans`, sem botão. | Texto corrido + botão artificial "ABRIR CENTRAL DE AJUDA". | **Redundância**. O menu lateral já tem link "Ajuda". Remover o botão artificial. |
| **Painel 9 (Mudar Senha)** | Uma única linha horizontal: `NOVA SENHA: [input] [SALVAR]`. | 3 linhas verticais empilhadas com labels de 160px e inputs largos. | **Estica a tela**. Compactar os 3 campos em grid linear no padrão do 14.6.1. |
| **Capacidades Promovidas** | Não existiam no 14.6.1 (introduzidas na Unificação Funcional). | 3 blocos longos com parágrafos descritivos prolixos de 780px e botões largos. | **Estica a tela**. Compactar em linhas horizontais limpas com botão de 75px padrão. |
| **Tabela de Arquivados (Painel 8)** | Tabela de 6 colunas (`CHAVE`, `FABRICANTE`, `DESCRICAO`, `MODELO`, `S/N`, `OS`). | Tabela sem limitação de scroll vertical, esticando a página. | **Ajustar**. Adicionar scroll vertical compacto (`max-height: 280px`). |

---

## 2. Detalhamento dos Painéis Históricos (14.6.1)

No Legacy 14.6.1 original, existem exatamente 7 seções numeradas (1, 4, 5, 6, 7, 8, 9):

1. `#01 ADICIONAR REPRESENTANTE`:
   - `NOME:` (input text de 25 caracteres)
   - Select de tipo: `ASSISTENCIA`, `FORNECEDOR`, `FABRICANTE`
   - Botão: `ADICIONAR` (75px)
2. `#04 ARQUIVAR UMA SOLICITACAO DE RMA`:
   - `NUMERO:` (input text)
   - Botão: `ARQUIVAR` (75px)
3. `#05 DELETAR UMA SOLICITACAO DE RMA`:
   - `NUMERO:` (input text)
   - Botão: `DELETAR` (75px)
4. `#06 DELETAR UM USUARIO`:
   - `E-MAIL:` (input text)
   - Botão: `DELETAR` (75px)
5. `#07 INFORMACAO DO PROCEDIMENTO DE RMA`:
   - Texto das 3 etapas (Entrada, Processamento, Saída).
6. `#08 LISTAR SOLICITACOES DE RMA ARQUIVADAS`:
   - Mensagem `Nenhum item arquivado` ou tabela de 6 colunas com classes `.trcontrole1` e `.tdcontrole1`.
7. `#09 MUDAR SENHA`:
   - Formulário compacto com campos de senha e botão `SALVAR`.

---

## 3. Matriz de Refinamento e Implementação

### 3.1 CSS (`resources/sass/temas/_v1-base.scss`)
- Reset completo de `<summary.formTitlePanel>`:
  ```scss
  summary.formTitlePanel {
      list-style: none;
      user-select: none;
      &::-webkit-details-marker {
          display: none;
      }
      &:hover {
          color: yellow;
      }
  }
  ```
- Altura e margens idênticas ao Legacy: `margin: 0 0 10px; font-size: 13px; font-weight: 300;`.
- Estilos de `.formInputPanel`, `.formSelectPanel` e `.formButtonEnviarPanel` rigorosamente alinhados.

### 3.2 View (`resources/views/temas/v1/rma/controle.blade.php`)
- `@section('omitirTituloPadrao', true)` para ocultar o H1 visual.
- Restaurar estrutura visual dos painéis de exclusão `#05` e `#06`.
- Compactar painel `#09 MUDAR SENHA`.
- Remover botão redundante do painel `#07`.
- Compactar os painéis de auditoria/usuário sem parágrafos prolixos.
