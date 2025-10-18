<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Menu Navigasi Utama -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Tautan Navigasi -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dasbor') }}
                    </x-nav-link>
                    @if (Auth::user() && Auth::user()->role === 'user')
                        <x-nav-link :href="route('user.devices')" :active="request()->routeIs('user.devices')">
                            {{ __('Perangkat') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Dropdown Pengaturan -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profil') }}
                        </x-dropdown-link>

                        <!-- Autentikasi -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Keluar') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Ruang kosong untuk mobile (ikon berada di bilah bawah tetap) -->
            <div class="-me-2 flex items-center sm:hidden"></div>
        </div>
    </div>

    <!-- Navigasi Bawah Tetap untuk Mobile -->
    <div class="sm:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-50">
        <div class="grid grid-cols-4 text-xs">
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center py-2 {{ request()->routeIs('dashboard') ? 'text-indigo-600' : 'text-gray-600' }}">
                <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6"/></svg>
                <span>Beranda</span>
            </a>
            @if (Auth::user() && Auth::user()->role === 'user')
                <a href="{{ route('user.devices') }}" class="flex flex-col items-center justify-center py-2 {{ request()->routeIs('user.devices') ? 'text-indigo-600' : 'text-gray-600' }}">
                    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Perangkat</span>
                </a>
            @endif
            <a href="{{ route('profile.edit') }}" class="flex flex-col items-center justify-center py-2 {{ request()->routeIs('profile.*') ? 'text-indigo-600' : 'text-gray-600' }}">
                <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Profil</span>
            </a>
            <form method="POST" action="{{ route('logout') }}" class="flex flex-col items-center justify-center py-2">
                @csrf
                <button type="submit" class="flex flex-col items-center justify-center {{ request()->routeIs('logout') ? 'text-indigo-600' : 'text-gray-600' }}">
                    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2h4a2 2 0 012 2v1"/></svg>
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </div>
</nav>
