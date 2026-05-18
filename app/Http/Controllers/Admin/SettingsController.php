<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAction;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $settings = [
            'site_name' => Setting::getValue('site_name', 'CardFlow'),
            'maintenance_mode' => Setting::getValue('maintenance_mode', '0'),
            'require_proof' => Setting::getValue('require_proof', '1'),
            'max_price_limit' => Setting::getValue('max_price_limit', '0'),
            'auto_expire_days' => Setting::getValue('auto_expire_days', '0'),
            'auto_flag_price' => Setting::getValue('auto_flag_price', '0'),
            'notify_admin_reports' => Setting::getValue('notify_admin_reports', '1'),
            'admin_notification_email' => Setting::getValue('admin_notification_email', auth()->user()->email),
            'announcement_message' => Setting::getValue('announcement_message', ''),
            'announcement_active' => Setting::getValue('announcement_active', '0'),
            'announcement_dismissible' => Setting::getValue('announcement_dismissible', '1'),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'site_name' => ['required', 'string', 'max:255'],
            'max_price_limit' => ['nullable', 'numeric', 'min:0'],
            'auto_expire_days' => ['nullable', 'integer', 'min:0'],
            'auto_flag_price' => ['nullable', 'numeric', 'min:0'],
            'admin_notification_email' => ['nullable', 'email', 'max:255'],
            'announcement_message' => ['nullable', 'string', 'max:1000'],
            'display_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        foreach ([
            'site_name',
            'max_price_limit',
            'auto_expire_days',
            'auto_flag_price',
            'admin_notification_email',
            'announcement_message',
        ] as $key) {
            Setting::setValue($key, $validated[$key] ?? '0');
        }

        foreach (['maintenance_mode', 'require_proof', 'notify_admin_reports', 'announcement_active', 'announcement_dismissible'] as $key) {
            Setting::setValue($key, $request->boolean($key));
        }

        $userData = [
            'name' => $validated['display_name'],
            'email' => $validated['email'],
        ];

        if (! empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        auth()->user()->forceFill($userData)->save();
        AdminAction::log('update_settings', null, 'Settings', 'Updated platform settings', 'setting');

        return back()->with('status', 'Admin settings saved.');
    }
}
