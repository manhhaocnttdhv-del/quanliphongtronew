<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use PayOS\PayOS;
use Exception;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    private $payOS;

    public function __construct()
    {
        $this->payOS = new PayOS(
            env('PAYOS_CLIENT_ID'),
            env('PAYOS_API_KEY'),
            env('PAYOS_CHECKSUM_KEY')
        );
    }

    public function createPaymentLink($invoice_id)
    {
        try {
            $invoice = Invoice::with(['contract.room', 'contract.tenant.user'])->findOrFail($invoice_id);

            // Cập nhật URL Return/Cancel để động theo domain hiện tại
            $returnUrl = url('/payment/success');
            $cancelUrl = url('/payment/cancel');

            // Mã đơn hàng phải là số (int32), giới hạn tối đa 2147483647
            // Gắn thêm timestamp để đảm bảo orderCode luôn duy nhất cho mỗi lần bấm thanh toán
            $orderCode = intval(date('ymdHis') . rand(10, 99));

            // Tính số tiền cần thanh toán
            $amountToPay = $invoice->total - $invoice->paid_amount;
            if ($amountToPay <= 0) {
                return back()->with('success', 'Hóa đơn này đã được thanh toán đầy đủ.');
            }

            // Ghi nhận orderCode vào Invoice để xử lý webhook sau này
            $invoice->update(['notes' => $invoice->notes . "\n[PayOS] OrderCode: " . $orderCode]);

            $data = [
                "orderCode" => $orderCode,
                "amount" => 2000, // FIXED test amount as requested
                "description" => "HD" . $invoice->id . " P." . $invoice->contract->room->name,
                "returnUrl" => $returnUrl,
                "cancelUrl" => $cancelUrl
            ];

            $response = $this->payOS->createPaymentLink($data);

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'checkoutUrl' => $response['checkoutUrl'],
                    'orderCode' => $orderCode
                ]);
            }

            return redirect($response['checkoutUrl']);
        } catch (Exception $e) {
            Log::error('PayOS Create Link Error: ' . $e->getMessage());
            return back()->withErrors(['Lỗi khi tạo link thanh toán: ' . $e->getMessage()]);
        }
    }

    public function paymentSuccess(Request $request)
    {
        $orderCode = $request->input('orderCode');
        
        // --- DÀNH CHO LOCALHOST / DEMO ĐỒ ÁN ---
        // Cập nhật hóa đơn thành Đã Thanh Toán (Paid) ngay lập tức khi người dùng 
        // quay lại trang báo thành công, bỏ qua bước chờ Webhook từ server.
        $invoice = Invoice::where('notes', 'LIKE', '%OrderCode: ' . $orderCode . '%')->first();

        // Nếu là invoice deposit → giao toàn bộ logic kích hoạt cho service.
        if ($invoice && $invoice->type === 'deposit' && $invoice->status !== 'paid') {
            app(\App\Services\Booking\BookingDepositService::class)
                ->markDepositPaid($invoice, (int) $invoice->total, (string) $orderCode);
            return view('payment.success', compact('orderCode'));
        }

        if ($invoice && $invoice->status !== 'paid') {
            $invoice->update([
                'paid_amount' => $invoice->total,
                'debt' => 0,
                'status' => 'paid',
                'notes' => $invoice->notes . "\n[PayOS] Đã thanh toán tự động hoàn tất (cập nhật qua Return URL)."
            ]);
        }
        
        return view('payment.success', compact('orderCode'));
    }

    public function paymentCancel(Request $request)
    {
        $orderCode = $request->input('orderCode');
        return view('payment.cancel', compact('orderCode'));
    }

    public function handleWebhook(Request $request)
    {
        try {
            $body = $request->all();
            Log::info("PayOS Webhook Received", $body);

            // Xác thực Webhook
            $webhookData = $this->payOS->verifyPaymentWebhookData($body);

            if ($webhookData['code'] == '00') {
                $orderCode = $webhookData['data']['orderCode'];
                $amount = $webhookData['data']['amount'];

                // Tìm hóa đơn có orderCode này trong notes
                $invoice = Invoice::where('notes', 'LIKE', '%OrderCode: ' . $orderCode . '%')->first();

                if ($invoice && $invoice->status !== 'paid') {
                    if ($invoice->type === 'deposit') {
                        app(\App\Services\Booking\BookingDepositService::class)
                            ->markDepositPaid(
                                $invoice,
                                (int) $amount,
                                (string) ($webhookData['data']['reference'] ?? $orderCode)
                            );
                    } else {
                        // Cập nhật đã thanh toán
                        $newPaidAmount = $invoice->paid_amount + $amount;
                        $debt = $invoice->total - $newPaidAmount;

                        $status = 'unpaid';
                        if ($newPaidAmount >= $invoice->total) {
                            $status = 'paid';
                        } elseif ($newPaidAmount > 0) {
                            $status = 'partial';
                        }

                        $invoice->update([
                            'paid_amount' => $newPaidAmount,
                            'debt' => $debt > 0 ? $debt : 0,
                            'status' => $status,
                            'notes' => $invoice->notes . "\n[PayOS] Đã thanh toán tự động: " . number_format($amount) . "đ (Mã GD: {$webhookData['data']['reference']})"
                        ]);
                    }

                    Log::info("Invoice {$invoice->id} marked as paid via PayOS.");
                }
            }

            return response()->json([
                "error" => 0,
                "message" => "Ok",
                "data" => null
            ]);
        } catch (Exception $e) {
            Log::error("PayOS Webhook Error: " . $e->getMessage());
            return response()->json([
                "error" => -1,
                "message" => $e->getMessage(),
                "data" => null
            ], 500);
        }
    }
}
