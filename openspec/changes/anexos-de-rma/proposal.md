# Proposal — Anexos de arquivo no RMA (Trilha B, Fase B)

Fase B das duas evoluções de Trilha B detalhadas nesta rodada (ver
`docs/produto/roadmap-evolucao-admin-arquivos.md`), depois da Fase A (Configuração de
admin). Não é fase da Trilha A. Depende da baseline de paridade da Trilha A validada
(`INV-RMA-05` §15) antes de qualquer linha de código de produto.

## Por quê

Nenhum campo de upload de arquivo/imagem existe no legado (confirmado por
`inventario-funcional-rma-v2.md`, `modelo-dominio-rma-legado.md`,
`regras-negocio-rma-legado.md`, `inventario-banco-rma-v2.md` — os blocos de NF são
texto/chave, nunca upload) nem nas Fases 1-9 do V3. É evolução de produto pura,
registrada como `EVO-ARQ-001` em `docs/produto/backlog-evolutivo.md`, investigação e
proporcionalidade fechadas em `docs/arquitetura/INV-RMA-09-arquivos-e-configuracao-
admin.md` Parte A. A operação de assistência técnica já lida com foto do defeito, laudo
técnico e NF digitalizada fora do sistema (e-mail/WhatsApp) — esta fase dá rastro
dentro do RMA.

## O que entra

- Entidade `AnexoDoRma` **dentro do módulo `app/Rma/` existente** (não um módulo
  central `Arquivos`/`Armazenamento`) — decisão já fechada em `INV-RMA-09` §A.4: não há
  hoje um segundo módulo do RMA V3 com necessidade de upload que justifique um catálogo
  agregado estilo CONAHOM.
- Interface mínima de storage (`guardar`/`baixar`/`existe`/`remover`), um único
  adaptador local por trás (`Illuminate\Filesystem`).
- Upload/listagem/download/remoção de anexo na tela de detalhe do RMA (Fase 3,
  `resources/views/rma/show.blade.php`), via Controller novo, sem tocar
  `VerDetalheDoRma`.
- Validação de tipo/tamanho de upload (ver `design.md` §Validação).

## O que não entra

- Módulo `Arquivos`/`Armazenamento` central, catálogo agregado
  (`FonteDoCatalogoDeArquivos`), multi-provider de storage, `EstadoDaVersaoDoArquivo`,
  `VerificarSituacaoFisicaDoArquivo` — todos rejeitados por proporcionalidade em
  `INV-RMA-09` §A.3 (um único consumidor hoje, volume baixo por RMA).
- Isolamento de storage por tenant (`EVO-SAAS-001`) — direção já registrada
  (`INV-RMA-09` §A.6), implementação adiada até `EVO-SAAS-001` avançar.
- Categorização estruturada de anexo por papel (foto/laudo/NF) — decisão de produto
  adiada; se necessário, um enum simples resolve depois sem redesenho.

## Critério de pronto

Ver `docs/produto/roadmap-evolucao-admin-arquivos.md` §Critério de pronto. Resumo:
Fase 10/QA da Trilha A concluída; `sail test` completo verde antes e depois; nenhuma
alteração em `VerDetalheDoRma.php` (Fase 3) — a seção de anexos é aditiva na view, lida
por um Controller/caso de uso próprio.
