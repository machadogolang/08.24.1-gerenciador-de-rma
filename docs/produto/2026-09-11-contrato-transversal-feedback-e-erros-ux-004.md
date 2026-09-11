# Contrato Transversal de Feedback, Erros HTTP e Estado Vazio (UX-004 / P8)

**Data:** 2026-09-11  
**Autor:** Pair Programming Senior  
**Status:** IMPLEMENTACAO E HOMOLOGACAO  
**Referencia:** `PLANO-ATAQUE.md` (P8), `docs/produto/checklist-master-v3.md` (H-037 / UX-004)

---

## 1. Contexto e Necessidade

O item `P8` / `UX-004` prevê o fechamento do contrato transversal de mensagens de retorno, validação, estados vazios e páginas de erro HTTP (`403`, `404`, `500`), garantindo que:

1. Mensagens de feedback (`status`, `sucesso`/`success`, `erro`/`error`, `aviso`/`warning`) sejam renderizadas de forma uniforme em todos os fluxos dos temas V1, V2 e V3.
2. Erros de validação globais (`$errors->any()`) tenham ponto consistente de apresentação quando não tratados inline.
3. Erros HTTP (`403 Forbidden`, `404 Not Found`, `500 Internal Error`) possuam páginas dedicadas, com comunicação clara e amigável ao operador, sem vazamento de stack traces em produção, e com links rápidos de retorno ao sistema.
4. O padrão histórico do Tema V1 (`.centrodeavisos`, `.nenhumencontrado`), do Tema V2 (`.centrodeavisos`) e do Tema V3 (`role="status"`, `role="alert"`, `.cartao`) seja integralmente respeitado.

---

## 2. Especificacao do Componente Compartilhado de Feedback

Criacao de `resources/views/compartilhado/mensagens_feedback.blade.php`:

- Leitura segura de chaves de sessão:
  - Sucesso: `session('status')`, `session('sucesso')`, `session('success')`
  - Erro: `session('erro')`, `session('error')`
  - Aviso: `session('aviso')`, `session('warning')`
  - Validação: `$errors->all()` quando existirem erros de validação
- Apresentação condicional por tema ativo (`$temaAtivo`):
  - **Tema V1**: Renderiza `<p class="centrodeavisos">` com separação sutil para sucesso/erro.
  - **Tema V2**: Renderiza `.centrodeavisos` preservando tipografia e cores históricas.
  - **Tema V3**: Renderiza `.cartao` com atributos ARIA (`role="status"`, `role="alert"`), contraste e alinhamento do console operacional.

---

## 3. Especificacao das Paginas de Erro HTTP

- `resources/views/errors/403.blade.php`:
  - Mensagem: "403 - Acesso Não Autorizado. Sua conta não possui permissão para acessar este recurso ou executar esta operação."
  - Ação: Botão "Voltar à Página Inicial".
- `resources/views/errors/404.blade.php`:
  - Mensagem: "404 - Página ou Registro Não Encontrado. O endereço acessado ou o registro solicitado não foi localizado."
  - Ação: Botão "Voltar ao Início" ou "Voltar à listagem de RMAs".
- `resources/views/errors/500.blade.php`:
  - Mensagem: "500 - Instabilidade Temporária. Ocorreu uma falha no processamento da solicitação."
  - Ação: Botão "Tentar Novamente" e link de retorno seguro.

---

## 4. Plano de Testes

1. **PHPUnit Feature (`ContratoTransversalFeedbackTest.php`)**:
   - Acesso negado gera status 403 e renderiza a view `errors.403`.
   - Recurso inexistente gera status 404 e renderiza a view `errors.404`.
   - Mensagens de feedback (`sucesso`, `erro`, `status`) são exibidas corretamente no layout dos temas.
2. **Playwright Browser (`ContratoTransversalFeedback.spec.ts`)**:
   - Navegação até recurso 404 exibe tela estilizada e permite retorno seguro via clique.
   - Submissão com erro ou sucesso exibe feedback visível e acessível.
