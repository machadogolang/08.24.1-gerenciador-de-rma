<?php

namespace App\Compartilhado\Tenant;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * EVO-SAAS-001 (S5) - trait de modelo tenant-scoped:
 *
 * - registra `EscopoDeTenant` como Global Scope;
 * - preenche `tenant_id` automaticamente no `creating` a partir do contexto;
 * - expõe `empresa()`.
 *
 * `tenant_id` NUNCA entra no `$fillable` do model (proteção de mass assignment); o
 * preenchimento por request acontece exclusivamente pelo observer/contexto.
 */
trait PertenceATenant
{
    public static function bootPertenceATenant(): void
    {
        static::addGlobalScope(new EscopoDeTenant());

        static::creating(function (Model $model): void {
            if ($model->tenant_id !== null) {
                return;
            }

            $contexto = app(ContextoDeTenant::class);
            if ($contexto->temEmpresa()) {
                $model->tenant_id = $contexto->empresaId();
            }
        });
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'tenant_id');
    }

    /**
     * EVO-SAAS-001 (S5.5) - route binding tenant-aware: o parent
     * `resolveRouteBinding()` executa `firstOrFail()` sobre a query devolvida aqui;
     * o Global Scope ativo já filtra pela empresa do contexto, então um ID de outra
     * empresa vira 404.
     */
    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        $query = $query->where($field ?? $this->getRouteKeyName(), $value);

        // Route binding roda antes do middleware de contexto no ciclo web; por isso a
        // defesa aqui usa os vínculos ativos do usuário autenticado (membership), não
        // o ContextoDeTenant. URL manual de outra empresa vira 404.
        $usuario = auth()->user();
        if ($usuario === null) {
            return $query;
        }

        $empresaIds = $usuario->empresas()
            ->wherePivot('ativo', true)
            ->pluck('companies.id')
            ->all();

        return $empresaIds === []
            ? $query->whereRaw('1 = 0')
            : $query->whereIn($this->qualifyColumn('tenant_id'), $empresaIds);
    }

}