<?php

namespace Tests\Feature\Tenant;

use App\Models\Company;
use App\Models\ContadorDeRma;
use Symfony\Component\Process\Process;
use Tests\TestCase;

/**
 * EVO-SAAS-001 (S10.4) - prova de concorrencia real com multiplos processos de sistema
 * operacional independentes e conexoes MySQL separadas.
 *
 * Nao usa RefreshDatabase para que os dados persistidos estejam imediatamente commitados
 * no MySQL e visiveis aos processos externos filhos. Limpeza garantida no bloco finally.
 */
class ConcorrenciaContadorTest extends TestCase
{
    public function test_processos_independentes_concorrentes_nao_colidem_numeros(): void
    {
        $empresa = Company::query()->create([
            'nome' => 'Empresa Concorrente ' . uniqid('', true),
        ]);

        try {
            $numProcessos = 4;
            $qtdPorProcesso = 20;
            $totalEsperado = $numProcessos * $qtdPorProcesso;

            /** @var Process[] $processos */
            $processos = [];
            for ($i = 0; $i < $numProcessos; $i++) {
                $processo = new Process([
                    PHP_BINARY,
                    base_path('artisan'),
                    'rma:reservar-numeros',
                    (string) $empresa->id,
                    (string) $qtdPorProcesso,
                ]);
                $processo->setTimeout(60);
                $processo->start();
                $processos[] = $processo;
            }

            // Aguarda a finalizacao de todos os processos paralelos
            foreach ($processos as $processo) {
                $processo->wait();
                $this->assertTrue(
                    $processo->isSuccessful(),
                    'Processo falhou: ' . $processo->getErrorOutput() . ' | ' . $processo->getOutput()
                );
            }

            // Consolida todos os numeros retornados pelos processos independentes
            $todosNumeros = [];
            foreach ($processos as $processo) {
                $saida = trim($processo->getOutput());
                $numerosProcesso = json_decode($saida, true, 512, JSON_THROW_ON_ERROR);
                $this->assertCount($qtdPorProcesso, $numerosProcesso);
                foreach ($numerosProcesso as $num) {
                    $todosNumeros[] = (int) $num;
                }
            }

            // 1. Quantidade total gerada
            $this->assertCount($totalEsperado, $todosNumeros);

            // 2. Zero colisoes (estritamente unicos)
            $this->assertSame($totalEsperado, count(array_unique($todosNumeros)));

            // 3. Sequencia contigua perfeita de 1 a N
            sort($todosNumeros);
            $this->assertSame(range(1, $totalEsperado), $todosNumeros);

            // 4. Estado final do contador no banco de dados
            $proximoBanco = ContadorDeRma::query()->where('company_id', $empresa->id)->value('proximo_numero');
            $this->assertSame($totalEsperado + 1, (int) $proximoBanco);
        } finally {
            ContadorDeRma::query()->where('company_id', $empresa->id)->delete();
            $empresa->delete();
        }
    }
}
