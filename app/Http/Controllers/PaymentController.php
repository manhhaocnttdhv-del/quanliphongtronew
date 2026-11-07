<?php

namespace App\Http\Controllers;

use App\Models\BookingRequest;
use App\Models\Invoice;
use App\Models\PaymentOrder;
use App\Services\InvoicePaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use PayOS\PayOS;
use RuntimeException;
use Throwable;

class PaymentController extends Controller
{
    private $payOS;

    private function gateway(): PayOS
    {
        if (! $this->payOS) {
            $credentials = config('services.payos');
            if (empty($credentials['client_id']) || empty($credentials['api_key']) || empty($credentials['checksum_key'])) {
                throw new RuntimeException('PayOS is not configured.');
            }
            $this->payOS = new PayOS($credentials['client_id'], $credentials['api_key'], $credentials['checksum_key']);
        }

        return $this->payOS;
    }

    public function createPaymentLink(Request $request, Invoice $invoice)
    {
        $invoice->load('contract.tenant');
        abort_unless($request->user()->hasRole('admin') || $invoice->contract->tenant->user_id === $request->user()->id, 403);
        abort_if($invoice->voided_at, 409, 'Hóa đơn đã hủy hoặc tất toán.');

        if ($invoice->type === 'deposit') {
            $booking = BookingRequest::where('contract_id', $invoice->contract_id)->firstOrFail();
            Gate::authorize('pay', $booking);
        }

        $order = DB::transaction(function () use ($invoice) {
            $invoice = Invoice::lockForUpdate()->findOrFail($invoice->id);
            abort_if($invoice->voided_at, 409, 'Hóa đơn đã hủy hoặc tất toán.');
            $amount = max(0, (int) $invoice->total - $invoice->net_paid_amount);
            if ($amount === 0) {
                return null;
            }

            return $invoice->paymentOrders()->where('status', 'pending')->where('amount', $amount)->latest('id')->first()
                ?? $invoice->paymentOrders()->create([
                    'order_code' => random_int(1000000000000, 99999999999999),
                    'amount' => $amount,
                ]);
        });

        if (! $order) {
            return back()->with('success', 'Hóa đơn này đã được thanh toán đầy đủ.');
        }
        if (! $order->wasRecentlyCreated && ! $order->checkout_url) {
            abort(409, 'Link thanh toán đang được tạo. Vui lòng thử lại sau.');
        }

        try {
            if (! $order->checkout_url) {
                $response = $this->gateway()->createPaymentLink([
                    'orderCode' => $order->order_code,
                    'amount' => (int) $order->amount,
                    'description' => 'HD'.$invoice->id,
                    'returnUrl' => route('payment.success'),
                    'cancelUrl' => route('payment.cancel'),
                ]);
                $order->update(['checkout_url' => $response['checkoutUrl']]);
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'checkoutUrl' => $order->checkout_url, 'orderCode' => $order->order_code]);
            }

            return redirect()->away($order->checkout_url);
        } catch (Throwable $exception) {
            $order->update(['status' => 'failed']);
            Log::error('PayOS checkout failed.', ['order_id' => $order->id, 'exception' => $exception::class]);

            return $request->wantsJson()
                ? response()->json(['message' => 'Chưa thể tạo link thanh toán. Vui lòng liên hệ quản lý.'], 503)
                : back()->withErrors(['payment' => 'Chưa thể tạo link thanh toán. Vui lòng liên hệ quản lý.']);
        }
    }

    public function paymentSuccess(Request $request)
    {
        $data = $request->validate(['orderCode' => ['nullable', 'integer', 'min:1']]);
        $orderCode = $data['orderCode'] ?? null;
        $confirmed = false;
        if ($orderCode && $request->user()) {
            $order = PaymentOrder::with('invoice.contract.tenant')->where('order_code', $orderCode)->first();
            if ($order && ($request->user()->hasRole('admin') || $order->invoice->contract->tenant->user_id === $request->user()->id)) {
                $confirmed = $order->status === 'paid';
            }
        }

        return view('payment.success', compact('orderCode', 'confirmed'));
    }

    public function paymentCancel(Request $request)
    {
        $data = $request->validate(['orderCode' => ['nullable', 'integer', 'min:1']]);

        return view('payment.cancel', ['orderCode' => $data['orderCode'] ?? null]);
    }

    public function handleWebhook(Request $request, InvoicePaymentService $payments)
    {
        try {
            $data = $this->gateway()->verifyPaymentWebhookData($request->all());
        } catch (Throwable $exception) {
            return response()->json(['error' => -1, 'message' => 'Invalid payment signature.'], 400);
        }

        if (($data['code'] ?? null) !== '00') {
            return response()->json(['error' => 0, 'message' => 'Ok']);
        }

        $data = validator($data, [
            'orderCode' => ['required', 'integer', 'min:1'],
            'amount' => ['required', 'integer', 'min:1', 'max:999999999999'],
            'reference' => ['required', 'string', 'max:150'],
        ])->validate();

        $order = PaymentOrder::where('order_code', $data['orderCode'])->first();
        if (! $order) {
            Log::warning('Unknown verified PayOS order.', ['order_code' => $data['orderCode']]);

            return response()->json(['error' => 0, 'message' => 'Unknown order.']);
        }

        $payments->record($order->invoice, (int) $data['amount'], $data['reference'], $order);

        return response()->json(['error' => 0, 'message' => 'Ok', 'data' => null]);
    }
}
