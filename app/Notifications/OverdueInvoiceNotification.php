<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OverdueInvoiceNotification extends Notification
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
            'type'    => 'overdue_invoice',
            'icon'    => 'fas fa-exclamation-triangle',
            'color'   => 'text-danger',
            'title'   => 'Hóa đơn tháng ' . $this->invoice->month . '/' . $this->invoice->year . ' đã quá hạn!',
            'message' => 'Số tiền còn nợ: ' . number_format($this->invoice->debt, 0, ',', '.') . 'đ. Vui lòng thanh toán sớm.',
            'url'     => route('tenant.invoices.show', $this->invoice),
        ];
    }
}
