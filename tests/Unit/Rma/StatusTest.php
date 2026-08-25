<?php

namespace Tests\Unit\Rma;

use App\Rma\Dominio\Status;
use PHPUnit\Framework\TestCase;

class StatusTest extends TestCase
{
    public function test_apenas_entrada_pode_receber(): void
    {
        $this->assertTrue(Status::Entrada->podeReceber());
        $this->assertFalse(Status::Recebido->podeReceber());
        $this->assertFalse(Status::Encaminhado->podeReceber());
        $this->assertFalse(Status::Concluido->podeReceber());
        $this->assertFalse(Status::Arquivado->podeReceber());
    }

    public function test_apenas_recebido_pode_encaminhar(): void
    {
        $this->assertFalse(Status::Entrada->podeEncaminhar());
        $this->assertTrue(Status::Recebido->podeEncaminhar());
        $this->assertFalse(Status::Encaminhado->podeEncaminhar());
        $this->assertFalse(Status::Concluido->podeEncaminhar());
        $this->assertFalse(Status::Arquivado->podeEncaminhar());
    }

    public function test_apenas_encaminhado_pode_concluir(): void
    {
        $this->assertFalse(Status::Entrada->podeConcluir());
        $this->assertFalse(Status::Recebido->podeConcluir());
        $this->assertTrue(Status::Encaminhado->podeConcluir());
        $this->assertFalse(Status::Concluido->podeConcluir());
        $this->assertFalse(Status::Arquivado->podeConcluir());
    }

    public function test_entrada_recebido_e_encaminhado_podem_arquivar(): void
    {
        $this->assertTrue(Status::Entrada->podeArquivar());
        $this->assertTrue(Status::Recebido->podeArquivar());
        $this->assertTrue(Status::Encaminhado->podeArquivar());
        $this->assertFalse(Status::Concluido->podeArquivar());
        $this->assertFalse(Status::Arquivado->podeArquivar());
    }

    public function test_apenas_recebido_e_encaminhado_podem_reverter_para_entrada(): void
    {
        $this->assertFalse(Status::Entrada->podeReverterParaEntrada());
        $this->assertTrue(Status::Recebido->podeReverterParaEntrada());
        $this->assertTrue(Status::Encaminhado->podeReverterParaEntrada());
        $this->assertFalse(Status::Concluido->podeReverterParaEntrada());
        $this->assertFalse(Status::Arquivado->podeReverterParaEntrada());
    }
}
