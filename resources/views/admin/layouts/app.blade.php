<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ config('app.name') }} Admin</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/css/admin.css'])
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="antialiased bg-slate-100" x-data="{ sidebarOpen: false }">
    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-950 transform transition-transform duration-300 lg:translate-x-0 lg:static lg:inset-auto"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            <div class="flex flex-col h-full">
                {{-- Logo --}}
                <div class="flex items-center gap-3 px-6 py-6 border-b border-slate-800">
                    <div class="w-9 h-9 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-600/40">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-white font-bold text-sm leading-tight">{{ config('app.name') }}</p>
                        <p class="text-slate-500 text-xs">Admin Panel</p>
                    </div>
                </div>

                {{-- Navigation --}}
                <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto" x-data="{ mastersOpen: {{ request()->routeIs('admin.masters.*') ? 'true' : 'false' }}, usersOpen: {{ request()->routeIs('admin.users.*') ? 'true' : 'false' }} }">
                    <p class="text-xs font-semibold text-slate-600 uppercase tracking-wider px-4 mb-2">Main</p>

                    <a href="{{ route('admin.dashboard') }}" class="admin-sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        Dashboard
                    </a>

                    <a href="{{ route('admin.products.index') }}" class="admin-sidebar-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        Products
                    </a>

                    <a href="{{ route('admin.banners.index') }}" class="admin-sidebar-link {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Banners
                    </a>

                    <a href="{{ route('admin.home-reels.index') }}" class="admin-sidebar-link {{ request()->routeIs('admin.home-reels.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        Home Reels
                    </a>

                    <a href="{{ route('admin.coupons.index') }}" class="admin-sidebar-link {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
                        Coupons
                    </a>

                    <a href="{{ route('admin.announcements.index') }}" class="admin-sidebar-link {{ request()->routeIs('admin.announcements.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                        Announcements
                    </a>

                    <a href="{{ route('admin.customers.index') }}" class="admin-sidebar-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Customers
                    </a>

                    <p class="text-xs font-semibold text-slate-600 uppercase tracking-wider px-4 mb-2 mt-5">Phase 1 — Masters</p>

                    <button @click="mastersOpen = !mastersOpen" class="admin-sidebar-link w-full justify-between {{ request()->routeIs('admin.masters.*') ? 'text-white' : '' }}">
                        <span class="flex items-center gap-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                            Master Data
                        </span>
                        <svg class="w-4 h-4 transition-transform" :class="mastersOpen && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="mastersOpen" x-cloak class="ml-4 space-y-0.5 border-l border-slate-800 pl-3">
                        @foreach([
                            ['route' => 'admin.masters.categories.index', 'label' => 'Categories'],
                            ['route' => 'admin.masters.brands.index', 'label' => 'Brands'],
                            ['route' => 'admin.masters.manufacturers.index', 'label' => 'Manufacturers'],
                            ['route' => 'admin.masters.suppliers.index', 'label' => 'Suppliers'],
                            ['route' => 'admin.masters.taxes.index', 'label' => 'Tax / GST'],
                            ['route' => 'admin.masters.units.index', 'label' => 'Units'],
                            ['route' => 'admin.masters.sizes.index', 'label' => 'Sizes'],
                            ['route' => 'admin.masters.colors.index', 'label' => 'Colors'],
                            ['route' => 'admin.masters.materials.index', 'label' => 'Materials'],
                            ['route' => 'admin.masters.vehicle_brands.index', 'label' => 'Vehicle Brands'],
                            ['route' => 'admin.masters.bike_models.index', 'label' => 'Bike Models'],
                        ] as $item)
                            <a href="{{ route($item['route']) }}" class="block px-3 py-2 text-sm rounded-lg {{ request()->routeIs($item['route']) ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">{{ $item['label'] }}</a>
                        @endforeach
                    </div>

                    <p class="text-xs font-semibold text-slate-600 uppercase tracking-wider px-4 mb-2 mt-5">User Management</p>

                    <button @click="usersOpen = !usersOpen" class="admin-sidebar-link w-full justify-between {{ request()->routeIs('admin.users.*') ? 'text-white' : '' }}">
                        <span class="flex items-center gap-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            Users & Roles
                        </span>
                        <svg class="w-4 h-4 transition-transform" :class="usersOpen && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="usersOpen" x-cloak class="ml-4 space-y-0.5 border-l border-slate-800 pl-3">
                        @foreach([
                            ['route' => 'admin.users.admin-users.index', 'label' => 'Admin Users'],
                            ['route' => 'admin.users.roles.index', 'label' => 'Roles'],
                            ['route' => 'admin.users.permissions.index', 'label' => 'Permissions'],
                            ['route' => 'admin.users.login-history.index', 'label' => 'Login History'],
                            ['route' => 'admin.users.activity-logs.index', 'label' => 'Activity Logs'],
                        ] as $item)
                            <a href="{{ route($item['route']) }}" class="block px-3 py-2 text-sm rounded-lg {{ request()->routeIs($item['route']) ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">{{ $item['label'] }}</a>
                        @endforeach
                    </div>

                    <a href="{{ route('admin.orders.index') }}" class="admin-sidebar-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        Orders
                    </a>

                    <a href="{{ route('admin.reports.index') }}" class="admin-sidebar-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Reports
                    </a>

                    <p class="text-xs font-semibold text-slate-600 uppercase tracking-wider px-4 mb-2 mt-5">System</p>

                    <a href="{{ route('admin.settings.payments') }}" class="admin-sidebar-link {{ request()->routeIs('admin.settings.payments') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        Payment Settings
                    </a>

                    <a href="{{ route('admin.settings.homepage') }}" class="admin-sidebar-link {{ request()->routeIs('admin.settings.homepage') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Homepage Settings
                    </a>

                    <a href="{{ route('admin.settings.tax') }}" class="admin-sidebar-link {{ request()->routeIs('admin.settings.tax') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                        Tax / GST Settings
                    </a>

                    <a href="{{ route('home') }}" target="_blank" class="admin-sidebar-link">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        View Store
                    </a>
                </nav>

                {{-- User --}}
                <div class="p-4 border-t border-slate-800">
                    <div class="flex items-center gap-3 px-2 py-2">
                        <div class="w-9 h-9 bg-indigo-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-white text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                            <p class="text-slate-500 text-xs truncate">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.logout') }}" class="mt-2">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-slate-400 hover:text-red-400 hover:bg-slate-800 rounded-xl transition-all duration-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Mobile overlay --}}
        <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-black/50 z-40 lg:hidden" x-cloak></div>

        {{-- Main Content --}}
        <div class="flex-1 flex flex-col min-w-0">
            {{-- Top bar --}}
            <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
                <div class="flex items-center justify-between px-6 py-4">
                    <div class="flex items-center gap-4">
                        <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                        <div>
                            <h1 class="text-xl font-bold text-slate-900">@yield('page-title', 'Dashboard')</h1>
                            <p class="text-sm text-slate-500 hidden sm:block">@yield('page-subtitle', 'Overview of your store performance')</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="hidden sm:inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-full">
                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                            Store Online
                        </span>
                        <div class="text-right hidden sm:block">
                            <p class="text-sm font-medium text-slate-700">{{ now()->format('l, M d, Y') }}</p>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Page content --}}
            <main class="flex-1 p-6 overflow-auto">
                @if(session('success'))
                    <div class="mb-6 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm">
                        <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        {{ session('success') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @vite(['resources/js/app.js', 'resources/js/admin-master.js'])
    @stack('vite')
    @stack('scripts')
</body>
</html>
