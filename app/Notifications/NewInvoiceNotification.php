<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewInvoiceNotification extends Notification
{
    use Queueable;

    public function __construct(public Invoice $invoice) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'new_invoice',
            'icon'    => 'fas fa-file-invoice-dollar',
            'color'   => 'text-primary',
            'title'   => 'Hóa đơn tháng ' . $this->invoice->month . '/' . $this->invoice->year . ' đã được lập',
            'message' => 'Tổng tiền: ' . number_format($this->invoice->total, 0, ',', '.') . 'đ — Hạn thanh toán: ' . \Carbon\Carbon::parse($this->invoice->due_date)->format('d/m/Y'),
            'url'     => route('tenant.invoices.show', $this->invoice),
        ];
    }
}
