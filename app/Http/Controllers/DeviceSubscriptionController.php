<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DeviceSubscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeviceSubscriptionController extends Controller
{
    public function lookup(): View
    {
        return view('superadmin.devices.subscriptions.lookup');
    }

    public function resolve(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'device_uid' => ['required', 'string', 'exists:devices,device_uid'],
        ]);

        $device = Device::where('device_uid', $validated['device_uid'])->first();

        return redirect()->route('superadmin.devices.subscriptions.create', $device);
    }

    public function create(Device $device): View
    {
        return view('superadmin.devices.subscriptions.create', compact('device'));
    }

    public function store(Request $request, Device $device): RedirectResponse
    {
        $validated = $request->validate([
            'subscription_name' => ['required', 'string', 'max:255'],
            'plan' => ['nullable', 'string', 'max:255'],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $device->subscriptions()->create([
            'subscription_name' => $validated['subscription_name'],
            'plan' => $validated['plan'] ?? null,
            'starts_on' => $validated['starts_on'] ?? null,
            'ends_on' => $validated['ends_on'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'notes' => $validated['notes'] ?? null,
        ]);

        $device->refreshStatus();

        return redirect()->route('superadmin.devices.show', $device)
            ->with('success', 'Subscription added successfully.');
    }

    public function destroy(DeviceSubscription $subscription): RedirectResponse
    {
        $device = $subscription->device;
        $subscription->delete();

        $device->refreshStatus();

        return redirect()->route('superadmin.devices.show', $device)
            ->with('success', 'Subscription removed successfully.');
    }

    public function toggle(DeviceSubscription $subscription): RedirectResponse
    {
        $subscription->update([
            'is_active' => ! $subscription->is_active,
        ]);

        $subscription->device->refreshStatus();

        return redirect()->route('superadmin.devices.show', $subscription->device)
            ->with('success', 'Subscription status updated.');
    }
}
