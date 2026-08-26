<?php

namespace Database\Seeders;

use App\Identidade\Dominio\Papel;
use App\Models\AssistenciaTecnica;
use App\Models\Cliente;
use App\Models\Fabricante;
use App\Models\Fornecedor;
use App\Models\Rma;
use App\Models\User;
use App\Rma\Dominio\Prioridade;
use App\Rma\Dominio\Solucao;
use App\Rma\Dominio\Status;
use Carbon\CarbonImmutable;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use LogicException;

class QaSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        if (app()->environment('production')) {
            throw new LogicException('O QaSeeder nao pode ser executado em producao.');
        }

        fake()->seed(1597);
        fake()->unique(true);

        $clientes = Cliente::factory()->count(30)->create();
        $fabricantes = Fabricante::factory()->count(10)->create();
        $fornecedores = Fornecedor::factory()->count(10)->create();
        $assistencias = AssistenciaTecnica::factory()->count(5)->create();
        $operador = User::query()->where('papel', Papel::Operador)->sole();

        $inicio = CarbonImmutable::create(2026, 1, 1, 0, 0, 0, 'America/Sao_Paulo');
        $status = Status::cases();
        $prioridades = Prioridade::cases();

        foreach (range(1, 60) as $indice) {
            $estado = $status[($indice - 1) % count($status)];
            $criadoEm = $inicio->copy()->addDays($indice - 1);
            $recebidoEm = $estado === Status::Entrada ? null : $criadoEm->copy()->addDay();
            $encaminhadoEm = in_array($estado, [Status::Encaminhado, Status::Concluido], true)
                ? $criadoEm->copy()->addDays(2)
                : null;
            $concluidoEm = $estado === Status::Concluido ? $criadoEm->copy()->addDays(8) : null;
            $arquivadoEm = $estado === Status::Arquivado ? $criadoEm->copy()->addDays(3) : null;
            $destinatario = match ($indice % 3) {
                0 => $assistencias[($indice - 1) % $assistencias->count()],
                1 => $fornecedores[($indice - 1) % $fornecedores->count()],
                default => $fabricantes[($indice - 1) % $fabricantes->count()],
            };

            // CP13 (fase 2 V1, `plano-execucao-paridade-visual-v1-fase2.md`) —
            // comprimento de texto comparável ao Legacy real (ex.: `os` como
            // "5947"/"6040", 4-5 dígitos, não "OS-QA-00059"; `descricao` como
            // "NOBREAK 700VA"/"ESTABILIZADOR 300VA", ~13-20 caracteres) — só o
            // COMPRIMENTO muda, o dado continua 100% fictício e determinístico.
            // `PendenteCredito` (CP13-02): nenhum registro do seed anterior tinha
            // esse valor de `solucao` — a tela "Aguardando Crédito" (fase 1,
            // CMP-V1-005) só tinha sido comparada por teste automatizado, sem par de
            // screenshot, por falta de dado. O 3º registro (primeiro
            // `Status::Encaminhado`) ganha esse valor aditivamente, sem remover
            // nenhuma combinação já coberta.
            $solucao = match (true) {
                $estado === Status::Concluido => Solucao::Reparo,
                $indice === 3 => Solucao::PendenteCredito,
                default => null,
            };

            Rma::factory()->create([
                'descricao' => sprintf('Ficticio QA %03d', $indice),
                'fabricante_id' => $fabricantes[($indice - 1) % $fabricantes->count()]->id,
                'fornecedor_id' => $fornecedores[($indice - 1) % $fornecedores->count()]->id,
                'cliente_id' => $clientes[($indice - 1) % $clientes->count()]->id,
                'modelo' => sprintf('MODELO-QA-%03d', $indice),
                'sn' => sprintf('SN-QA-%06d', $indice),
                'os' => (string) (5900 + $indice),
                'origem' => $indice % 2 === 0 ? 'Cliente' : 'Loja',
                'empresa' => 'Empresa Ficticia QA',
                'defeito' => sprintf('Defeito ficticio para teste %03d', $indice),
                'observacao' => 'Registro inteiramente ficticio e deterministico para QA local.',
                'status' => $estado,
                'recebido_em' => $recebidoEm,
                'encaminhado_em' => $encaminhadoEm,
                'concluido_em' => $concluidoEm,
                'arquivado_em' => $arquivadoEm,
                'protocolo' => sprintf('PROTOCOLO-QA-%04d', $indice),
                'solucao' => $solucao,
                'destinatario_type' => $destinatario::class,
                'destinatario_id' => $destinatario->id,
                'prioridade' => $prioridades[($indice - 1) % count($prioridades)],
                'marcarestoque' => $indice % 4 !== 0,
                'credito_disponivel' => $estado === Status::Concluido && $indice % 2 === 0,
                'operador_id' => $operador->id,
                'created_at' => $criadoEm,
                'updated_at' => $criadoEm,
            ]);
        }
    }
}
