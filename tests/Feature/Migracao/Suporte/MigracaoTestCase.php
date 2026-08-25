<?php

namespace Tests\Feature\Migracao\Suporte;

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

        $this->criarEsquemaLegado();
    }

    protected function novoRelatorio(): RelatorioDeReconciliacao
    {
        return new RelatorioDeReconciliacao;
    }
}
