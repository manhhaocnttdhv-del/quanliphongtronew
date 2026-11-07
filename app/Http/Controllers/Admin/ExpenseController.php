<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\House;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate(['period' => 'nullable|date_format:Y-m', 'house_id' => 'nullable|exists:houses,id', 'status' => 'nullable|in:active,voided']);
        $query = Expense::with(['house', 'creator'])->when($request->filled('period'), fn ($q) => $q->whereYear('spent_on', substr($data['period'], 0, 4))->whereMonth('spent_on', substr($data['period'], 5, 2)))
            ->when($request->filled('house_id'), fn ($q) => $q->where('house_id', $data['house_id']));
        ($data['status'] ?? 'active') === 'voided' ? $query->whereNotNull('voided_at') : $query->whereNull('voided_at');
        $total = (int) (clone $query)->sum('amount');
        $expenses = $query->orderByDesc('spent_on')->latest('id')->paginate(20)->withQueryString();
        $houses = House::orderBy('name')->get();

        return view('admin.expenses.index', compact('expenses', 'houses', 'total'));
    }

    public function create()
    {
        $houses = House::orderBy('name')->get();
        $token = (string) \Illuminate\Support\Str::uuid();
        session()->put('expense_forms.'.$token, true);

        return view('admin.expenses.create', compact('houses', 'token'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'house_id' => 'nullable|exists:houses,id', 'category' => ['required', Rule::in(array_keys(Expense::CATEGORIES))],
            'description' => 'required|string|max:255', 'amount' => 'required|integer|min:1|max:999999999999',
            'spent_on' => 'required|date|before_or_equal:today', 'reference' => 'nullable|string|max:150',
            'evidence' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120', 'submission_token' => 'required|uuid',
        ]);
        $key = 'expense_forms.'.$data['submission_token'];
        if (! $request->session()->has($key)) {
            throw ValidationException::withMessages(['submission_token' => 'Biểu mẫu đã được gửi hoặc hết hạn.']);
        }
        $submissionKey = $data['submission_token'];
        unset($data['evidence'], $data['submission_token']);
        $path = $request->file('evidence')?->store('finance/expenses', 'private');
        try {
            $expense = Expense::firstOrCreate(['submission_key' => $submissionKey], $data + ['evidence_path' => $path, 'created_by' => $request->user()->id]);
            if (! $expense->wasRecentlyCreated && $path) {
                Storage::disk('private')->delete($path);
            }
        } catch (Throwable $exception) {
            if ($path) {
                Storage::disk('private')->delete($path);
            } throw $exception;
        }
        $request->session()->forget($key);

        return redirect()->route('admin.expenses.index')->with('success', 'Đã ghi sổ chi phí.');
    }

    public function void(Request $request, Expense $expense)
    {
        $data = $request->validate(['void_reason' => 'required|string|min:10|max:2000']);
        DB::transaction(function () use ($expense, $data, $request) {
            $expense = Expense::lockForUpdate()->findOrFail($expense->id);
            if ($expense->voided_at) {
                throw ValidationException::withMessages(['expense' => 'Khoản chi đã được hủy.']);
            }
            $expense->update($data + ['voided_at' => now(), 'voided_by' => $request->user()->id]);
        });

        return back()->with('success', 'Đã hủy khoản chi và giữ lại lịch sử.');
    }

    public function evidence(Expense $expense)
    {
        abort_unless($expense->evidence_path && Storage::disk('private')->exists($expense->evidence_path), 404);

        return Storage::disk('private')->download($expense->evidence_path, basename($expense->evidence_path), ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
