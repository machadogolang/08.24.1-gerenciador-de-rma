# Datação — CellSystem RMA V2 (15.9.7)

Data: 2026-08-24. **Ordem histórica confirmada pelo autor original do sistema** (ver
`docs/legado/matriz-comparacao-apps-rma.md` para a nomenclatura oficial): TEMA V1
(14.6.1) foi construído primeiro; TEMA V2 (15.8.1) depois, como segunda geração,
preservando TEMA V1 em vez de substituí-lo. **RMA V2 FINAL = 15.9.7** é o estado final
conhecido dessa segunda geração do produto, com os dois temas coexistindo. Este arquivo
guarda só pistas de datação absoluta (quando cada coisa foi escrita), não mais a
pergunta de ordem relativa (já resolvida).

## Pistas de datação por fonte

| Fonte | Pista | Leitura |
|---|---|---|
| `14.10.2` (repositório GitHub, protótipo sem banco) | jQuery 1.11.0 (lançado 2014); placeholder de formulário `00/00/2014`; único commit git `2016-01-07` (mensagem de backup automático) | Escrito ~2014, arquivado/empurrado ao GitHub em 2016 — artefato à parte, nunca funcionou como sistema real (não confundir com TEMA V1/V2 do RMA V2) |
| `15.10.1` (repositório GitHub, só assets `pattern/*.css/js`) | Commits de `2016-01-06` | Snapshot de 2016 dos arquivos `pattern/`; comparado byte a byte com os mesmos arquivos do backup de 2019: só `14.6.1.js` é idêntico, os outros 5 divergem — os assets de TEMA V1/V2 continuaram sendo editados entre 2016 e 2019 |
| Backup completo (RMA V2 FINAL) | Timestamp interno do `.tar.gz`: 16/12/2019 14:16; dump `dump-cellsyst_rma-201912161213.sql` | Data do estado final capturado do RMA V2 |
| Dumps intermediários (`app/1maiode2019.sql`, `app/2maiode2019.sql`) | Schema idêntico ao de dezembro/2019 | Schema do banco estável entre maio e dezembro de 2019 |

## Pendente (não bloqueante)

Data exata de criação de cada tema (TEMA V1 vs. TEMA V2) dentro da linha do tempo
2014-2019 não está documentalmente cravada (sem changelog/comentário de versão
comparável no código) — não é necessário resolver isso para a reconstrução da V3, já
que o alvo de fidelidade é o estado final capturado no backup (RMA V2 FINAL, ambos os
temas presentes).
