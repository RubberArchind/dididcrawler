<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'DididCrawler')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        body {
            overflow-x: hidden;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            overflow-y: auto;
            background: #343a40;
            z-index: 1000;
            padding: 1.5rem 1rem;
            width: 250px;
            transition: transform 0.3s ease-in-out;
        }
        
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar::-webkit-scrollbar-track {
            background: #2c3136;
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: #495057;
            border-radius: 3px;
        }
        
        .sidebar::-webkit-scrollbar-thumb:hover {
            background: #6c757d;
        }
        
        .sidebar .nav-link {
            color: #adb5bd;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 0.25rem;
            transition: all 0.2s ease;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }
        
        .sidebar .nav-link i {
            width: 20px;
        }
        
        .main-content {
            margin-left: 250px;
            background: #f8f9fa;
            min-height: 100vh;
            transition: margin-left 0.3s ease-in-out;
        }
        
        .content {
            padding: 2rem;
        }
        
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: none;
        }
        
        .sidebar-brand {
            padding: 1rem 0;
            margin-bottom: 1rem;
            border-bottom: 1px solid #495057;
        }
        
        .sidebar-brand h5 {
            color: #fff;
            font-weight: 600;
            margin: 0;
        }
        
        .user-info {
            margin-top: auto;
            padding-top: 1rem;
            border-top: 1px solid #495057;
        }
        
        .user-info .dropdown-toggle::after {
            margin-left: auto;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                width: 280px;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .mobile-toggle {
                display: block;
            }
        }
        
        @media (min-width: 769px) {
            .mobile-toggle {
                display: none;
            }
        }
        
        .mobile-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }
        
        @media (max-width: 768px) {
            .mobile-overlay.show {
                display: block;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile Toggle Button -->
    <button class="btn btn-primary mobile-toggle position-fixed" 
            style="top: 1rem; left: 1rem; z-index: 1001;"
            onclick="toggleSidebar()">
        <i class="bi bi-list"></i>
    </button>
    
    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobileOverlay" onclick="toggleSidebar()"></div>
    
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
        
        <ul class="nav nav-pills flex-column">
            @if(auth()->user()->isSuperAdmin())
                <li class="nav-item">
                    <a href="{{ route('superadmin.dashboard') }}" 
                       class="nav-link {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2 me-2"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('superadmin.users') }}" 
                       class="nav-link {{ request()->routeIs('superadmin.users*') ? 'active' : '' }}">
                        <i class="bi bi-people me-2"></i>
                        User Management
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('superadmin.reports') }}" 
                       class="nav-link {{ request()->routeIs('superadmin.reports') ? 'active' : '' }}">
                        <i class="bi bi-graph-up me-2"></i>
                        Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('superadmin.payments') }}" 
                       class="nav-link {{ request()->routeIs('superadmin.payments') ? 'active' : '' }}">
                        <i class="bi bi-credit-card me-2"></i>
                        Payments
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('superadmin.settings') }}" 
                       class="nav-link {{ request()->routeIs('superadmin.settings') ? 'active' : '' }}">
                        <i class="bi bi-gear me-2"></i>
                        Settings
                    </a>
                </li>
            @else
                <li class="nav-item">
                    <a href="{{ route('user.dashboard') }}" 
                       class="nav-link {{ request()->routeIs('user.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2 me-2"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('user.reports') }}" 
                       class="nav-link {{ request()->routeIs('user.reports') ? 'active' : '' }}">
                        <i class="bi bi-graph-up me-2"></i>
                        Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('user.payments') }}" 
                       class="nav-link {{ request()->routeIs('user.payments') ? 'active' : '' }}">
                        <i class="bi bi-credit-card me-2"></i>
                        Payment Status
                    </a>
                </li>
            @endif
        </ul>
        
        <div class="user-info">
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle"
                   data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle me-2"></i>
                    <span>{{ auth()->user()->name }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
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
        <div class="content">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>@yield('page-title', 'Dashboard')</h2>
                <div class="text-muted">
                    {{ now()->format('l, d F Y') }}
                </div>
            </div>

            <!-- Alerts -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Content -->
            @yield('content')
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Sidebar Toggle Script -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobileOverlay');
            
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        }
        
        // Close sidebar when clicking on main content on mobile
        document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.querySelector('.main-content');
            
            if (window.innerWidth <= 768) {
                mainContent.addEventListener('click', function() {
                    const sidebar = document.getElementById('sidebar');
                    const overlay = document.getElementById('mobileOverlay');
                    
                    if (sidebar.classList.contains('show')) {
                        sidebar.classList.remove('show');
                        overlay.classList.remove('show');
                    }
                });
            }
        });
        
        // Handle window resize
        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobileOverlay');
            
            if (window.innerWidth > 768) {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>