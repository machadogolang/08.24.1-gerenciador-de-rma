<?php

namespace App\Rma\Dominio;

final class CriterioDeBusca
{
    private function __construct(
        private readonly string $tipo,   // 'texto' | 'nota_fiscal' | 'serial'
        private readonly string $valor,
    ) {}

    public static function porTexto(string $valor): self
    {
        return new self('texto', $valor);
    }

    public static function porNotaFiscal(string $valor): self
    {
        return new self('nota_fiscal', $valor);
    }

    public static function porSerial(string $valor): self
    {
        return new self('serial', $valor);
    }

    public function tipo(): string
    {
        return $this->tipo;
    }

    public function valor(): string
    {
        return $this->valor;
    }
}
