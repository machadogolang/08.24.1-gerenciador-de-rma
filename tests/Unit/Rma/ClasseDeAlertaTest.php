<?php

namespace Tests\Unit\Rma;

use App\Rma\Dominio\ClasseDeAlerta;
use App\Rma\Dominio\Origem;
use App\Rma\Dominio\Prioridade;
use App\Rma\Dominio\Rma;
use App\Rma\Dominio\Solucao;
use Tests\TestCase;

/**
 * RN-11 (`LEG-RMA-028`) — os 4 critérios do `match(true)` de `Rma::classeDeAlerta()`,
 * na ordem certa (primeiro critério que bate vence).
 */
class ClasseDeAlertaTest extends TestCase
{
    private function rma(array $sobrescritas = []): Rma
    {
        $base = [
            'id' => null,
            'descricao' => 'Descrição',
            'fabricanteId' => null,
            'fornecedorId' => null,
            'modelo' => null,
            'sn' => null,
            'os' => null,
            'origem' => null,
            'empresa' => null,
            'clienteId' => null,
            'defeito' => 'Defeito',
            'observacao' => null,
            'prioridade' => null,
            'marcarestoque' => true,
            'solucao' => null,
            'createdAt' => now(),
        ];

        $dados = array_merge($base, $sobrescritas);

        return new Rma(
            id: $dados['id'],
            descricao: $dados['descricao'],
            fabricanteId: $dados['fabricanteId'],
            fornecedorId: $dados['fornecedorId'],
            modelo: $dados['modelo'],
            sn: $dados['sn'],
            os: $dados['os'],
            origem: $dados['origem'],
            empresa: $dados['empresa'],
            clienteId: $dados['clienteId'],
            defeito: $dados['defeito'],
            observacao: $dados['observacao'],
            solucao: $dados['solucao'],
            prioridade: $dados['prioridade'],
            marcarestoque: $dados['marcarestoque'],
            createdAt: $dados['createdAt'],
        );
    }

    public function test_criterio_1_sem_garantia_gera_inconformidade(): void
    {
        $rma = $this->rma(['solucao' => Solucao::SemGarantia]);

        $this->assertSame(ClasseDeAlerta::Inconformidade, $rma->classeDeAlerta());
    }

    public function test_criterio_2_prioridade_alta_gera_inconformidade(): void
    {
        $rma = $this->rma(['prioridade' => Prioridade::Alta]);

        $this->assertSame(ClasseDeAlerta::Inconformidade, $rma->classeDeAlerta());
    }

    public function test_criterio_3_origem_cliente_fora_de_estoque_fora_do_prazo_gera_inconformidade(): void
    {
        $rma = $this->rma([
            'origem' => Origem::Cliente->value,
            'marcarestoque' => false,
            'createdAt' => now()->subDays(31),
        ]);

        $this->assertSame(ClasseDeAlerta::Inconformidade, $rma->classeDeAlerta());
    }

    public function test_criterio_3_dentro_do_prazo_nao_dispara_isoladamente(): void
    {
        $rma = $this->rma([
            'origem' => 'Outra Origem Qualquer',
            'marcarestoque' => false,
            'createdAt' => now()->subDays(5),
        ]);

        $this->assertSame(ClasseDeAlerta::Neutro, $rma->classeDeAlerta());
    }

    public function test_criterio_4_fora_de_estoque_origem_cliente_ou_licitacao_gera_inconformidade(): void
    {
        $rma = $this->rma([
            'origem' => Origem::Licitacao->value,
            'marcarestoque' => false,
            'createdAt' => now(),
        ]);

        $this->assertSame(ClasseDeAlerta::Inconformidade, $rma->classeDeAlerta());
    }

    public function test_criterio_4_nao_dispara_para_origem_fora_do_par_cliente_licitacao(): void
    {
        $rma = $this->rma([
            'origem' => Origem::Loja->value,
            'marcarestoque' => false,
            'createdAt' => now(),
        ]);

        $this->assertSame(ClasseDeAlerta::Neutro, $rma->classeDeAlerta());
    }

    public function test_nenhum_criterio_bate_resulta_em_neutro(): void
    {
        $rma = $this->rma([
            'origem' => Origem::Loja->value,
            'marcarestoque' => true,
            'prioridade' => Prioridade::Baixa,
            'solucao' => Solucao::Reparo,
        ]);

        $this->assertSame(ClasseDeAlerta::Neutro, $rma->classeDeAlerta());
    }

    public function test_primeiro_criterio_que_bate_vence_nao_precisa_avaliar_os_demais(): void
    {
        // Solucao::SemGarantia (critério 1) bate primeiro; createdAt nulo faria
        // `origemEhTerceiroForaDoPrazo()` (critério 3) quebrar se fosse avaliado sem
        // guarda — a ordem do match(true) garante que nunca chega lá.
        $rma = $this->rma([
            'solucao' => Solucao::SemGarantia,
            'origem' => Origem::Cliente->value,
            'marcarestoque' => false,
            'createdAt' => null,
        ]);

        $this->assertSame(ClasseDeAlerta::Inconformidade, $rma->classeDeAlerta());
    }
}
