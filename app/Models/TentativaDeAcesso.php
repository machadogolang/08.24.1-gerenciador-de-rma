<?php

namespace App\Models;

use App\Identidade\Dominio\ResultadoDeAcesso;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'email_informado', 'ip', 'user_agent', 'resultado'])]
class TentativaDeAcesso extends Model
{
    protected $table = 'tentativas_de_acesso';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'resultado' => ResultadoDeAcesso::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
