<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaymentReviewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Throwable;

class PaymentReviewController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate(['status' => 'nullable|in:pending,resolved']);
        $resolved = ($data['status'] ?? 'pending') === 'resolved';
        $payments = Payment::with(['invoice.contract.room.house', 'invoice.contract.tenant.user', 'resolution.resolver'])
            ->when($resolved, fn ($q) => $q->whereHas('resolution'), fn ($q) => $q->where('requires_review', true)->whereDoesntHave('resolution'))
            ->latest()->paginate(20)->withQueryString();

        return view('admin.payment-reviews.index', compact('payments', 'resolved'));
    }

    public function show(Payment $payment, PaymentReviewService $reviews)
    {
        abort_unless($payment->requires_review || $payment->resolution()->exists(), 404);
        $payment->load(['invoice.contract.room.house', 'invoice.contract.tenant.user', 'resolution.resolver']);
        $refundable = $reviews->refundable($payment);

        return view('admin.payment-reviews.show', compact('payment', 'refundable'));
    }

    public function store(Request $request, Payment $payment, PaymentReviewService $reviews)
    {
        $data = $request->validate([
            'outcome' => 'required|in:retain,refund', 'reason' => 'required|string|min:10|max:3000',
            'refund_amount' => 'required_if:outcome,refund|nullable|integer|min:1|max:999999999999',
            'reference' => 'required_if:outcome,refund|nullable|string|min:3|max:150',
            'evidence' => 'required_if:outcome,refund|nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);
        $path = $request->file('evidence')?->store('finance/payment-refunds/'.$payment->id, 'private');
        try {
            $reviews->resolve($payment, $data + ['evidence_path' => $path], $request->user()->id);
        } catch (Throwable $exception) {
            if ($path) {
                Storage::disk('private')->delete($path);
            }
            throw $exception;
        }

        return redirect()->route('admin.payment-reviews.show', $payment)->with('success', 'Đã lưu kết quả đối soát.');
    }

    public function evidence(Payment $payment)
    {
        $path = $payment->resolution?->evidence_path;
        abort_unless($path && Storage::disk('private')->exists($path), 404);

        return Storage::disk('private')->download($path, basename($path), ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
