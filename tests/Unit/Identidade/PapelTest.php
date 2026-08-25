<?php

namespace Tests\Unit\Identidade;

use App\Identidade\Dominio\Papel;
use PHPUnit\Framework\TestCase;

class PapelTest extends TestCase
{
    public function test_apenas_bloqueado_nao_pode_autenticar(): void
    {
        $this->assertFalse(Papel::Bloqueado->podeAutenticar());
        $this->assertTrue(Papel::Leitura->podeAutenticar());
        $this->assertTrue(Papel::Operador->podeAutenticar());
        $this->assertTrue(Papel::Supervisor->podeAutenticar());
        $this->assertTrue(Papel::SuperAdministrador->podeAutenticar());
    }

    public function test_bloqueado_e_leitura_nao_podem_gravar(): void
    {
        $this->assertFalse(Papel::Bloqueado->podeGravar());
        $this->assertFalse(Papel::Leitura->podeGravar());
        $this->assertTrue(Papel::Operador->podeGravar());
        $this->assertTrue(Papel::Supervisor->podeGravar());
        $this->assertTrue(Papel::SuperAdministrador->podeGravar());
    }

    public function test_apenas_supervisor_e_superadministrador_podem_gerenciar_usuarios(): void
    {
        $this->assertFalse(Papel::Bloqueado->podeGerenciarUsuarios());
        $this->assertFalse(Papel::Leitura->podeGerenciarUsuarios());
        $this->assertFalse(Papel::Operador->podeGerenciarUsuarios());
        $this->assertTrue(Papel::Supervisor->podeGerenciarUsuarios());
        $this->assertTrue(Papel::SuperAdministrador->podeGerenciarUsuarios());
    }

    public function test_apenas_superadministrador_fica_oculto_da_listagem(): void
    {
        $this->assertFalse(Papel::Bloqueado->ocultoDaListagemDeUsuarios());
        $this->assertFalse(Papel::Leitura->ocultoDaListagemDeUsuarios());
        $this->assertFalse(Papel::Operador->ocultoDaListagemDeUsuarios());
        $this->assertFalse(Papel::Supervisor->ocultoDaListagemDeUsuarios());
        $this->assertTrue(Papel::SuperAdministrador->ocultoDaListagemDeUsuarios());
    }

    public function test_apenas_superadministrador_pode_reverter_alem_do_mesmo_dia(): void
    {
        $this->assertFalse(Papel::Bloqueado->podeReverterAlemDoMesmoDia());
        $this->assertFalse(Papel::Leitura->podeReverterAlemDoMesmoDia());
        $this->assertFalse(Papel::Operador->podeReverterAlemDoMesmoDia());
        $this->assertFalse(Papel::Supervisor->podeReverterAlemDoMesmoDia());
        $this->assertTrue(Papel::SuperAdministrador->podeReverterAlemDoMesmoDia());
    }
}
