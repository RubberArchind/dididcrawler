@extends('layouts.admin')

@section('title', 'Find Device')
@section('page-title', 'Add Subscription by Device ID')

@push('styles')
<style>
    .device-search-container {
        position: relative;
    }
    
    .device-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #dee2e6;
        border-top: none;
        max-height: 300px;
        overflow-y: auto;
        display: none;
        z-index: 1000;
        border-radius: 0 0 0.375rem 0.375rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    
    .device-dropdown.show {
        display: block;
    }
    
    .device-dropdown-item {
        padding: 0.75rem 1rem;
        cursor: pointer;
        border-bottom: 1px solid #f0f0f0;
        transition: background-color 0.15s ease-in-out;
    }
    
    .device-dropdown-item:last-child {
        border-bottom: none;
    }
    
    .device-dropdown-item:hover,
    .device-dropdown-item.active {
        background-color: #f8f9fa;
    }
    
    .device-dropdown-item-uid {
        font-weight: 600;
        color: #2c3e50;
    }
    
    .device-dropdown-item-name {
        font-size: 0.875rem;
        color: #6c757d;
        display: block;
    }
    
    .device-dropdown-empty {
        padding: 1rem;
        text-align: center;
        color: #6c757d;
    }
    
    .form-control:focus ~ .device-dropdown {
        display: block;
    }
</style>
@endpush

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Locate Device</h5>
            <a href="{{ route('superadmin.devices.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to devices
            </a>
        </div>
        <form method="POST" action="{{ route('superadmin.devices.subscriptions.lookup.submit') }}">
            @csrf
            <div class="card-body">
                <div class="mb-3">
                    <label for="device_uid" class="form-label">Device ID / Name</label>
                    <div class="device-search-container">
                        <input 
                            type="text" 
                            name="device_uid" 
                            id="device_uid" 
                            class="form-control" 
                            value="{{ old('device_uid') }}" 
                            placeholder="Search by Device ID or Name" 
                            autocomplete="off"
                            required>
                        <div class="device-dropdown" id="deviceDropdown"></div>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="bi bi-info-circle me-1"></i>
                        Type to search existing devices, then select one to proceed with adding a subscription.
                    </small>
                </div>
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search me-1"></i>
                    Find Device
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    const deviceInput = document.getElementById('device_uid');
    const deviceDropdown = document.getElementById('deviceDropdown');
    let highlightedIndex = -1;

    // Attach listeners to dropdown items
    function attachDropdownListeners() {
        const items = document.querySelectorAll('.device-dropdown-item');
        items.forEach(item => {
            item.addEventListener('click', (e) => {
                deviceInput.value = item.getAttribute('data-device-id');
                deviceDropdown.classList.remove('show');
            });
            
            item.addEventListener('mouseenter', () => {
                document.querySelectorAll('.device-dropdown-item').forEach(i => i.classList.remove('active'));
                item.classList.add('active');
                highlightedIndex = parseInt(item.getAttribute('data-index'));
            });
        });
    }

    // Search on input
    deviceInput.addEventListener('input', (e) => {
        const query = e.target.value.trim();
        if (query.length > 0) {
            loadDevices(query);
        } else {
            deviceDropdown.innerHTML = '';
            deviceDropdown.classList.remove('show');
        }
    });

    // Load initial devices
    async function loadDevices(query = '') {
        try {
            const response = await fetch(`{{ route('superadmin.devices.search') }}?q=${encodeURIComponent(query)}`);
            const devices = await response.json();
            
            if (devices.length === 0) {
                deviceDropdown.innerHTML = '<div class="device-dropdown-empty">No devices found</div>';
            } else {
                deviceDropdown.innerHTML = devices.map((device, index) => {
                    const tagDisplay = device.tags && device.tags.length > 0 
                        ? `<span class="device-dropdown-item-name">${device.tags.join(', ')}</span>`
                        : '';
                    return `
                        <div class="device-dropdown-item" data-device-id="${device.device_uid}" data-index="${index}">
                            <div class="device-dropdown-item-uid">${device.device_uid}</div>
                            ${tagDisplay}
                        </div>
                    `;
                }).join('');
                
                attachDropdownListeners();
            }
            
            deviceDropdown.classList.add('show');
            highlightedIndex = -1;
        } catch (error) {
            console.error('Error loading devices:', error);
        }
    }

    // Keyboard navigation
    deviceInput.addEventListener('keydown', (e) => {
        const items = document.querySelectorAll('.device-dropdown-item');
        
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            highlightedIndex = Math.min(highlightedIndex + 1, items.length - 1);
            items[highlightedIndex]?.classList.add('active');
            items[highlightedIndex]?.scrollIntoView({ block: 'nearest' });
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            highlightedIndex = Math.max(highlightedIndex - 1, -1);
            if (highlightedIndex >= 0) {
                items[highlightedIndex]?.classList.add('active');
                items[highlightedIndex]?.scrollIntoView({ block: 'nearest' });
            }
            document.querySelectorAll('.device-dropdown-item').forEach(i => i.classList.remove('active'));
            items[highlightedIndex]?.classList.add('active');
        } else if (e.key === 'Enter' && highlightedIndex >= 0) {
            e.preventDefault();
            const selectedItem = items[highlightedIndex];
            if (selectedItem) {
                deviceInput.value = selectedItem.getAttribute('data-device-id');
                deviceDropdown.classList.remove('show');
            }
        } else if (e.key === 'Escape') {
            deviceDropdown.classList.remove('show');
        }
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.device-search-container')) {
            deviceDropdown.classList.remove('show');
        }
    });

    // Open dropdown when focused and empty
    deviceInput.addEventListener('focus', () => {
        if (deviceInput.value === '' && deviceDropdown.querySelector('.device-dropdown-item')) {
            deviceDropdown.classList.add('show');
        }
    });

    // Load initial devices on page load
    window.addEventListener('load', () => {
        loadDevices();
    });
</script>
@endpush
