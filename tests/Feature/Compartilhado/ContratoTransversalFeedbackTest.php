<?php

namespace Tests\Feature\Compartilhado;

use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContratoTransversalFeedbackTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(Papel $papel = Papel::Operador, TemaPreferido $tema = TemaPreferido::V1): User
    {
        return User::factory()->create([
            'papel' => $papel,
            'tema_preferido' => $tema,
        ]);
    }

    public function test_acesso_a_recurso_inexistente_retorna_404_com_pagina_estruturada(): void
    {
        $usuario = $this->usuario();

        $response = $this->actingAs($usuario)->get('/rma/9999999');

        $response->assertNotFound();
        $response->assertSeeText('404');
        $response->assertSeeText('Página ou Registro Não Encontrado');
        $response->assertSeeText('Voltar à Página Inicial');
    }

    public function test_mensagens_de_status_e_sucesso_renderizam_no_layout_v1(): void
    {
        $usuario = $this->usuario(Papel::Operador, TemaPreferido::V1);

        $response = $this->actingAs($usuario)
            ->withSession(['status' => 'Operação concluída com êxito!'])
            ->get('/rmas-entrada');

        $response->assertOk();
        $response->assertSee('centrodeavisos');
        $response->assertSeeText('Operação concluída com êxito!');
    }

    public function test_mensagens_de_erro_renderizam_no_layout_v1(): void
    {
        $usuario = $this->usuario(Papel::Operador, TemaPreferido::V1);

        $response = $this->actingAs($usuario)
            ->withSession(['erro' => 'Falha ao processar solicitação.'])
            ->get('/rmas-entrada');

        $response->assertOk();
        $response->assertSee('centrodeavisos');
        $response->assertSeeText('Falha ao processar solicitação.');
    }

    public function test_mensagens_de_sucesso_e_erro_renderizam_no_layout_v2(): void
    {
        $usuario = $this->usuario(Papel::Operador, TemaPreferido::V2);

        $response = $this->actingAs($usuario)
            ->withSession(['sucesso' => 'RMA atualizado com sucesso!'])
            ->get('/rmas');

        $response->assertOk();
        $response->assertSee('centrodeavisos');
        $response->assertSeeText('RMA atualizado com sucesso!');
    }

    public function test_mensagens_de_feedback_renderizam_no_tema_v3_com_aria(): void
    {
        $usuario = $this->usuario(Papel::Operador, TemaPreferido::V1);

        $response = $this->actingAs($usuario)
            ->withSession(['sucesso' => 'Registro V3 salvo com sucesso!'])
            ->get('/v3');

        $response->assertOk();
        $response->assertSee('role="status"', false);
        $response->assertSeeText('Registro V3 salvo com sucesso!');

        $responseErro = $this->actingAs($usuario)
            ->withSession(['erro' => 'Erro de validação no V3.'])
            ->get('/v3');

        $responseErro->assertOk();
        $responseErro->assertSee('role="alert"', false);
        $responseErro->assertSeeText('Erro de validação no V3.');
    }

    public function test_resumo_de_erros_de_validacao_renderiza_adequadamente(): void
    {
        $errors = new \Illuminate\Support\ViewErrorBag();
        $errors->put('default', new \Illuminate\Support\MessageBag([
            'descricao' => ['O campo descrição é obrigatório.'],
        ]));

        $view = $this->view('compartilhado.mensagens_feedback', [
            'errors' => $errors,
        ]);

        $view->assertSee('O campo descrição é obrigatório.');
        $view->assertSee('centrodeavisos');
    }
}
