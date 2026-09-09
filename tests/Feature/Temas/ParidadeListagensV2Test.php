<?php

namespace Tests\Feature\Temas;

use App\Rma\Dominio\Prioridade;
use App\Rma\Dominio\Rma;
use App\Rma\Dominio\Solucao;
use App\Rma\Dominio\Status;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * PAR-RES-001..003/PAR-LEGACY-RESIDUAL-01 - classes de linha das listagens V2
 * reproduzidas por tela como no PHP fonte 15.8.1, em vez da regra generica de
 * alerta RN-11 que misturava criterios de telas diferentes.
 */
class ParidadeListagensV2Test extends TestCase
{
    private static function criar(array $alteracoes = []): Rma
    {
        $base = new Rma(
            id: 1,
            descricao: 'RMA lista V2',
            fabricanteId: null,
            fornecedorId: null,
            modelo: null,
            sn: null,
            os: null,
            origem: null,
            empresa: null,
            clienteId: null,
            defeito: 'Defeito lista V2',
            observacao: null,
            status: Status::Entrada,
            prioridade: Prioridade::Baixa,
            marcarestoque: true,
            nfcompra: '1',
            nfvenda: '1',
        );

        return $alteracoes === [] ? $base : $base->comAlteracoes($alteracoes);
    }

    #[Test]
    public function entrada_nao_usa_urgente_e_sem_garantia_vira_inconformidade(): void
    {
        $zebra = false;
        $alta = self::criar(['prioridade' => Prioridade::Alta]);
        $semGarantia = self::criar(['solucao' => Solucao::SemGarantia]);

        $this->assertSame('TrInconformidade', classe_css_linha_v2('entrada', $alta, $zebra));
        // Prioridade alta nao alterna a zebra no Legacy Entrada.
        $this->assertFalse($zebra);
        $this->assertSame('TrInconformidade', classe_css_linha_v2('entrada', $semGarantia, $zebra));
        $this->assertTrue($zebra);
        $this->assertSame('TrZebrada1', classe_css_linha_v2('entrada', self::criar(), $zebra));
    }

    #[Test]
    public function recebido_aplica_urgente_e_sem_nf_vira_inconformidade(): void
    {
        $zebra = false;
        $alta = self::criar(['status' => Status::Recebido, 'prioridade' => Prioridade::Alta]);
        $semNota = self::criar(['status' => Status::Recebido, 'nfcompra' => null, 'nfvenda' => null]);
        $semGarantia = self::criar(['status' => Status::Recebido, 'solucao' => Solucao::SemGarantia]);

        $this->assertSame('TrUrgente', classe_css_linha_v2('recebido', $alta, $zebra));
        $this->assertSame('TrInconformidade', classe_css_linha_v2('recebido', $semNota, $zebra));
        $this->assertSame('TrInconformidade', classe_css_linha_v2('recebido', $semGarantia, $zebra));
    }

    #[Test]
    public function encaminhado_aplica_urgente_e_sem_garantia_vira_inconformidade(): void
    {
        $zebra = false;
        $alta = self::criar(['status' => Status::Encaminhado, 'prioridade' => Prioridade::Alta]);
        $semGarantia = self::criar(['status' => Status::Encaminhado, 'solucao' => Solucao::SemGarantia]);

        $this->assertSame('TrUrgente', classe_css_linha_v2('encaminhado', $alta, $zebra));
        $this->assertSame('TrInconformidade', classe_css_linha_v2('encaminhado', $semGarantia, $zebra));
    }

    #[Test]
    #[DataProvider('pesquisaProvider')]
    public function pesquisa_reproduz_sem_garantia_so_no_concluido_e_resto_inconformidade(
        Rma $registro,
        bool $esperaAlternar,
    ): void {
        $zebra = false;
        $classe = classe_css_linha_v2('pesquisa', $registro, $zebra);

        if ($registro->status === Status::Concluido && $registro->solucao === Solucao::SemGarantia) {
            $this->assertStringStartsWith('TrSemGarantia', $classe);
            $this->assertTrue($zebra);
        } else {
            $this->assertSame('TrInconformidade', $classe);
        }
        if (! $esperaAlternar && $registro->prioridade !== Prioridade::Alta) {
            $this->assertFalse($zebra);
        }
    }

    public static function pesquisaProvider(): array
    {
        return [
            'concluido sem garantia' => [
                self::criar(['status' => Status::Concluido, 'solucao' => Solucao::SemGarantia]),
                true,
            ],
            'prioridade alta' => [
                self::criar(['prioridade' => Prioridade::Alta]),
                false,
            ],
            'fora do estoque origem cliente' => [
                self::criar(['origem' => 'Cliente', 'marcarestoque' => false]),
                true,
            ],
        ];
    }
}
