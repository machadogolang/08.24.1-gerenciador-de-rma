# Parecer Executivo — Decisões de Arquitetura, Segurança e Domínio (Trilha A / Fase 10)

Data-base: 2026-09-04  
Status: **HOMOLOGADO E CONCLUÍDO**  
Escopo: Itens `ETA-03`, `ETA-04` e `ETA-05` do `PLANO-ATAQUE.md`, respondendo a `DECISAO C-01`, `F10-COB-03`, `F10-COB-04` e ao achado `CP14`.

---

## 1. Contexto e Objetivos

Durante a evolução do projeto e a Fase 10 (QA de Paridade e Fechamento da Trilha A), três decisões materiais de arquitetura e conformidade de produto permaneceram em aberto no `checklist-master-v3.md`:
1. `DECISAO C-01` (`LEG-RMA-002`): modelo de cadastro de usuários com chave de convite vs provisionamento administrativo.
2. `F10-COB-03` e `F10-COB-04` (`VIS-V1-011` / `VIS-V1-012`): operações destrutivas de hard-delete em RMAs e Usuários do sistema legado.
3. `CP14`: correção de mapeamento de domínio em `Rma::classeDeAlerta()` para devolução de `ClasseDeAlerta::Urgente` e `SemGarantia`.

Este documento consolida as análises técnicas, a conformidade legal/fiscal e o registro definitivo de cada decisão no repositório.

---

## 2. Decisão C-01 (`LEG-RMA-002`) — Provisionamento Seguro de Usuários

### 2.1 Pista Histórica
No legado 14.6.1 (`inc/signup.php`), existia um formulário público de cadastro de usuários onde o requerente informava dados pessoais e um campo "Chave de Convite". Essa chave consistia em uma constante de texto estática fixada diretamente no código-fonte PHP. No legado 15.8.1, o arquivo sequer estava integrado ao fluxo de navegação.

### 2.2 Análise de Risco e Segurança
A permissão de autocadastro público mediante chave compartilhada ou estática é uma vulnerabilidade inaceitável em um sistema moderno de gestão de garantias e ativos corporativos. Permitiria que qualquer agente externo criasse credenciais ativas na aplicação.

### 2.3 Resolução Adotada (Opção B - Homologada)
- **Produção na Trilha A:** O provisionamento de contas no V3 é restrito estritamente a administradores (`Supervisor` ou `SuperAdministrador`) por meio de `UsuarioController` e do caso de uso `CriarUsuario` / `GerenciarUsuarios`, sob a guarda de `UserPolicy`.
- **Evolução na Trilha B (`EVO-SEG-001`):** A capacidade de convidar novos usuários por e-mail mediante link assinado com token criptográfico de uso único e prazo de expiração fica classificada na Trilha B, sem expor qualquer endpoint de autocadastro estático.
- **Rastreabilidade:** `LEG-RMA-002` é reconciliado como **DECIDIDO / DEFERIDO**, encerrando a pendência sem introduzir vulnerabilidade.

---

## 3. Decisão de Integridade (`F10-COB-03` e `F10-COB-04`) — Exclusão Física vs Auditoria

### 3.1 Pista Histórica
No legado, rotas administrativas executavam comandos SQL brutos de `DELETE FROM bd WHERE id = ...` e `DELETE FROM usuario WHERE id = ...` sem integridade referencial ou tratamento de dependências órfãs.

### 3.2 Análise Contábil, Fiscal e Rastreabilidade
Boletins de RMA movimentam garantias fiscais (notas fiscais de compra e venda), peças de estoque, transações de crédito com parceiros e geram logs na tabela de modificações (`modificacao_de_rmas`). A destruição física de um registro de RMA viola os princípios fundamentais de conformidade fiscal e auditoria contábil. Similarmente, a exclusão de um usuário quebra a autoria dos boletins e das ações operacionais do passado.

### 3.3 Resolução Adotada (Preservação Estrita e Auditoria)
- **Para RMAs:** O ciclo de vida do V3 conta com a ação oficial de **Arquivamento** (`Status::Arquivado`, rota `POST /rmas/{id}/arquivar`), já implementada e coberta por testes. O arquivamento retira o registro dos fluxos operacionais ativos e preserva 100% da integridade relacional e histórica. Operações destrutivas de hard-delete são formalmente rejeitadas.
- **Para Usuários:** A gestão de acesso utiliza a inativação de conta mediante o papel **Bloqueado** (`Papel::Bloqueado`), impedindo qualquer autenticação ou operação do operador no sistema sem remover a chave primária de autoria dos registros legados e contemporâneos. Hard-delete é rejeitado.
- **Rastreabilidade:** `VIS-V1-011` e `VIS-V1-012` são encerrados com decisão de integridade imutável registrada.

---

## 4. Resolução do Achado CP14 (`ETA-05`) — Máquina de Estados e `ClasseDeAlerta::Urgente`

### 4.1 Problema Identificado no CP14
O método `Rma::classeDeAlerta()` mapeava todas as quatro condições de alerta (`SemGarantia`, `Prioridade::Alta`, `origemEhTerceiroForaDoPrazo` e `marcarestoque=false + Cliente/Licitação`) para `ClasseDeAlerta::Inconformidade`. No legado (`entrada.php:41-49`), linhas de prioridade alta e prazo de 30 dias de clientes utilizavam `TrUrgente` (fundo `#382830`), enquanto inconformidades utilizavam `TrInconformidade` (fundo `#303033`).

### 4.2 Implementação Realizada
No arquivo `app/Rma/Dominio/Rma.php`, o `match(true)` foi refinado com segurança:
```php
    public function classeDeAlerta(): ClasseDeAlerta
    {
        return match (true) {
            $this->solucao === Solucao::SemGarantia => ClasseDeAlerta::SemGarantia,
            $this->prioridade === Prioridade::Alta => ClasseDeAlerta::Urgente,
            $this->origemEhTerceiroForaDoPrazo() => ClasseDeAlerta::Urgente,
            $this->marcarestoque === false
                && in_array($this->origem, [Origem::Cliente->value, Origem::Licitacao->value], true)
                => ClasseDeAlerta::Inconformidade,
            default => ClasseDeAlerta::Neutro,
        };
    }
```
A função helper `classe_css_de_alerta` (em `app/Support/view_do_tema.php`) já continha os mapeamentos exatos:
- `ClasseDeAlerta::Urgente` -> `'TrUrgente'`
- `ClasseDeAlerta::Inconformidade` -> `'TrInconformidade'`
- `ClasseDeAlerta::SemGarantia` -> `'TrInconformidade'` (no V1) e `'TrSemGarantia1/2'` (no V2)
- `ClasseDeAlerta::Neutro` -> `'TrZebrada1/2'`

### 4.3 Validação e Regressão Zero
- Suíte unitária `Tests\Unit\Rma\ClasseDeAlertaTest`: 8/8 testes aprovados cobrindo cada ramo de prioridade, urgência e sem-garantia.
- Suíte geral de backend: **388 testes e 941 asserções aprovados sem falhas**.

---

## 5. Conclusão e Estado

Com a emissão deste parecer:
- As decisões `DECISAO C-01`, `F10-COB-03` e `F10-COB-04` estão formalmente registradas e homologadas.
- O achado `CP14` está integralmente corrigido e coberto por testes automatizados.
- A Frente 2 da Fase 10 está **CONCLUÍDA**.
