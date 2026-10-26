<?php

namespace App\Mail;

use App\Models\PurchaseOrder;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class PurchaseOrderMail extends Mailable
{
    public function __construct(public PurchaseOrder $po, public string $pdfBinary) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Purchase Order '.$this->po->po_number.' - Vortex Supply Dynamics');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.po');
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfBinary, $this->po->po_number.'.pdf')->withMime('application/pdf'),
        ];
    }
}
