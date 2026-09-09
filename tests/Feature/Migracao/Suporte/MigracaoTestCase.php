<?php

namespace Tests\Feature\Migracao\Suporte;

use App\Compartilhado\Tenant\ContextoDeTenant;
use App\Models\Company;
use App\Rma\Infraestrutura\Migracao\RelatorioDeReconciliacao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class MigracaoTestCase extends TestCase
{
    use RefreshDatabase;
    use ComBancoLegadoDeTeste;

    protected function setUp(): void
    {
        parent::setUp();

        // EVO-SAAS-001 (S11/S3.6) — testes do migrador rodam com tenant CellSystem
        // explícito (igual ao runtime da migração), permitindo criações tenant-scoped.
        app(ContextoDeTenant::class)->definir(Company::query()->where('nome', 'CellSystem')->firstOrFail());

        $this->criarEsquemaLegado();
    }

    protected function novoRelatorio(): RelatorioDeReconciliacao
    {
        return new RelatorioDeReconciliacao;
    }
}
