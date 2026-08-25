Ao concluir uma investigação, encerre formalmente o respectivo arquivo .md localizado em docs/investigacoes-pendente/.

Remova desse arquivo o conteúdo operacional que o tratava como uma investigação ainda pendente e renomeie-o acrescentando o sufixo:

-concluido-AAAA-MM-DD.md

Em seguida, mova o arquivo encerrado para:

docs/investigacoes-pendente/concluido/

No topo desse arquivo, registre obrigatoriamente uma nota de encerramento contendo:

a data da conclusão;
um resumo objetivo da investigação;
a conclusão alcançada;
eventuais pendências ou desdobramentos;
referência explícita ao parecer completo correspondente.

O arquivo armazenado em docs/investigacoes-pendente/concluido/ funciona como registro resumido e rastreável da investigação concluída. Ele não substitui o parecer técnico completo.

Toda investigação concluída deve obrigatoriamente gerar também um parecer próprio e completo, em arquivo .md, armazenado em:

docs/pareceres/

O parecer deve ser datado e conter, conforme aplicável:

contexto e objetivo da investigação;
análise realizada;
evidências encontradas;
conclusões;
decisões adotadas;
divergências identificadas;
riscos;
pendências;
recomendações;
desdobramentos necessários.

Deve existir rastreabilidade bidirecional obrigatória entre os dois documentos:

o arquivo resumido da investigação concluída, em docs/investigacoes-pendente/concluido/, deve identificar e referenciar explicitamente o parecer completo em docs/pareceres/;
o parecer completo, em docs/pareceres/, deve identificar e referenciar explicitamente a investigação que lhe deu origem e o respectivo arquivo concluído.

Portanto, a organização final deve seguir obrigatoriamente esta regra:

docs/investigacoes-pendente/
    investigacao-ainda-pendente.md


docs/investigacoes-pendente/concluido/
    investigacao-concluida-concluido-AAAA-MM-DD.md
        -> resumo do resultado
        -> referência para o parecer completo


docs/pareceres/
    AAAA-MM-DD-parecer-da-investigacao.md
        -> parecer técnico completo
        -> referência para a investigação concluída

Não deixe investigações já concluídas no diretório de pendentes e não considere uma investigação formalmente encerrada enquanto o resumo, o parecer completo e a rastreabilidade bidirecional entre ambos não estiverem registrados.

---

Nota deste projeto (`08.24.1-gerenciador-de-rma`, 2026-08-25): esta convenção é a
mesma já usada em `machadogolang/online-conahom-laravel`
(`docs/investigacoes-pendente/README.md`), replicada aqui a pedido do usuário — mesma
arquitetura de harness, mesmo processo. Um investigação com várias frentes (ex.:
`INV-RMA-BUG-LAYOUT`) pode gerar um parecer parcial concluído (uma sub-frente
encerrada, movida para `docs/pareceres/`) mesmo enquanto o arquivo-mãe continua em
`docs/investigacoes-pendente/` porque outras frentes seguem abertas — só mova o
arquivo-mãe para `concluido/` quando TODAS as frentes que ele abriu estiverem
fechadas ou explicitamente transferidas para outro documento de rastreio (ex.: o
checklist operacional).
