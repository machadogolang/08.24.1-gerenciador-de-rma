<?php

namespace App\Identidade\Aplicacao;

use App\Identidade\Dominio\ResultadoDeAcesso;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\TentativaDeAcesso;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class AutenticarUsuario
{
    /**
     * Autentica o usuário e devolve o tema preferido, para o controller decidir o
     * redirect. Lança ValidationException com mensagem genérica em qualquer falha
     * (e-mail inexistente, papel bloqueado, senha errada) — nunca revela qual dos três
     * motivos causou a negação (correção de segurança sobre o legado, que tinha
     * enumeração de usuário confirmada).
     */
    public function autenticar(string $email, string $senha, ?string $ip, ?string $userAgent): TemaPreferido
    {
        $usuario = User::query()->where('email', $email)->first();

        if (! $usuario) {
            $this->registrar(null, $email, $ip, $userAgent, ResultadoDeAcesso::Negado);
            $this->falhar();
        }

        // Bloqueio é checado antes da senha (ordem confirmada no legado).
        if (! $usuario->papel->podeAutenticar()) {
            $this->registrar($usuario, $email, $ip, $userAgent, ResultadoDeAcesso::Bloqueado);
            $this->falhar();
        }

        if (! Hash::check($senha, $usuario->password)) {
            $this->registrar($usuario, $email, $ip, $userAgent, ResultadoDeAcesso::Negado);
            $this->falhar();
        }

        Auth::login($usuario);
        $this->registrar($usuario, $email, $ip, $userAgent, ResultadoDeAcesso::Permitido);

        return $usuario->tema_preferido;
    }

    private function registrar(
        ?User $usuario,
        string $email,
        ?string $ip,
        ?string $userAgent,
        ResultadoDeAcesso $resultado
    ): void {
        TentativaDeAcesso::query()->create([
            'user_id' => $usuario?->id,
            'email_informado' => $email,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'resultado' => $resultado,
        ]);
    }

    /**
     * @return never
     */
    private function falhar(): void
    {
        throw ValidationException::withMessages([
            'email' => __('As credenciais informadas não conferem.'),
        ]);
    }
}
