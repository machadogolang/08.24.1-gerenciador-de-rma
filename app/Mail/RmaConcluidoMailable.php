<?php

namespace App\Mail;

use App\Rma\Dominio\Rma;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * `LEG-RMA-045` (`ezequiel()`). Enviado por `EnviarNotificacaoDeConclusao` quando
 * `RmaConcluido` dispara.
 */
class RmaConcluidoMailable extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Rma $rma,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "RMA #{$this->rma->id} concluído",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.rma-concluido',
        );
    }
}
