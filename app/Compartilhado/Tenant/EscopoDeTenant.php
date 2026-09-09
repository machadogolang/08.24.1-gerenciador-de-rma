<?php

namespace App\Compartilhado\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * EVO-SAAS-001 (S5) — Global Scope de tenant. Só filtra quando o
 * `ContextoDeTenant` tem empresa ativa (requests web autenticados); em console/testes
 * sem contexto a query continua sem filtro para não esconder escrita/seed. A camada
 * web nunca chega a Controller sem contexto (middleware falha 403).
 */
final class EscopoDeTenant implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $contexto = app(ContextoDeTenant::class);
        $empresaId = $contexto->empresaId();

        if ($empresaId !== null) {
            $builder->where($model->getTable().'.tenant_id', $empresaId);
        }
    }
}
