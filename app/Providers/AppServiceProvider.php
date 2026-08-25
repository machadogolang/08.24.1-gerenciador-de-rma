<?php

namespace App\Providers;

use App\Rma\Aplicacao\EnviarNotificacaoDeConclusao;
use App\Rma\Aplicacao\EnviarNotificacaoDeTentativaNaoPermitida;
use App\Rma\Aplicacao\RegistrarModificacaoDeRma;
use App\Rma\Dominio\Eventos\RmaArquivado;
use App\Rma\Dominio\Eventos\RmaConcluido;
use App\Rma\Dominio\Eventos\RmaCriado;
use App\Rma\Dominio\Eventos\RmaEditado;
use App\Rma\Dominio\Eventos\RmaEncaminhado;
use App\Rma\Dominio\Eventos\RmaRecebido;
use App\Rma\Dominio\Eventos\RmaRevertido;
use App\Rma\Dominio\Eventos\SolucaoRegistrada;
use App\Rma\Dominio\Eventos\TentativaDeGravacaoNaoPermitida;
use App\Models\Fabricante;
use App\Rma\Aplicacao\PainelLateral\ListarPainelLateral;
use App\Rma\Dominio\RepositorioDeRmas;
use App\Rma\Infraestrutura\RmasEmBanco;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(RepositorioDeRmas::class, RmasEmBanco::class);
    }

    /**
     * Bootstrap any application services.
     *
     * Fase 7 (Auditoria) — projeto sem `EventServiceProvider` explícito (Laravel 13,
     * event discovery automático não se aplica a listeners com `handle(object $evento)`
     * genérico como `RegistrarModificacaoDeRma`), listeners registrados via
     * `Event::listen()` aqui.
     */
    public function boot(): void
    {
        foreach ([
            RmaCriado::class,
            RmaEditado::class,
            RmaRecebido::class,
            RmaEncaminhado::class,
            RmaConcluido::class,
            RmaArquivado::class,
            RmaRevertido::class,
            SolucaoRegistrada::class,
        ] as $evento) {
            Event::listen($evento, RegistrarModificacaoDeRma::class);
        }

        Event::listen(RmaConcluido::class, EnviarNotificacaoDeConclusao::class);
        Event::listen(TentativaDeGravacaoNaoPermitida::class, EnviarNotificacaoDeTentativaNaoPermitida::class);

        // VIS-V1-002 — `temas.v1.layout` inclui o painel "Novo" (`#JS-Novo`) em toda
        // página do TEMA V1, não só na rota de criação; o `<select fabricante_id>`
        // precisa da lista independente de qual controller renderizou a página atual.
        // Mesmo comportamento do legado: `menujs-top/novo.php` roda
        // `listar_nome_de_fabricantes()` toda vez que `#JS-Novo` é incluído no HTML,
        // painel visível ou não.
        View::composer('temas.v1.rma._form_novo', function ($view) {
            $view->with('fabricantes', Fabricante::query()->orderBy('nome')->get());
        });

        // CP19 (paridade visual V2) — `inc/rightmenu.php` é incluído por `index.php`
        // em toda página do TEMA V2, não só numa rota específica (mesmo raciocínio do
        // composer acima para o painel "Novo" do TEMA V1).
        View::composer('temas.v2.layout', function ($view) {
            $view->with('painelLateral', app(ListarPainelLateral::class)->listar());
        });
    }
}
