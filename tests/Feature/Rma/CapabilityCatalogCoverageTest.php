<?php

namespace Tests\Feature\Rma;

use Tests\TestCase;
use Tests\Support\CapabilityCatalog;

class CapabilityCatalogCoverageTest extends TestCase
{
    public function test_catalogo_possui_exatamente_65_capacidades_unicas(): void
    {
        $todas = CapabilityCatalog::all();

        $this->assertCount(65, $todas, 'O catálogo deve possuir exatamente 65 capacidades canônicas.');

        $ids = array_column($todas, 'id');
        $unicos = array_unique($ids);

        $this->assertCount(65, $unicos, 'Não podem existir IDs de capacidade duplicados.');
    }

    public function test_catalogo_nao_tem_drift_com_a_openspec_capabilities_md(): void
    {
        $caminhoOpenSpec = base_path('openspec/changes/unificacao-funcional-temas-v1-v2/capabilities.md');
        $this->assertFileExists($caminhoOpenSpec, 'O arquivo capabilities.md da OpenSpec deve existir.');

        $conteudo = file_get_contents($caminhoOpenSpec);

        // Encontra todos os padrões "CAP-[A-Z]+-[0-9]{3}" no arquivo
        preg_match_all('/(CAP-[A-Z]+-[0-9]{3})/', $conteudo, $matches);
        $idsNaOpenSpec = array_unique($matches[1]);

        $idsNoCatalogo = array_keys(CapabilityCatalog::all());

        // Todos os IDs da OpenSpec devem estar no catálogo
        foreach ($idsNaOpenSpec as $id) {
            $this->assertArrayHasKey(
                $id,
                CapabilityCatalog::all(),
                "A capacidade {$id} presente na OpenSpec deve constar no CapabilityCatalog."
            );
        }

        // Todos os IDs do catálogo devem estar na OpenSpec
        foreach ($idsNoCatalogo as $id) {
            $this->assertContains(
                $id,
                $idsNaOpenSpec,
                "A capacidade {$id} do CapabilityCatalog deve estar descrita na OpenSpec."
            );
        }

        $this->assertCount(65, $idsNaOpenSpec, 'A OpenSpec deve conter exatamente as 65 capacidades canônicas.');
    }

    public function test_todas_capacidades_ativas_possuem_rotas_v1_e_v2_definidas(): void
    {
        $ativas = CapabilityCatalog::ativas();

        foreach ($ativas as $cap) {
            $this->assertNotNull(
                $cap['rota_v1'],
                "Capacidade ativa {$cap['id']} ({$cap['nome']}) deve possuir rota V1 definida."
            );
            $this->assertNotNull(
                $cap['rota_v2'],
                "Capacidade ativa {$cap['id']} ({$cap['nome']}) deve possuir rota V2 definida."
            );
        }
    }

    public function test_todas_capacidades_com_navegacao_possuem_tipo_apropriado(): void
    {
        $descobríveis = CapabilityCatalog::descobríveis();

        $tiposPermitidos = [
            'PAGE',
            'LIST',
            'DETAIL',
            'CREATE',
            'UPDATE',
            'DELETE/DESATIVAR',
            'ACTION',
            'WORKFLOW',
            'REPORT',
            'FILTER',
            'ALERT',
            'AUDIT',
            'CONFIG',
            'BACKGROUND/DOMAIN',
        ];

        foreach ($descobríveis as $cap) {
            $this->assertContains(
                $cap['tipo'],
                $tiposPermitidos,
                "Capacidade {$cap['id']} possui tipo inválido: {$cap['tipo']}."
            );
        }
    }

    public function test_recalculo_e_metricas_das_65_capacidades(): void
    {
        $todas = CapabilityCatalog::all();
        $ativas = CapabilityCatalog::ativas();
        $descobríveis = CapabilityCatalog::descobríveis();

        // 65 total, 58 ativas (7 códigos mortos catalogados)
        $this->assertCount(65, $todas);
        $this->assertCount(58, $ativas);

        // Capacidades com UI navegável
        $this->assertGreaterThanOrEqual(40, count($descobríveis));
    }
}
