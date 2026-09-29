<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="jwt-token" content="{{ session('jwt_token') }}">
    <title>{{ $title ?? 'Monitoring Penugasan Layanan' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }

        body {
            background: #f4f6fa;
            color: #0f172a;
        }

        .app-shell {
            display: grid;
            grid-template-columns: 0 minmax(0, 1fr);
            min-height: 100vh;
        }

        .app-shell.is-sidebar-open {
            grid-template-columns: 230px minmax(0, 1fr);
        }

        .app-sidebar {
            grid-column: 1;
            position: sticky;
            top: 0;
            width: 230px;
            height: 100vh;
            overflow-x: hidden;
            overflow-y: auto;
            background: #0f172a;
            color: #f1f5f9;
        }

        .app-shell:not(.is-sidebar-open) .app-sidebar {
            visibility: hidden;
            pointer-events: none;
        }

        .app-overlay { display: none; }

        .app-body {
            grid-column: 2;
            min-width: 0;
        }

        .app-header {
            position: sticky;
            top: 0;
            z-index: 20;
            border-bottom: 1px solid #e2e8f0;
            background: #fff;
        }

        @media (max-width: 767px) {
            .app-shell,
            .app-shell.is-sidebar-open {
                display: block;
            }

            .app-sidebar {
                position: fixed;
                top: 0;
                bottom: 0;
                left: 0;
                z-index: 50;
                width: min(230px, 86vw);
                transform: translateX(-100%);
                visibility: hidden;
                transition: transform .2s ease;
            }

            .app-shell.is-sidebar-open .app-sidebar {
                visibility: visible;
                transform: translateX(0);
                pointer-events: auto;
            }

            .app-overlay {
                position: fixed;
                inset: 0;
                z-index: 40;
                background: rgba(2, 6, 23, .48);
            }

            .app-shell.is-sidebar-open .app-overlay {
                display: block;
            }
        }

        .app-content .rounded-xl.bg-white,
        .app-content .overflow-x-auto.rounded-xl {
            border: 1px solid #e2e8f0;
            box-shadow: none;
        }

        .app-content input:not([type="checkbox"]):not([type="hidden"]):not(.border-0),
        .app-content select,
        .app-content textarea {
            border-color: #e2e8f0;
            border-radius: 9px;
            background-color: #f4f6fa;
            color: #0f172a;
        }

        .app-content button.bg-indigo-600,
        .app-content button.bg-emerald-600 {
            background-color: #059669;
        }

        .app-content button.bg-indigo-600:hover,
        .app-content button.bg-emerald-600:hover {
            background-color: #047857;
        }

        .app-content summary.text-indigo-600 { color: #059669; }
        .app-content table thead { background: #f4f6fa; }

        .app-content table thead th {
            color: #64748b;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .app-content table tbody tr:hover { background: #f8fafc; }
    </style>
</head>

<body class="text-slate-800">
    <div
        class="app-shell"
        x-data="{ sidebarOpen: window.innerWidth >= 768 }"
        :class="{ 'is-sidebar-open': sidebarOpen }"
    >
        <div class="app-overlay" @click="sidebarOpen = false"></div>

        <aside
            x-cloak
            id="app-sidebar"
            class="app-sidebar whitespace-nowrap"
        >
            <div class="flex min-w-0 items-center gap-3 border-b border-slate-800 p-4">
                <img
                    src="{{ asset('bbkfk-logo.png') }}"
                    alt="BBKFK Logo"
                    class="h-11 w-11 shrink-0 rounded bg-white p-1"
                >

                <div class="min-w-0">
                    <p class="text-sm text-slate-200">Monitoring</p>
                    <p class="font-semibold">Penugasan Layanan</p>
                </div>
            </div>

            <nav class="space-y-1 overflow-hidden whitespace-nowrap p-4 text-sm">
                <a
                    href="{{ route('dashboard') }}"
                    class="block rounded-lg px-3 py-2 hover:bg-slate-800 {{ request()->routeIs('dashboard') ? 'bg-emerald-600 text-white' : '' }}"
                >Dashboard</a>

                <a
                    href="{{ route('penugasan.index') }}"
                    class="block rounded-lg px-3 py-2 hover:bg-slate-800 {{ request()->routeIs('penugasan.*') ? 'bg-emerald-600 text-white' : '' }}"
                >Penugasan</a>

                <a
                    href="{{ route('petugas.index') }}"
                    class="block rounded-lg px-3 py-2 hover:bg-slate-800 {{ request()->routeIs('petugas.*') ? 'bg-emerald-600 text-white' : '' }}"
                >Menu Petugas</a>

                <a
                    href="{{ route('layanan.index') }}"
                    class="block rounded-lg px-3 py-2 hover:bg-slate-800 {{ request()->routeIs('layanan.*') ? 'bg-emerald-600 text-white' : '' }}"
                >Menu Layanan</a>

                @if (auth()->user()->isAdmin())
                    <a
                        href="{{ route('user-management.index') }}"
                        class="block rounded-lg px-3 py-2 hover:bg-slate-800 {{ request()->routeIs('user-management.*') ? 'bg-emerald-600 text-white' : '' }}"
                    >User Management</a>
                @endif
            </nav>
        </aside>

        <div class="app-body">
            <header class="app-header px-4 py-3 lg:px-8">
                <div class="flex items-center justify-between">
                    <button
                        type="button"
                        class="rounded border border-slate-400 p-2 hover:bg-slate-50 focus:ring-2 focus:ring-emerald-400"
                        @click="sidebarOpen = !sidebarOpen"
                        :aria-expanded="sidebarOpen.toString()"
                        aria-controls="app-sidebar"
                        aria-label="Buka atau tutup menu"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M3.75 5.25h16.5M3.75 12h16.5m-16.5 6.75h16.5"
                            />
                        </svg>
                    </button>

                    <div class="flex items-center gap-3">
                        <div class="text-right">
                            <p class="text-sm font-semibold">
                                {{ auth()->user()->name }}
                            </p>
                            <p class="text-xs uppercase tracking-wide text-slate-500">
                                {{ auth()->user()->role }}
                            </p>
                        </div>

                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="rounded-md bg-rose-500 px-3 py-2 text-sm font-medium text-white hover:bg-rose-600">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="app-content p-4 lg:p-8">
                {{ $slot ?? '' }}
                @yield('content')
            </main>
        </div>
    </div>

    @if (session('success'))
        <script>
            window.addEventListener(
                'load',
                () => window.showToast('success', @json(session('success')))
            );
        </script>
    @endif

    @if (session('error'))
        <script>
            window.addEventListener(
                'load',
                () => window.showToast('error', @json(session('error')))
            );
        </script>
    @endif
</body>
</html>