<?php

namespace App\Rma\Dominio;

final class CriterioDeBusca
{
    private function __construct(
        private readonly string $tipo,   // 'texto' | 'nota_fiscal' | 'serial'
        private readonly string $valor,
        // CP7 (fase 2 V1) — `menujs-top/localizar.php` tem um 2º select
        // independente (`solucao`, "QUALQUER UMA SOLUCAO" por padrão) que o legado
        // aplica junto do campo de texto, não em vez dele. Sem equivalente antes
        // desta fase; adicionado aditivamente (default null = sem filtro, mesmo
        // comportamento de antes para quem não usa este parâmetro, ex. TEMA V2).
        private readonly ?Solucao $solucao = null,
    ) {}

    public static function porTexto(string $valor, ?Solucao $solucao = null): self
    {
        return new self('texto', $valor, $solucao);
    }

    public static function porNotaFiscal(string $valor, ?Solucao $solucao = null): self
    {
        return new self('nota_fiscal', $valor, $solucao);
    }

    public static function porSerial(string $valor, ?Solucao $solucao = null): self
    {
        return new self('serial', $valor, $solucao);
    }

    public function tipo(): string
    {
        return $this->tipo;
    }

    public function valor(): string
    {
        return $this->valor;
    }

    public function solucao(): ?Solucao
    {
        return $this->solucao;
    }
}
