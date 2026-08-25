<?php

namespace App\Identidade\Dominio;

enum Papel
{
    case Bloqueado;
    case Leitura;
    case Operador;
    case Supervisor;
    case SuperAdministrador;

    public function podeAutenticar(): bool
    {
        return $this !== self::Bloqueado;
    }

    public function podeGravar(): bool
    {
        return match ($this) {
            self::Bloqueado, self::Leitura => false,
            default => true,
        };
    }

    public function podeGerenciarUsuarios(): bool
    {
        return match ($this) {
            self::Supervisor, self::SuperAdministrador => true,
            default => false,
        };
    }

    public function ocultoDaListagemDeUsuarios(): bool
    {
        return $this === self::SuperAdministrador;
    }

    /**
     * Equivalente a `permissao==4` do legado (`LEG-RMA-015`) — único nível que reverte
     * um RMA para Entrada fora da janela de "mesmo dia" do encaminhamento.
     */
    public function podeReverterAlemDoMesmoDia(): bool
    {
        return $this === self::SuperAdministrador;
    }

    /**
     * ARQ-003 (`INV-RMA-10`) — Supervisor pode gerenciar usuários, mas nunca
     * SuperAdministrador: nem alterar/resetar senha de quem já é SuperAdministrador,
     * nem atribuir esse papel a ninguém (o que incluiria promover a si próprio). Só
     * SuperAdministrador lida com SuperAdministrador. `$papel` é o papel atual do alvo
     * (gerenciar/resetar senha) ou o papel pretendido na troca (atribuir).
     */
    public function podeOperarSobrePapel(self $papel): bool
    {
        if (! $this->podeGerenciarUsuarios()) {
            return false;
        }

        return $this === self::SuperAdministrador || $papel !== self::SuperAdministrador;
    }
}
