# Proposal - Unificacao Funcional dos Temas V1/V2

Mudanca canonica de contrato de produto (decisao do dono, 2026-09-10).
Conceito: "Mesma capacidade funcional, apresentacao historica por tema".

## 1. Contexto e Problema

Os legados 14.6.1 (Tema V1) e 15.8.1 (Tema V2) foram construidos historicamente como experiencias distintas operando sobre o mesmo banco de dados (`cellsyst_rma`). Ao longo da evolucao do software legado:
- Funcionalidades surgiram apenas em uma versao (ex.: hub estatistico de relatorios no 15.8.1, relatorios fiscais RCD/RPEC/RMPE no 14.6.1, fila dedicada de Recebidos no 15.8.1);
- Funcionalidades quebraram em uma versao mantendo-se ativas na outra (ex.: arquivar quebrado no 14.6.1 mas funcional no 15.8.1; troca de senha quebrada no 15.8.1 mas funcional no 14.6.1);
- Paineis e auditorias ficaram acessiveis por uma interface e nao por outra (ex.: logs de autenticacao e modificacao no 15.8.1);
- Dados operacionais foram expostos de maneiras diferentes (ex.: prioridade e threshold de R$ 75 no 15.8.1, destinatarios com frete e CFOP no 14.6.1).

A abordagem inicial buscou paridade visual e funcional estritamente espelhada por versao (V1 = 14.6.1, V2 = 15.8.1). Isso criou uma assimetria inaceitavel de produto: ao alternar de tema, o operador perdia capacidades essenciais que o sistema possui.

## 2. Nova Regra Canonica do Dono (2026-09-10)

1. **VISUAL:**
   - O Tema V1 continua com identidade, layout, navegacao, densidade e padroes derivados do 14.6.1.
   - O Tema V2 continua com identidade, layout, navegacao, densidade e padroes derivados do 15.8.1.
   - O Tema V3 continua sendo evolucao propria com a direcao Console Operacional Dark.

2. **FUNCIONALIDADE:**
   - O Tema V1 e o Tema V2 devem oferecer O MESMO CONJUNTO FUNCIONAL.
   - Esse conjunto e a uniao das capacidades funcionais vivas existentes nos legados 14.6.1 e 15.8.1 mais as capacidades modernas aprovadas:
     `CATALOGO_FUNCIONAL = capacidades_vivas(14.6.1) UNION capacidades_vivas(15.8.1) UNION capacidades_modernas_aprovadas`
   - Se uma capacidade funcional existe em apenas um legado, ela deve ser disponibilizada tambem no outro tema novo.
   - Ao transportar uma funcionalidade para outro tema, **NUNCA transportar o layout do tema de origem**; desenhar como a capacidade deve existir dentro da linguagem visual e ergonomia do tema receptor.
   - Futuramente, o Tema V3 tambem oferecera o mesmo catalogo antes do `T3-GATE`.

3. **O QUE MUDA AO TROCAR DE TEMA:**
   - Muda: layout, navegacao, densidade, cor, composicao e estilo de interacao.
   - **NAO MUDA:** capacidades, permissoes, regras, dados disponiveis, acoes possiveis, relatorios disponiveis, auditorias disponiveis e resultado de negocio.

## 3. O que entra nesta mudanca

- **Fila de Recebidos no Tema V1 (`GAP-V1-01`):** Criacao de listagem e link de navegacao na linguagem V1 para visualizar exclusivamente os RMAs recebidos.
- **Relatorios Cruzados (`GAP-V2-01..03` e `GAP-V1-06`):**
  - No Tema V1: integracao do Hub Estatistico (Situacao, Resolucao, Origem, NF, Dados do Sistema, Series) no painel de Relatorios V1.
  - No Tema V2: acesso descobrvel e funcional aos relatorios RCD, RPEC e RMPE dentro da experiencia de Relatorios do 15.8.1.
- **Auditoria e Logs no Tema V1 (`GAP-V1-03..05`):** Acesso a logs de autenticacao, logs de modificacao de RMA e detalhe do log (`Ver`) integrados ao Controle/menu V1.
- **Creditos Tabulares no Tema V1 (`GAP-V1-07`):** Visualizacao tabular rica de creditos disponiveis no padrao visual V1.
- **Novo Usuario pelo Operador no Tema V1 (`GAP-V1-02`):** Tela de cadastro administrativo de usuario no padrao V1.
- **Urgencia e Prioridade no Tema V1 (`GAP-V1-08/09`):** Alerta operacional de prazo com threshold de R$ 75 e campo/indicador de prioridade no Tema V1.
- **Logistica e Destinatarios Cruzados (`GAP-V1-11` e `GAP-V2-04`):** Transporte Porto Alegre no V1; consulta com frete e CFOP no V2.
- **RMAs Associados e Ajuda (`GAP-V1-10` e `GAP-V2-05`):** RMAs associados ao parceiro no V1; procedimento operacional no V2.
- **Suite de Contrato Funcional e Descobribilidade:** Testes automatizados garantindo que toda capacidade promovida esta ativa e alcancavel em ambos os temas.

## 4. O que nao entra

- **Nao promover codigo morto:** superficies sem funcao (ex.: `retornou.php` de 0 bytes, `marcarcomo.php` duplicado, sub-rotas de creditos inexistentes, stubs de e-mail).
- **Nao reproduzir vulnerabilidades ou comportamentos inseguros:** autocadastro com segredo hardcoded (`signup.php` do 14.6.1) fica deferido; hard delete sem auditoria e rejeitado.
- **Nao copiar HTML/CSS entre temas:** cada tema deve construir suas proprias views Blade e estilos locais respeitando sua identidade historica.
- **Nao quebrar a matriz forense:** a matriz forense (`docs/produto/2026-09-10-matriz-forense-paridade-legacy-v1-v2.md`) continua existindo para auditar o espelhamento historico; a matriz de uniao (`docs/produto/2026-09-10-matriz-uniao-funcional-temas.md`) governa a equivalencia cruzada de produto.
