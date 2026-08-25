<?php

namespace Tests\Feature\Migracao;

use App\Models\AssistenciaTecnica;
use App\Models\Cliente;
use App\Models\Fabricante;
use App\Models\Fornecedor;
use App\Models\Rma;
use App\Rma\Dominio\Prioridade;
use App\Rma\Dominio\Solucao;
use App\Rma\Dominio\Status;
use App\Rma\Infraestrutura\Migracao\Importadores\ImportarRmas;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Migracao\Suporte\MigracaoTestCase;

class ImportarRmasTest extends MigracaoTestCase
{
    private function inserirLinhaBase(array $sobrescritas = []): void
    {
        DB::connection('rma_legacy')->table('bd')->insert(array_merge([
            'numero' => 1001,
            'descricao' => 'HD externo',
            'fabricante' => 'Seagate',
            'modelo' => 'ST1000',
            'os' => '12345',
            'status' => 'recebido',
            'origem' => 'Cliente',
            'sn' => 'SN123',
            'prioridade' => 'alta',
            'defeito' => 'não liga',
            'nfcompra' => '999',
            'entrada' => '2019-05-01 10:00:00',
            'recebido' => '02/05/2019',
            'encaminhado' => null,
            'concluido' => null,
            'nfcompra_emissao' => '01/05/2019',
            'nfvenda' => null,
            'nfvenda_emissao' => null,
            'nfcompra_chave' => null,
            'nfvenda_chave' => null,
            'observacao' => 'obs livre',
            'solucao' => 'REPARO',
            'marcarestoque' => 1,
            'creditodisponivel' => 0,
            'empresa' => 'R A',
            'cliente' => 'Cliente Teste',
            'destinatario' => 'Fornecedor Teste',
            'protocolo' => 'PROT1',
            'arquivado' => null,
            'fornecedor' => 'Fornecedor Teste',
            'valor' => 150.50,
            'snretorno' => null,
            'lancadoretorno' => 'pendente',
            'dtaalt' => '2019-05-02 11:00:00',
            'nfdevolucaodevenda' => null,
            'nfentrada_cli' => null,
            'nfretorno_cli' => null,
            'nfremessa' => null,
            'nfremessa_emissao' => null,
            'nfremessa_chave' => null,
            'nfretorno' => null,
            'nfretorno_emissao' => null,
            'nfretorno_chave' => null,
            'pn' => null,
            'snid' => null,
            'rastreio_ida' => null,
            'rastreio_retorno' => null,
            'cliente_email' => 'cliente@teste.com',
            'destinatario_email' => null,
            'destinatario_fone' => null,
            'descricao_final' => null,
            'usuario' => 'ana@example.com',
            'prazo' => null,
            'ano' => 2019,
            'dtains' => '2019-05-01 10:00:00',
            'retornou' => null,
        ], $sobrescritas));
    }

    public function test_caso_feliz_traduz_status_solucao_prioridade_e_resolve_parceiros(): void
    {
        Fornecedor::query()->create(['nome' => 'Fornecedor Teste']);
        $this->inserirLinhaBase();

        (new ImportarRmas)->executar($this->novoRelatorio());

        $rma = Rma::query()->where('numero_legado', 1001)->first();

        $this->assertNotNull($rma);
        $this->assertSame(Status::Recebido, $rma->status);
        $this->assertSame(Solucao::Reparo, $rma->solucao);
        $this->assertSame(Prioridade::Alta, $rma->prioridade);
        $this->assertSame('Registros Ativos', $rma->empresa);
        $this->assertNotNull($rma->fabricante_id);
        $this->assertNotNull($rma->cliente_id);
        $this->assertSame('Fornecedor', class_basename($rma->destinatario_type));
        $this->assertSame('2019-05-02 00:00:00', $rma->recebido_em->toDateTimeString());
        $this->assertSame(150.50, (float) $rma->valor);
    }

    public function test_hgst_e_normalizado_para_hitachi_rn13(): void
    {
        $this->inserirLinhaBase(['fabricante' => 'HGST']);

        (new ImportarRmas)->executar($this->novoRelatorio());

        $rma = Rma::query()->where('numero_legado', 1001)->first();

        $this->assertSame('Hitachi', $rma->fabricante->nome);
    }

    public function test_status_fora_do_dominio_vira_anomalia_e_fica_null(): void
    {
        $this->inserirLinhaBase(['status' => 'lixo']);

        $relatorio = $this->novoRelatorio();
        (new ImportarRmas)->executar($relatorio);

        $rma = Rma::query()->where('numero_legado', 1001)->first();

        $this->assertNull($rma->status);
        $this->assertNotEmpty($relatorio->anomalias());
    }

    public function test_status_retornou_vira_anomalia_sem_inventar_case_novo(): void
    {
        $this->inserirLinhaBase(['status' => 'retornou']);

        $relatorio = $this->novoRelatorio();
        (new ImportarRmas)->executar($relatorio);

        $rma = Rma::query()->where('numero_legado', 1001)->first();

        $this->assertNull($rma->status);
        $motivos = array_column($relatorio->anomalias(), 'motivo');
        $this->assertTrue(collect($motivos)->contains(fn ($m) => str_contains($m, "status='retornou'")));
    }

    public function test_solucao_nao_reconhecida_preserva_valor_bruto(): void
    {
        $this->inserirLinhaBase(['solucao' => 'ALGO ESQUISITO']);

        $relatorio = $this->novoRelatorio();
        (new ImportarRmas)->executar($relatorio);

        $rma = Rma::query()->where('numero_legado', 1001)->first();

        $this->assertNull($rma->solucao);
        $this->assertSame('ALGO ESQUISITO', $rma->solucao_legado_bruto);
        $this->assertNotEmpty($relatorio->anomalias());
    }

    public function test_prioridade_urgente_e_conversao_assistida_para_alta(): void
    {
        $this->inserirLinhaBase(['prioridade' => 'urgente']);

        $relatorio = $this->novoRelatorio();
        (new ImportarRmas)->executar($relatorio);

        $rma = Rma::query()->where('numero_legado', 1001)->first();

        $this->assertSame(Prioridade::Alta, $rma->prioridade);
        $this->assertNotEmpty($relatorio->conversoesAssistidas());
    }

    public function test_data_nao_parseavel_grava_null_e_registra_anomalia_com_valor_bruto(): void
    {
        $this->inserirLinhaBase(['recebido' => '31 de maio']);

        $relatorio = $this->novoRelatorio();
        (new ImportarRmas)->executar($relatorio);

        $rma = Rma::query()->where('numero_legado', 1001)->first();

        $this->assertNull($rma->recebido_em);
        $motivos = array_column($relatorio->anomalias(), 'motivo');
        $this->assertTrue(collect($motivos)->contains(fn ($m) => str_contains($m, '31 de maio')));
    }

    public function test_data_no_formato_ymd_tambem_e_aceita(): void
    {
        $this->inserirLinhaBase(['recebido' => '2019-05-02']);

        (new ImportarRmas)->executar($this->novoRelatorio());

        $rma = Rma::query()->where('numero_legado', 1001)->first();

        $this->assertSame('2019-05-02 00:00:00', $rma->recebido_em->toDateTimeString());
    }

    public function test_destinatario_nao_resolvido_preserva_nome_bruto_sem_auto_criar(): void
    {
        $this->inserirLinhaBase(['destinatario' => 'Nome Que Não Existe Em Lugar Nenhum']);

        $relatorio = $this->novoRelatorio();
        (new ImportarRmas)->executar($relatorio);

        $rma = Rma::query()->where('numero_legado', 1001)->first();

        $this->assertNull($rma->destinatario_type);
        $this->assertNull($rma->destinatario_id);
        $this->assertSame('Nome Que Não Existe Em Lugar Nenhum', $rma->destinatario_nome_legado);
        $this->assertSame(0, AssistenciaTecnica::query()->count());
    }

    public function test_destinatario_resolve_via_cascata_assistencia_tecnica_primeiro(): void
    {
        AssistenciaTecnica::query()->create(['nome' => 'Parceiro Compartilhado']);
        Fornecedor::query()->create(['nome' => 'Parceiro Compartilhado']);
        $this->inserirLinhaBase(['destinatario' => 'Parceiro Compartilhado', 'fornecedor' => null]);

        (new ImportarRmas)->executar($this->novoRelatorio());

        $rma = Rma::query()->where('numero_legado', 1001)->first();

        $this->assertSame('AssistenciaTecnica', class_basename($rma->destinatario_type));
    }

    public function test_operador_soft_match_por_email_preenche_operador_id(): void
    {
        DB::connection('rma_legacy')->table('usuario')->insert([
            'nome' => 'Ana Operadora',
            'email' => 'ana@example.com',
            'anotacao' => '',
            'permissao' => 2,
            'app' => '15.8.1',
            'data_de_cadastro' => '2019-05-01',
        ]);
        (new \App\Rma\Infraestrutura\Migracao\Importadores\ImportarUsuarios)->executar($this->novoRelatorio());

        $this->inserirLinhaBase();
        (new ImportarRmas)->executar($this->novoRelatorio());

        $rma = Rma::query()->where('numero_legado', 1001)->first();

        $this->assertNotNull($rma->operador_id);
        $this->assertSame('ana@example.com', $rma->operador_email_legado);
    }

    public function test_retornou_preenchido_vira_anomalia_e_nao_e_migrado(): void
    {
        $this->inserirLinhaBase(['retornou' => '2019-06-01']);

        $relatorio = $this->novoRelatorio();
        (new ImportarRmas)->executar($relatorio);

        $motivos = array_column($relatorio->anomalias(), 'motivo');
        $this->assertTrue(collect($motivos)->contains(fn ($m) => str_contains($m, 'retornou=')));
    }

    public function test_idempotencia_nao_duplica_ao_rodar_duas_vezes(): void
    {
        $this->inserirLinhaBase();

        (new ImportarRmas)->executar($this->novoRelatorio());
        (new ImportarRmas)->executar($this->novoRelatorio());

        $this->assertSame(1, Rma::query()->where('numero_legado', 1001)->count());
    }

    public function test_dry_run_nao_grava_nada(): void
    {
        $this->inserirLinhaBase();

        (new ImportarRmas)->executar($this->novoRelatorio(), dryRun: true);

        $this->assertSame(0, Rma::query()->count());
        $this->assertSame(0, Cliente::query()->count());
        $this->assertSame(0, Fabricante::query()->count());
    }

    /**
     * ARQ-002 (`INV-RMA-10`) — antes desta correção, `if ($dryRun) { continue; }`
     * pulava a tradução inteira, então dry-run nunca detectava anomalia nenhuma. Agora
     * a tradução roda por completo (e é desfeita depois), então a anomalia aparece.
     */
    public function test_dry_run_detecta_anomalias_sem_gravar(): void
    {
        $this->inserirLinhaBase(['status' => 'lixo']);

        $relatorio = $this->novoRelatorio();
        (new ImportarRmas)->executar($relatorio, dryRun: true);

        $this->assertNotEmpty($relatorio->anomalias());
        $this->assertSame(0, Rma::query()->count());
    }

    /**
     * ARQ-002 — antes, `$processados` (destino) sempre era 0 em dry-run, escondendo o
     * que de fato seria migrado. Agora reflete quantas linhas passariam pela tradução.
     */
    public function test_dry_run_conta_quantas_linhas_seriam_processadas(): void
    {
        $this->inserirLinhaBase();

        $relatorio = $this->novoRelatorio();
        (new ImportarRmas)->executar($relatorio, dryRun: true);

        $this->assertSame(1, $relatorio->contagemOrigem()['bd']);
        $this->assertSame(1, $relatorio->contagemDestino()['rmas']);
        $this->assertSame(0, Rma::query()->count());
    }

    /**
     * ARQ-002 — a checagem de idempotência (`numero_legado` já migrado) roda dentro da
     * mesma transação desfeita, então dry-run continua reportando "seria ignorado" para
     * uma linha já migrada de verdade, sem contá-la de novo como planejada.
     */
    public function test_dry_run_nao_conta_linha_ja_migrada_como_planejada(): void
    {
        $this->inserirLinhaBase();
        (new ImportarRmas)->executar($this->novoRelatorio());

        $relatorio = $this->novoRelatorio();
        (new ImportarRmas)->executar($relatorio, dryRun: true);

        $this->assertSame(1, $relatorio->contagemOrigem()['bd']);
        $this->assertSame(0, $relatorio->contagemDestino()['rmas']);
        $this->assertSame(1, Rma::query()->count());
    }
}
