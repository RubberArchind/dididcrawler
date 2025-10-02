<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'DididCrawler')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    @stack('styles')
    
    <!-- Custom CSS -->
    <style>
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background-color: #f5f5f7;
            color: #333;
            overflow-x: hidden;
        }
        
        /* Sidebar styling */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 250px;
            background: #2c3037;
            color: #fff;
            z-index: 1030;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            transition: all 0.3s;
            padding: 0;
            overflow-y: auto;
        }
        
        .sidebar-brand {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-brand h5 {
            color: #fff;
            font-weight: 600;
            margin: 0;
        }
        
        .sidebar .nav {
            padding: 1rem 0;
        }
        
        .sidebar .nav-item {
            width: 100%;
            padding: 0 1rem;
            margin-bottom: 0.5rem;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.7);
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            transition: all 0.2s;
            width: 100%;
        }
        
        .sidebar .nav-link:hover {
            color: #fff;
            background: rgba(255,255,255,0.1);
        }
        
        .sidebar .nav-link.active {
            color: #fff;
            background: rgba(255,255,255,0.15);
            font-weight: 500;
        }
        
        .sidebar .nav-link i {
            width: 24px;
            text-align: center;
            margin-right: 8px;
        }
        
        .user-info {
            padding: 1rem 1.5rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            margin-top: auto;
        }
        
        /* Main content styling */
        .main-content {
            margin-left: 250px;
            min-height: 100vh;
            padding: 2rem 2rem 4rem;
            transition: all 0.3s;
        }
        
        .page-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        /* Card styling */
        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 1rem 1.5rem;
            font-weight: 500;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .card-footer {
            background-color: #fff;
            border-top: 1px solid rgba(0,0,0,0.05);
            padding: 1rem 1.5rem;
        }
        
        /* Table styling */
        .table {
            margin-bottom: 0;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(0,0,0,0.02);
        }
        
        .table th {
            font-weight: 600;
            background-color: #f8f9fa;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.03em;
            color: #6c757d;
            padding: 0.75rem 1rem;
            border-top: none;
            vertical-align: middle;
        }
        
        .table td {
            padding: 1rem;
            vertical-align: middle;
            border-color: rgba(0,0,0,0.05);
        }
        
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        /* Badge styling */
        .badge {
            font-weight: 500;
            padding: 0.5em 0.75em;
        }
        
        /* Button styling */
        .btn {
            border-radius: 0.25rem;
            padding: 0.5rem 1rem;
            font-weight: 500;
        }
        
        .btn-sm {
            padding: 0.25rem 0.75rem;
            font-size: 0.875rem;
        }
        
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }
        
        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }
        
        /* Modal styling */
        .modal-content {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .modal-header {
            border-bottom: 1px solid rgba(0,0,0,0.05);
            background-color: #f8f9fa;
            padding: 1rem 1.5rem;
        }
        
        .modal-body {
            padding: 1.5rem;
        }
        
        .modal-footer {
            border-top: 1px solid rgba(0,0,0,0.05);
            background-color: #f8f9fa;
            padding: 1rem 1.5rem;
        }
        
        /* Form styling */
        .form-control {
            border-radius: 0.25rem;
            border: 1px solid rgba(0,0,0,0.1);
            padding: 0.5rem 0.75rem;
        }
        
        .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        /* Alert styling */
        .alert {
            border-radius: 0.25rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border: 1px solid transparent;
        }
        
        /* Mobile responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease-in-out;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                padding: 1rem 1rem 5rem;
            }
            
            .content {
                padding-bottom: 5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <h5>
                @if(auth()->user()->isSuperAdmin())
                    SuperAdmin Panel
                @else
                    User Dashboard
                @endif
            </h5>
        </div>
        
        <ul class="nav flex-column">
            @if(auth()->user()->isSuperAdmin())
                <li class="nav-item">
                    <a href="{{ route('superadmin.dashboard') }}" 
                       class="nav-link {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('superadmin.users') }}" 
                       class="nav-link {{ request()->routeIs('superadmin.users*') ? 'active' : '' }}">
                        <i class="bi bi-people"></i>
                        User Management
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('superadmin.reports') }}" 
                       class="nav-link {{ request()->routeIs('superadmin.reports') ? 'active' : '' }}">
                        <i class="bi bi-graph-up"></i>
                        Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('superadmin.payments') }}" 
                       class="nav-link {{ request()->routeIs('superadmin.payments') ? 'active' : '' }}">
                        <i class="bi bi-credit-card"></i>
                        Payments
                    </a>
                </li>
                @php
                    $deviceNavActive = request()->routeIs('superadmin.devices.*') && !request()->routeIs('superadmin.devices.subscriptions.*');
                @endphp
                <li class="nav-item">
                    <a href="{{ route('superadmin.devices.index') }}" 
                       class="nav-link {{ $deviceNavActive ? 'active' : '' }}">
                        <i class="bi bi-hdd-network"></i>
                        Devices
                    </a>
                </li>
                <li class="nav-item ps-4">
                    <a href="{{ route('superadmin.devices.subscriptions.lookup') }}" 
                       class="nav-link {{ request()->routeIs('superadmin.devices.subscriptions.*') ? 'active' : '' }}">
                        <i class="bi bi-link-45deg"></i>
                        Add Subscription
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('superadmin.settings') }}" 
                       class="nav-link {{ request()->routeIs('superadmin.settings') ? 'active' : '' }}">
                        <i class="bi bi-gear"></i>
                        Settings
                    </a>
                </li>
            @else
                <li class="nav-item">
                    <a href="{{ route('user.dashboard') }}" 
                       class="nav-link {{ request()->routeIs('user.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('user.reports') }}" 
                       class="nav-link {{ request()->routeIs('user.reports') ? 'active' : '' }}">
                        <i class="bi bi-graph-up"></i>
                        Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('user.payments') }}" 
                       class="nav-link {{ request()->routeIs('user.payments') ? 'active' : '' }}">
                        <i class="bi bi-credit-card"></i>
                        Payment Status
                    </a>
                </li>
            @endif
        </ul>
        
        <div class="user-info mt-auto">
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle"
                   data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle me-2"></i>
                    <span>{{ auth()->user()->name }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark">
                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">Sign out</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Page header -->
        <div class="page-header d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-bold">@yield('page-title', 'Dashboard')</h2>
            <div class="text-muted">
                {{ now()->format('l, d F Y') }}
            </div>
        </div>

        <!-- Alerts -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Content -->
        @yield('content')
    </div>

    <!-- Mobile Bottom Navigation -->
    @php
        $isSuper = auth()->check() && auth()->user()->isSuperAdmin();
    @endphp
    
    <nav class="d-md-none navbar navbar-light bg-white border-top fixed-bottom">
        <div class="container-fluid px-0">
            <div class="d-flex justify-content-around w-100 text-center">
                @if($isSuper)
                    <a href="{{ route('superadmin.dashboard') }}" class="text-decoration-none {{ request()->routeIs('superadmin.dashboard') ? 'text-primary' : 'text-muted' }}">
                        <i class="bi bi-speedometer2 fs-5 d-block"></i>
                        <small>Dashboard</small>
                    </a>
                    <a href="{{ route('superadmin.reports') }}" class="text-decoration-none {{ request()->routeIs('superadmin.reports') ? 'text-primary' : 'text-muted' }}">
                        <i class="bi bi-graph-up fs-5 d-block"></i>
                        <small>Reports</small>
                    </a>
                    <a href="{{ route('superadmin.payments') }}" class="text-decoration-none {{ request()->routeIs('superadmin.payments') ? 'text-primary' : 'text-muted' }}">
                        <i class="bi bi-credit-card fs-5 d-block"></i>
                        <small>Payments</small>
                    </a>
                    <a href="{{ route('superadmin.devices.index') }}" class="text-decoration-none {{ request()->routeIs('superadmin.devices.*') ? 'text-primary' : 'text-muted' }}">
                        <i class="bi bi-hdd-network fs-5 d-block"></i>
                        <small>Devices</small>
                    </a>
                    <a href="{{ route('superadmin.settings') }}" class="text-decoration-none {{ request()->routeIs('superadmin.settings') ? 'text-primary' : 'text-muted' }}">
                        <i class="bi bi-gear fs-5 d-block"></i>
                        <small>Settings</small>
                    </a>
                @else
                    <a href="{{ route('user.dashboard') }}" class="text-decoration-none {{ request()->routeIs('user.dashboard') ? 'text-primary' : 'text-muted' }}">
                        <i class="bi bi-speedometer2 fs-5 d-block"></i>
                        <small>Dashboard</small>
                    </a>
                    <a href="{{ route('user.reports') }}" class="text-decoration-none {{ request()->routeIs('user.reports') ? 'text-primary' : 'text-muted' }}">
                        <i class="bi bi-graph-up fs-5 d-block"></i>
                        <small>Reports</small>
                    </a>
                    <a href="{{ route('user.payments') }}" class="text-decoration-none {{ request()->routeIs('user.payments') ? 'text-primary' : 'text-muted' }}">
                        <i class="bi bi-credit-card fs-5 d-block"></i>
                        <small>Payments</small>
                    </a>
                    <a href="{{ route('profile.edit') }}" class="text-decoration-none {{ request()->routeIs('profile.*') ? 'text-primary' : 'text-muted' }}">
                        <i class="bi bi-person fs-5 d-block"></i>
                        <small>Profile</small>
                    </a>
                @endif
            </div>
        </div>
    </nav>
    
    @stack('modals')

    <!-- Core JS -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js"></script>
    
    <!-- Mobile sidebar toggle -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Bootstrap components
            var tooltipList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                .map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
                
            var popoverList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
                .map(function (popoverTriggerEl) {
                    return new bootstrap.Popover(popoverTriggerEl);
                });
                
            // Ensure modal buttons work
            var modalButtons = document.querySelectorAll('[data-bs-toggle="modal"]');
            modalButtons.forEach(function(button) {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    var target = document.querySelector(button.getAttribute('data-bs-target'));
                    if (target) {
                        var modal = new bootstrap.Modal(target);
                        modal.show();
                    }
                });
            });
            
            // Mobile sidebar functionality
            function toggleSidebar() {
                var sidebar = document.getElementById('sidebar');
                if (sidebar) {
                    sidebar.classList.toggle('show');
                }
            }
            
            var mobileToggle = document.createElement('button');
            mobileToggle.className = 'd-md-none btn btn-primary position-fixed';
            mobileToggle.style.bottom = '80px';
            mobileToggle.style.right = '20px';
            mobileToggle.style.zIndex = '1040';
            mobileToggle.style.width = '50px';
            mobileToggle.style.height = '50px';
            mobileToggle.style.borderRadius = '50%';
            mobileToggle.innerHTML = '<i class="bi bi-list"></i>';
            mobileToggle.addEventListener('click', toggleSidebar);
            document.body.appendChild(mobileToggle);
        });
    </script>
    
    @stack('scripts')
</body>
</html>