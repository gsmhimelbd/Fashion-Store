<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminNewOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;
    public string $storeName;

    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->storeName = Setting::get('store_name', 'OnlineBdMart');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "🚨 [New Order Alert] #{$this->order->id} - ৳" . number_format($this->order->grand_total, 2) . " from {$this->order->customer_name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin_order_alert',
        );
    }
}
