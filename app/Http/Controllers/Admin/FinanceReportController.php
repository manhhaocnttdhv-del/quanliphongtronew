<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\House;
use App\Services\FinanceReportService;
use App\Services\SpreadsheetExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class FinanceReportController extends Controller
{
    public const LABELS = [
        'receipt' => 'Thu tiền thuê', 'expense' => 'Chi vận hành', 'income_refund' => 'Hoàn tiền thuê',
        'deposit_received' => 'Nhận cọc', 'deposit_refund' => 'Hoàn cọc', 'deposit_application' => 'Cọc bù công nợ',
    ];

    public function index(Request $request, FinanceReportService $reports, SpreadsheetExport $export)
    {
        $data = $request->validate(['period' => 'nullable|date_format:Y-m', 'house_id' => 'nullable|exists:houses,id', 'export' => 'nullable|in:xlsx', 'page' => 'nullable|integer|min:1']);
        $period = Carbon::createFromFormat('!Y-m', $data['period'] ?? today()->format('Y-m'));
        $houseId = ! empty($data['house_id']) ? (int) $data['house_id'] : null;
        $entries = $reports->entries($period->copy()->startOfMonth(), $period->copy()->endOfMonth(), $houseId);
        $summary = $reports->summary($entries);
        if (($data['export'] ?? '') === 'xlsx') {
            $rows = [['Ngày', 'Chi nhánh', 'Phòng', 'Loại', 'Nội dung', 'Tiền vào', 'Tiền ra', 'Ảnh hưởng lợi nhuận']];
            foreach ($entries as $entry) {
                $rows[] = [$entry['date'], $entry['house'], $entry['room'], self::LABELS[$entry['kind']], $entry['description'], $entry['in'], $entry['out'], $entry['profit']];
            }
            $rows[] = ['TỔNG', '', '', '', '', (int) $entries->sum('in'), (int) $entries->sum('out'), $summary['profit']];

            return response()->download($export->create($rows), 'thu-chi-'.$period->format('Y-m').'.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
        }
        $transactions = new LengthAwarePaginator($entries->forPage($request->integer('page', 1), 25), $entries->count(), 25, $request->integer('page', 1), ['path' => $request->url(), 'query' => $request->query()]);
        $houses = House::orderBy('name')->get();

        return view('admin.reports.index', compact('period', 'summary', 'transactions', 'houses'));
    }
}
