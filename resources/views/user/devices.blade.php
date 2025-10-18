@extends('layouts.admin')

@section('title', 'Perangkat Saya')
@section('page-title', 'Perangkat Saya')

@section('content')
<style>
    .device-card {
        border: none;
        border-radius: 0.75rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        margin-bottom: 1.5rem;
        overflow: hidden;
    }

    .device-card:hover {
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        transform: translateY(-2px);
    }

    .device-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .device-id {
        font-size: 1.5rem;
        font-weight: 700;
        letter-spacing: -0.5px;
    }

    .badge-status {
        padding: 0.5rem 1rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        margin-top: 0.5rem;
        display: inline-block;
    }

    .badge-status.active {
        background: rgba(16, 185, 129, 0.2);
        color: #10b981;
    }

    .badge-status.idle {
        background: rgba(251, 146, 60, 0.2);
        color: #fb923c;
    }

    .badge-status.inactive {
        background: rgba(239, 68, 68, 0.2);
        color: #ef4444;
    }

    .device-body {
        padding: 1.5rem;
    }

    .info-row {
        display: flex;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #f0f0f0;
    }

    .info-row:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .info-label {
        font-size: 0.75rem;
        font-weight: 700;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        width: 80px;
        flex-shrink: 0;
    }

    .info-content {
        flex-grow: 1;
        color: #1f2937;
    }

    .tags-container {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .tag {
        display: inline-block;
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        color: white;
        padding: 0.375rem 0.875rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .subscription-list {
        margin-top: 0;
    }

    .subscription-item {
        background: linear-gradient(135deg, #f5f7fa 0%, #fff 100%);
        border-left: 4px solid #667eea;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 0.75rem;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .subscription-item.active {
        border-left-color: #10b981;
    }

    .subscription-item.inactive {
        border-left-color: #d1d5db;
        opacity: 0.7;
    }

    .subscription-name {
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.25rem;
    }

    .subscription-plan {
        font-size: 0.875rem;
        color: #6b7280;
        margin-bottom: 0.5rem;
    }

    .subscription-dates {
        font-size: 0.75rem;
        color: #9ca3af;
        line-height: 1.5;
    }

    .subscription-badge {
        padding: 0.375rem 0.75rem;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .subscription-badge.active {
        background: #dbeafe;
        color: #0369a1;
    }

    .subscription-badge.inactive {
        background: #e5e7eb;
        color: #6b7280;
    }

    .empty-state {
        text-align: center;
        padding: 3rem 2rem;
        background: linear-gradient(135deg, #f5f7fa 0%, #fff 100%);
        border-radius: 0.75rem;
        border: 2px dashed #e5e7eb;
    }

    .empty-state-icon {
        font-size: 3rem;
        color: #d1d5db;
        margin-bottom: 1rem;
    }

    .empty-state-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
    }

    .empty-state-text {
        color: #9ca3af;
        font-size: 0.95rem;
    }

    .section-title {
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.75rem;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .no-subscriptions {
        text-align: center;
        padding: 1rem;
        background: #f9fafb;
        border-radius: 0.5rem;
        color: #6b7280;
        font-size: 0.875rem;
    }

    .devices-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 1.5rem;
    }
</style>

<div class="mb-4">
    <p class="text-muted">Anda memiliki <strong>{{ $devices->count() }}</strong> perangkat yang terdaftar</p>
</div>

@forelse($devices as $device)
    <div class="device-card">
        <!-- Header -->
        <div class="device-header">
            <div>
                <div class="device-id">{{ $device->device_uid }}</div>
                <div class="badge-status {{ $device->status }}">
                    @if($device->status === 'active')
                        ● Aktif
                    @elseif($device->status === 'idle')
                        ● Menganggur
                    @else
                        ● Tidak Aktif
                    @endif
                </div>
            </div>
        </div>

        <!-- Body -->
        <div class="device-body">
            <!-- Tags -->
            @if($device->tags && count($device->tags) > 0)
                <div class="info-row">
                    <div class="info-label">Tag</div>
                    <div class="info-content">
                        <div class="tags-container">
                            @foreach($device->tags as $tag)
                                <span class="tag">{{ $tag }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Last Seen -->
            @if($device->last_seen_at)
                <div class="info-row">
                    <div class="info-label">Terakhir</div>
                    <div class="info-content">
                        @tz($device->last_seen_at, 'd M Y, H:i:s') WIB
                    </div>
                </div>
            @endif

            <!-- Subscriptions -->
            <div class="mt-3">
                <div class="section-title">Langganan Aktif</div>
                @if($device->subscriptions->where('is_active', true)->count() > 0)
                    <div class="subscription-list">
                        @foreach($device->subscriptions->where('is_active', true) as $subscription)
                            <div class="subscription-item active">
                                <div>
                                    <div class="subscription-name">{{ $subscription->subscription_name }}</div>
                                    @if($subscription->plan)
                                        <div class="subscription-plan">Paket: {{ $subscription->plan }}</div>
                                    @endif
                                    <div class="subscription-dates">
                                        @if($subscription->starts_on)
                                            📅 Mulai: {{ $subscription->starts_on->format('d M Y') }}<br>
                                        @endif
                                        @if($subscription->ends_on)
                                            Berakhir: {{ $subscription->ends_on->format('d M Y') }}
                                        @else
                                            Berakhir: Tidak Terbatas
                                        @endif
                                    </div>
                                </div>
                                <span class="subscription-badge active">Aktif</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="no-subscriptions">
                        Tidak ada langganan aktif
                    </div>
                @endif

                @if($device->subscriptions->where('is_active', false)->count() > 0)
                    <div class="section-title mt-3 pt-2">Langganan Tidak Aktif</div>
                    <div class="subscription-list">
                        @foreach($device->subscriptions->where('is_active', false) as $subscription)
                            <div class="subscription-item inactive">
                                <div>
                                    <div class="subscription-name">{{ $subscription->subscription_name }}</div>
                                    @if($subscription->plan)
                                        <div class="subscription-plan">Paket: {{ $subscription->plan }}</div>
                                    @endif
                                    <div class="subscription-dates">
                                        @if($subscription->starts_on)
                                            Mulai: {{ $subscription->starts_on->format('d M Y') }}<br>
                                        @endif
                                        @if($subscription->ends_on)
                                            Berakhir: {{ $subscription->ends_on->format('d M Y') }}
                                        @endif
                                    </div>
                                </div>
                                <span class="subscription-badge inactive">Tidak Aktif</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
@empty
    <!-- Empty State -->
    <div class="empty-state">
        <div class="empty-state-icon">
            <i class="bi bi-hdd-network"></i>
        </div>
        <div class="empty-state-title">Belum Ada Perangkat</div>
        <p class="empty-state-text">Anda belum memiliki perangkat yang terdaftar.<br>Hubungi administrator untuk mendaftarkan perangkat Anda.</p>
    </div>
@endforelse

@endsection
