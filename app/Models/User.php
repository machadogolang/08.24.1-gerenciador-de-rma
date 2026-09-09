<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Compartilhado\Tenant\ContextoDeTenant;
use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\TemaPreferido;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'papel', 'tema_preferido', 'anotacao'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'papel' => Papel::class,
            'tema_preferido' => TemaPreferido::class,
        ];
    }

    public function empresas(): BelongsToMany
    {
        return $this->belongsToMany(Company::class)
            ->using(CompanyUser::class)
            ->withPivot(['papel', 'ativo'])
            ->withTimestamps();
    }

    /**
     * EVO-SAAS-001 (S9) — papel no contexto da empresa ativa (vínculo `company_user`),
     * com fallback de compatibilidade para `users.papel` quando não há contexto web.
     * O fallback é removido na etapa S9.7/S9.8, quando nenhum consumidor depender dele.
     */
    public function papelAtivo(): Papel
    {
        return app(ContextoDeTenant::class)->papelAtivo() ?? $this->papel;
    }

    /**
     * Vínculo ativo do usuário na empresa corrente do contexto.
     */
    public function vinculoAtivo(): ?CompanyUser
    {
        $empresaId = app(ContextoDeTenant::class)->empresaId();
        if ($empresaId === null) {
            return null;
        }

        return CompanyUser::query()
            ->where('company_id', $empresaId)
            ->where('user_id', $this->id)
            ->where('ativo', true)
            ->first();
    }

    /**
     * Papel do usuário em uma empresa específica (sem depender do contexto do ator);
     * usado para o alvo em autorização/painel administrativo.
     */
    public function papelNaEmpresa(int $empresaId): ?Papel
    {
        $vinculo = CompanyUser::query()
            ->where('company_id', $empresaId)
            ->where('user_id', $this->id)
            ->first();

        return $vinculo?->papel;
    }
}
