<?php

namespace App\Compartilhado\Tenant;

use App\Models\Company;

/**
 * EVO-SAAS-001 (S4) — fonte central de tenant corrente por request. Registrado como
 * singleton no container; populado por `ResolverTenantAtivo` depois da autenticação.
 * Nenhuma entidade de domínio lê session/request diretamente para descobrir tenant.
 */
final class ContextoDeTenant
{
    private ?Company $empresa = null;

    public function definir(Company $empresa): void
    {
        $this->empresa = $empresa;
    }

    public function limpar(): void
    {
        $this->empresa = null;
    }

    public function empresa(): ?Company
    {
        return $this->empresa;
    }

    public function empresaId(): ?int
    {
        return $this->empresa?->id;
    }

    public function temEmpresa(): bool
    {
        return $this->empresa !== null;
    }
}
