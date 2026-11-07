<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReminderDelivery;
use App\Models\Setting;
use App\Services\RentalReminderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReminderController extends Controller
{
    public function index()
    {
        $deliveries = ReminderDelivery::with('user')->latest('sent_at')->paginate(20);

        return view('admin.reminders.index', compact('deliveries'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'reminders_enabled' => 'sometimes|boolean', 'reminder_before_days' => 'required|integer|min:0|max:30',
            'reminder_repeat_days' => 'required|integer|min:1|max:30', 'reminder_contract_days' => 'required|integer|min:1|max:90',
        ]);
        $data['reminders_enabled'] = $request->boolean('reminders_enabled') ? '1' : '0';
        DB::transaction(function () use ($data) {
            foreach ($data as $key => $value) {
                Setting::set($key, $value);
            }
        });

        return back()->with('success', 'Đã lưu lịch nhắc hạn.');
    }

    public function send(RentalReminderService $reminders)
    {
        return back()->with('success', 'Đã gửi '.$reminders->send().' thông báo mới.');
    }
}
