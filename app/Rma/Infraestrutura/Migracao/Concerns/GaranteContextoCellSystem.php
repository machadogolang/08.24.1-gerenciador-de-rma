<?php

namespace App\Rma\Infraestrutura\Migracao\Concerns;

use App\Compartilhado\Tenant\ContextoDeTenant;
use App\Models\Company;

/**
 * EVO-SAAS-001 (S11) — importadores que tocam tabela tenant-scoped garantem o tenant
 * histórico CellSystem mesmo quando executados individualmente (teste/CLI), sem
 * depender do comando orquestrador nem de request.
 */
trait GaranteContextoCellSystem
{
    private function garantirContextoCellSystem(): void
    {
        $contexto = app(ContextoDeTenant::class);
        if ($contexto->temEmpresa()) {
            return;
        }

        $contexto->definir(Company::query()->where('nome', 'CellSystem')->firstOrFail());
    }
}
