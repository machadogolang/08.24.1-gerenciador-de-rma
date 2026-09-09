# Incidente operacional - `bwrap: loopback: Failed RTM_NEWADDR` no sandbox de execução

## Identificação

- Data: 2026-09-09
- Ferramenta/agente: Codex (camada `exec_command` com sandbox gerenciado do harness)
- Projeto: CellSystem RMA V3 (`08.24.1-gerenciador-de-rma`)
- Categoria: C/D (falha do sandbox/bwrap na criação de namespace de rede), **não** é
  erro do repositório

## Sintoma

Comandos locais simples que deveriam rodar sem rede falham antes de executar:

```
bwrap: loopback: Failed RTM_NEWADDR: Operation not permitted
```

A mesma mensagem aparece para `pwd`, `printf ok`, `find`, `ls`, `tail -120`,
`git show`, `rg` sem prefixo aprovado e para execuções com shell login e sem login.
O comando não chega a rodar.

## Classificação

- A. erro do comando: não
- B. permissão de filesystem: não
- C. erro do sandbox/bwrap: **sim (causa da mensagem)**
- D. namespace de rede: **sim (componente que falha: loopback do network namespace)**
- E. diretório atual: não
- F. ferramenta local indisponível: não
- G. problema do repositório: não

## Causa raiz provável

O mecanismo de sandbox do harness envolve os comandos com bubblewrap e tenta criar um
network namespace com interface loopback antes de executar o comando, mesmo para
leituras locais que não precisam de rede. Neste ambiente, a criação do loopback no
namespace é negada (`RTM_NEWADDR: Operation not permitted`), então o comando morre no
wrapper. A causa exata do lado do kernel/container (permissão de user namespace, netns
ou configuração do wrapper) não pode ser inspecionada sem acesso ao host/fora do
sandbox e fica registrada como limitação do ambiente.

## Tentativas que NÃO resolveram

- repetir o mesmo comando com pequenas variações (`find`, `ls`, `tail`);
- trocar shell login/não-login;
- reduzir o comando ao mínimo (`pwd`, `printf ok`).

Todas falharam com a mesma mensagem - não repetir esse caminho.

## Solução comprovada

Duas modalidades de execução fora do sandbox quebrado funcionam:

1. **Prefixos já aprovados** (ex.: `cat`, `git status --short --branch`,
   `git log`, `rg` com padrão específico) - executam sem o wrapper.
2. **Execução escalada pontual** (`sandbox_permissions: require_escalated`) com
   prefixo estreito de leitura - ex.: `find docs`, `git ls-files`, `rg -n ... docs`.
   Usar somente para comandos necessários e de leitura; para escrita, manter a
   disciplina normal de `apply_patch` e escopo do repositório.

## Prevenção / regra para próximas sessões

- Se um comando simples falhar com `bwrap: loopback` ou `RTM_NEWADDR`, testar no
  máximo 2 variações equivalentes e depois **mudar de mecanismo**:
  1. usar prefixo aprovado quando existir;
  2. senão, escalar a leitura pontual com `require_escalated`;
  3. registrar o incidente aqui quando a solução estiver comprovada.
- Não transformar o troubleshooting do sandbox em frente própria nem concluir que o
  repositório está inacessível por causa do wrapper.
- O objetivo-pai continua sendo o levantamento de estado, o plano de ataque e a
  execução por fases com commits pequenos.

## Como reconhecer rapidamente

Saída `bwrap: loopback: Failed RTM_NEWADDR: Operation not permitted` antes de qualquer
saída útil, mesmo em `pwd`/`printf`, indica wrapper de sandbox, não comando nem repo.

## Estado

Solução alternativa comprovada e em uso. Causa raiz fina do ambiente permanece fora do
escopo deste repositório (registro de limitação). Nenhuma configuração global do host
foi alterada.
