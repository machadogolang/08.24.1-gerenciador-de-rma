<?php

namespace Tests\Unit\Rma;

use App\Rma\Dominio\CriterioDeBusca;
use Tests\TestCase;

class CriterioDeBuscaTest extends TestCase
{
    public function test_por_texto(): void
    {
        $criterio = CriterioDeBusca::porTexto('disco');

        $this->assertSame('texto', $criterio->tipo());
        $this->assertSame('disco', $criterio->valor());
    }

    public function test_por_nota_fiscal(): void
    {
        $criterio = CriterioDeBusca::porNotaFiscal('12345');

        $this->assertSame('nota_fiscal', $criterio->tipo());
        $this->assertSame('12345', $criterio->valor());
    }

    public function test_por_serial(): void
    {
        $criterio = CriterioDeBusca::porSerial('SN0001');

        $this->assertSame('serial', $criterio->tipo());
        $this->assertSame('SN0001', $criterio->valor());
    }
}
