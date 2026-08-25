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
use App\Rma\Dominio\RepositorioDeRmas;
use App\Rma\Infraestrutura\RmasEmBanco;
use Illuminate\Support\Facades\Event;
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
    }
}
