<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Fakturace') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Additional Styles -->
    <style>
        .required:after {
            content: " *";
            color: red;
        }
        
        /* Sidebar styles */
        .sidebar {
            min-height: calc(100vh - 56px);
            background-color: #f8f9fa;
            padding-top: 20px;
            border-right: 1px solid #dee2e6;
        }
        
        @media (max-width: 767.98px) {
            .sidebar {
                position: fixed;
                top: 56px;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 1000;
                padding-top: 20px;
                background-color: #f8f9fa;
                overflow-y: auto;
            }
        }
        
        .sidebar .nav-link {
            font-weight: 500;
            color: #333;
            padding: 0.5rem 1rem;
            margin-bottom: 0.2rem;
        }
        
        .sidebar .nav-link.active {
            color: #007bff;
            background-color: rgba(0, 123, 255, 0.1);
            border-radius: 0.25rem;
        }
        
        .sidebar .nav-link:hover {
            color: #007bff;
        }
        
        .sidebar-heading {
            font-size: .75rem;
            text-transform: uppercase;
        }
        
        /* Submenu styles */
        .submenu {
            display: block;
            padding-left: 1rem;
            margin-bottom: 0.5rem;
        }
        
        .submenu .nav-link {
            padding: 0.3rem 1rem;
            font-size: 0.9rem;
        }
        
        .submenu .nav-item {
            margin-bottom: 0;
        }
        
        .sidebar .nav-item {
            width: 100%;
        }
        
        .sidebar .nav-link {
            cursor: pointer;
        }
        
        /* Main content adjustment */
        @media (min-width: 768px) {
            .ms-sm-auto {
                margin-left: auto !important;
            }
            .px-md-4 {
                padding-right: 1.5rem !important;
                padding-left: 1.5rem !important;
            }
        }
    </style>
</head>
<body>
    <div id="app" class="d-flex flex-column min-vh-100">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <button class="navbar-toggler me-2 d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle sidebar">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Fakturace') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                        @auth
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('dashboard') }}">
                                    <i class="fas fa-tachometer-alt"></i> {{ __('Dashboard') }}
                                </a>
                            </li>
                            
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="customersDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-users"></i> {{ __('Zákazníci') }}
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="customersDropdown">
                                    <li><a class="dropdown-item" href="{{ route('customers.index') }}">{{ __('Seznam zákazníků') }}</a></li>
                                    <li><a class="dropdown-item" href="{{ route('customers.create') }}">{{ __('Přidat zákazníka') }}</a></li>
                                </ul>
                            </li>
                            
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="invoicesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-file-invoice"></i> {{ __('Faktury') }}
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="invoicesDropdown">
                                    <li><a class="dropdown-item" href="{{ route('invoices.index') }}">{{ __('Seznam faktur') }}</a></li>
                                    <li><a class="dropdown-item" href="{{ route('invoices.create') }}">{{ __('Vytvořit fakturu') }}</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ route('invoices.index') }}?status=new">{{ __('Nezaplacené faktury') }}</a></li>
                                    <li><a class="dropdown-item" href="{{ route('invoices.index') }}?status=paid">{{ __('Zaplacené faktury') }}</a></li>
                                </ul>
                            </li>
                        @endauth
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                        {{ __('Profile') }}
                                    </a>
                                    
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container-fluid mt-3">
            <div class="row g-0">
                @auth
                <!-- Sidebar -->
                <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar" id="sidebarMenu">
                    <div class="position-sticky pt-3">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                    <i class="fas fa-tachometer-alt me-2"></i>
                                    {{ __('Dashboard') }}
                                </a>
                            </li>
                            
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}" href="{{ route('customers.index') }}">
                                    <i class="fas fa-users me-2"></i>
                                    {{ __('Zákazníci') }}
                                    <i class="fas fa-chevron-down float-end"></i>
                                </a>
                                <div class="submenu" id="customersSubmenu">
                                    <ul class="nav flex-column ms-3">
                                        <li class="nav-item">
                                            <a class="nav-link {{ request()->routeIs('customers.index') ? 'active' : '' }}" href="{{ route('customers.index') }}">
                                                <i class="fas fa-list me-2"></i> {{ __('Seznam zákazníků') }}
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link {{ request()->routeIs('customers.create') ? 'active' : '' }}" href="{{ route('customers.create') }}">
                                                <i class="fas fa-plus me-2"></i> {{ __('Přidat zákazníka') }}
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}" href="{{ route('invoices.index') }}">
                                    <i class="fas fa-file-invoice me-2"></i>
                                    {{ __('Faktury') }}
                                    <i class="fas fa-chevron-down float-end"></i>
                                </a>
                                <div class="submenu" id="invoicesSubmenu">
                                    <ul class="nav flex-column ms-3">
                                        <li class="nav-item">
                                            <a class="nav-link {{ request()->routeIs('invoices.index') && !request()->has('status') ? 'active' : '' }}" href="{{ route('invoices.index') }}">
                                                <i class="fas fa-list me-2"></i> {{ __('Všechny faktury') }}
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link {{ request()->routeIs('invoices.create') ? 'active' : '' }}" href="{{ route('invoices.create') }}">
                                                <i class="fas fa-plus me-2"></i> {{ __('Vytvořit fakturu') }}
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link {{ request('status') === 'new' ? 'active' : '' }}" href="{{ route('invoices.index', ['status' => 'new']) }}">
                                                <i class="fas fa-clock me-2"></i> {{ __('Nezaplacené') }}
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link {{ request('status') === 'paid' ? 'active' : '' }}" href="{{ route('invoices.index', ['status' => 'paid']) }}">
                                                <i class="fas fa-check-circle me-2"></i> {{ __('Zaplacené') }}
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link {{ request('status') === 'cancelled' ? 'active' : '' }}" href="{{ route('invoices.index', ['status' => 'cancelled']) }}">
                                                <i class="fas fa-ban me-2"></i> {{ __('Stornované') }}
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            
                            <li class="nav-item mt-3">
                                <a class="nav-link" href="{{ route('profile.edit') }}">
                                    <i class="fas fa-user-cog me-2"></i>
                                    {{ __('Nastavení profilu') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <!-- Main content -->
                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                    @yield('content')
                </main>
                @else
                <!-- Main content for guests -->
                <main class="col-12 py-4">
                    @yield('content')
                </main>
                @endauth
            </div>
        </div>
        
        <!-- Footer -->
        <footer class="footer mt-auto py-3 bg-light">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <span class="text-muted">&copy; {{ date('Y') }} Fakturační systém</span>
                    </div>
                    <div class="col-md-6 text-end">
                        <span class="text-muted">Verze 1.0</span>
                    </div>
                </div>
            </div>
        </footer>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Additional Scripts -->
    @stack('scripts')
    
    <!-- Sidebar Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                const sidebar = document.getElementById('sidebarMenu');
                const toggleButton = document.querySelector('[data-bs-target="#sidebarMenu"]');
                
                if (window.innerWidth < 768 &&
                    sidebar &&
                    sidebar.classList.contains('show') &&
                    !sidebar.contains(event.target) &&
                    !toggleButton.contains(event.target)) {
                    
                    const bsCollapse = new bootstrap.Collapse(sidebar);
                    bsCollapse.hide();
                }
            });
            
            // Toggle submenu visibility on parent click
            document.querySelectorAll('.sidebar .nav-item > .nav-link').forEach(link => {
                if (link.nextElementSibling && link.nextElementSibling.classList.contains('submenu')) {
                    link.addEventListener('click', function(e) {
                        const submenu = this.nextElementSibling;
                        const icon = this.querySelector('.fa-chevron-down, .fa-chevron-up');
                        
                        if (icon) {
                            icon.classList.toggle('fa-chevron-up');
                            icon.classList.toggle('fa-chevron-down');
                        }
                        
                        // Toggle submenu visibility with animation
                        if (submenu.style.display === 'none') {
                            submenu.style.display = 'block';
                        } else {
                            submenu.style.display = 'none';
                        }
                    });
                }
            });
            
            // Highlight active menu items
            const currentPath = window.location.pathname;
            document.querySelectorAll('.sidebar .nav-link').forEach(link => {
                if (link.getAttribute('href') === currentPath) {
                    link.classList.add('active');
                    
                    // Highlight parent menu if in submenu
                    const parentItem = link.closest('.submenu');
                    if (parentItem && parentItem.previousElementSibling) {
                        parentItem.previousElementSibling.classList.add('active');
                    }
                }
            });
        });
    </script>
</body>
</html>
