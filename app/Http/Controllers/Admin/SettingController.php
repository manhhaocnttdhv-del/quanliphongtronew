<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SettingController extends Controller
{
    public function index()
    {
        $general      = Setting::getGroup('general');
        $finance      = Setting::getGroup('finance');
        $notification = Setting::getGroup('notification');

        return view('admin.settings.index', compact('general', 'finance', 'notification'));
    }

    public function update(Request $request)
    {
        $tab = $request->input('tab', 'general');

        match ($tab) {
            'general'      => $this->saveGeneral($request),
            'finance'      => $this->saveFinance($request),
            'notification' => $this->saveNotification($request),
            'account'      => $this->saveAccount($request),
            default        => null,
        };

        return redirect()->route('admin.settings.index', ['tab' => $tab])
            ->with('success', 'Đã lưu cài đặt thành công!');
    }

    private function saveGeneral(Request $request): void
    {
        $request->validate([
            'site_name'       => 'required|string|max:100',
            'site_tagline'    => 'nullable|string|max:200',
            'contact_email'   => 'nullable|email|max:100',
            'contact_phone'   => 'nullable|string|max:20',
            'contact_address' => 'nullable|string|max:255',
        ]);

        foreach (['site_name', 'site_tagline', 'contact_email', 'contact_phone', 'contact_address'] as $key) {
            Setting::set($key, $request->input($key, ''));
        }
    }

    private function saveFinance(Request $request): void
    {
        $request->validate([
            'vat_rate'        => 'required|numeric|min:0|max:100',
            'late_fee_rate'   => 'required|numeric|min:0|max:100',
            'invoice_due_days'=> 'required|integer|min:1|max:60',
        ]);

        foreach (['currency', 'vat_rate', 'late_fee_rate', 'invoice_due_days'] as $key) {
            Setting::set($key, $request->input($key, ''));
        }
    }

    private function saveNotification(Request $request): void
    {
        foreach (['notify_new_invoice', 'notify_overdue', 'notify_maintenance'] as $key) {
            Setting::set($key, $request->has($key) ? '1' : '0');
        }
    }

    private function saveAccount(Request $request): void
    {
        $user = auth()->user();

        $request->validate([
            'name'  => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->name  = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $request->validate([
                'password'              => ['required', Password::min(8)],
                'password_confirmation' => 'required|same:password',
            ]);
            $user->password = Hash::make($request->password);
        }

        $user->save();
    }
}
