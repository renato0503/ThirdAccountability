<?php
namespace App\Mail;

use App\Models\Diligence;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DiligenciaNovaEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Diligence $diligencia) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Nova Diligência — ' . ($this->diligencia->project?->nome ?? 'Projeto'));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.diligencia-nova');
    }
}
