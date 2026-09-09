<?php

namespace App\Compartilhado\Tenant;

use App\Identidade\Dominio\Papel;
use App\Models\Company;

/**
 * EVO-SAAS-001 (S4) — fonte central de tenant corrente por request. Registrado como
 * singleton no container; populado por `ResolverTenantAtivo` depois da autenticação.
 * Nenhuma entidade de domínio lê session/request diretamente para descobrir tenant.
 */
final class ContextoDeTenant
{
    private ?Company $empresa = null;

    private ?Papel $papel = null;

    /**
     * Define a empresa ativa e, quando disponível, o Papel do vínculo ativo
     * (`company_user`) daquele usuário na empresa. `papel` é opcional para manter
     * chamadas de teste/contexto manual simples; Policies usam `User::papelAtivo()`.
     */
    public function definir(Company $empresa, ?Papel $papel = null): void
    {
        $this->empresa = $empresa;
        $this->papel = $papel;
    }

    public function limpar(): void
    {
        $this->empresa = null;
        $this->papel = null;
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

    public function papelAtivo(): ?Papel
    {
        return $this->papel;
    }
}
