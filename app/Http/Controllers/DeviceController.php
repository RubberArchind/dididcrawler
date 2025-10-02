<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DeviceSubscription;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeviceController extends Controller
{
    public function index(Request $request): View
    {
        $devices = Device::with('user')
            ->withCount(['subscriptions as active_subscriptions_count' => function ($query) {
                $query->where('is_active', true);
            }])
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->get('status'));
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('superadmin.devices.index', compact('devices'));
    }

    public function dashboard(): View
    {
        $totalDevices = Device::count();
        $assignedDevices = Device::assigned()->count();
        $unassignedDevices = Device::unassigned()->count();
        $activeSubscriptions = DeviceSubscription::where('is_active', true)->count();
        $expiringSoon = DeviceSubscription::with('device')
            ->where('is_active', true)
            ->whereDate('ends_on', '>=', now())
            ->whereDate('ends_on', '<=', now()->addDays(14))
            ->orderBy('ends_on')
            ->get();

        $recentActivity = Device::with('user')
            ->orderByDesc('last_seen_at')
            ->take(5)
            ->get();

        $statusBreakdown = Device::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('superadmin.devices.dashboard', compact(
            'totalDevices',
            'assignedDevices',
            'unassignedDevices',
            'activeSubscriptions',
            'expiringSoon',
            'recentActivity',
            'statusBreakdown'
        ));
    }

    public function create(): View
    {
        $users = User::where('role', 'user')->orderBy('name')->get();

        return view('superadmin.devices.create', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'device_uid' => ['required', 'string', 'max:255', 'unique:devices,device_uid'],
            'user_id' => ['nullable', 'exists:users,id'],
            'tags' => ['nullable', 'string'],
        ]);

        $device = new Device($validated);
        $device->tags = $this->extractTags($request->input('tags'));
        $device->save();

        $device->refreshStatus();

        return redirect()->route('superadmin.devices.index')
            ->with('success', 'Device registered successfully.');
    }

    public function show(Device $device): View
    {
        $device->load(['user', 'subscriptions' => fn ($query) => $query->latest()]);

        $users = User::where('role', 'user')->orderBy('name')->get();

        return view('superadmin.devices.show', compact('device', 'users'));
    }

    public function edit(Device $device): View
    {
        $users = User::where('role', 'user')->orderBy('name')->get();

        return view('superadmin.devices.edit', compact('device', 'users'));
    }

    public function update(Request $request, Device $device): RedirectResponse
    {
        $validated = $request->validate([
            'device_uid' => ['required', 'string', 'max:255', 'unique:devices,device_uid,' . $device->id],
            'user_id' => ['nullable', 'exists:users,id'],
            'tags' => ['nullable', 'string'],
        ]);

        $device->fill($validated);
        $device->tags = $this->extractTags($request->input('tags'));
        $device->save();

        $device->refreshStatus();

        return redirect()->route('superadmin.devices.show', $device)
            ->with('success', 'Device updated successfully.');
    }

    public function destroy(Device $device): RedirectResponse
    {
        $device->delete();

        return redirect()->route('superadmin.devices.index')
            ->with('success', 'Device removed successfully.');
    }

    protected function extractTags(?string $tags): array
    {
        if (blank($tags)) {
            return [];
        }

        return collect(explode(',', $tags))
            ->map(fn ($tag) => trim($tag))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
