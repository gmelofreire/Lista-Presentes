<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NovaListaGrupoMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    /** @return list<int> */
    public function backoff(): array
    {
        return [30, 120, 600];
    }

    public $lista;

    public $grupo;

    public $criador;

    public function __construct($lista, $grupo, $criador)
    {
        $this->lista = $lista;
        $this->grupo = $grupo;
        $this->criador = $criador;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Nova lista '{$this->lista->nome}' no grupo {$this->grupo->nome}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.nova-lista-grupo',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
