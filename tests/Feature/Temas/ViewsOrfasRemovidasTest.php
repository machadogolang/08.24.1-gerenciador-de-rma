<?php

namespace Tests\Feature\Temas;

use Illuminate\Support\Facades\View;
use Tests\TestCase;

/**
 * UI-07.2 - as views nao-tematicas sem consumidor foram removidas com prova de zero
 * rota/controller/include/extends/view_do_tema/rota_tema/teste. Este teste evita
 * reintroducao acidental e garante que as versoes por tema continuam existindo.
 */
class ViewsOrfasRemovidasTest extends TestCase
{
    /** @return list<string> */
    public static function viewsRemovidas(): array
    {
        return [
            'parceiros._form',
            'parceiros.index',
            'rma.index',
            'rma.show',
            'rma.create',
            'rma.edit',
            'rma._campos',
            'rma._painel_de_alertas',
            'rma.credito.index',
            'rma.relatorios.rcd',
            'rma.relatorios.rpec',
            'rma.relatorios.rmpe',
            'identidade.usuarios.index',
            'identidade.perfil.senha',
            'identidade.historico-de-acesso.index',
        ];
    }

    public function test_views_sem_consumidor_foram_removidas(): void
    {
        foreach (self::viewsRemovidas() as $nome) {
            $this->assertFalse(View::exists($nome), "View orfa reintroduzida: {$nome}");
        }
    }

    public function test_views_por_tema_continuam_existindo(): void
    {
        $compartilhadas = [
            'rma.index',
            'rma.show',
            'rma.create',
            'rma.edit',
            'rma.credito.index',
            'rma.relatorios.rcd',
            'rma.relatorios.rpec',
            'rma.relatorios.rmpe',
            'parceiros.index',
            'parceiros._form',
            'identidade.usuarios',
            'identidade.historico-de-acesso.index',
        ];

        foreach ($compartilhadas as $nome) {
            foreach (['v1', 'v2'] as $tema) {
                $this->assertTrue(
                    View::exists("temas.{$tema}.{$nome}"),
                    "View de tema ausente: temas.{$tema}.{$nome}",
                );
            }
        }
    }
}
