<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CargoTrace - Global Shipment Tracking')</title>
    
    <!-- Tailwind CSS CDN untuk kemudahan preview -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Leaflet.js CSS & JS untuk Peta Rute Interaktif (Slide 09) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <style>
        .pulse-marker {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.2); }
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 font-sans antialiased min-h-screen flex flex-col">

    <!-- Top Navigation Bar -->
    <header class="border-b border-slate-800 bg-slate-900/90 backdrop-blur sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <!-- Brand Logo -->
            <div class="flex items-center space-x-3">
                <a href="{{ route('shipments.index') }}" class="flex items-center space-x-2">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-500 flex items-center justify-center shadow-lg shadow-blue-500/20 text-white font-bold">
                        CT
                    </div>
                    <div>
                        <span class="text-lg font-bold bg-gradient-to-r from-blue-400 to-teal-300 bg-clip-text text-transparent">CargoTrace</span>
                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-blue-900/60 text-blue-300 border border-blue-700/50 uppercase ml-1 font-semibold">UAS</span>
                    </div>
                </a>
            </div>

            <!-- Navigation Links -->
            @auth
            <nav class="hidden md:flex items-center space-x-1">
                <a href="{{ route('shipments.index') }}" class="px-3 py-1.5 text-xs font-semibold rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 transition">
                    📦 Pengiriman (Shipments)
                </a>
                <a href="{{ route('carriers.index') }}" class="px-3 py-1.5 text-xs font-semibold rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 transition">
                    🚢 Maskapai (Carriers)
                </a>
                <a href="{{ route('locations.index') }}" class="px-3 py-1.5 text-xs font-semibold rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 transition">
                    📍 Pelabuhan (Locations)
                </a>
            </nav>

            <!-- User Role Badge & Logout -->
            <div class="flex items-center space-x-3">
                <div class="text-right hidden sm:block">
                    <p class="text-xs font-medium text-slate-200">{{ auth()->user()->name }}</p>
                    <span class="inline-flex items-center px-2 py-0.2 rounded text-[10px] font-semibold uppercase
                        {{ auth()->user()->isAdmin() ? 'bg-purple-900/50 text-purple-300 border border-purple-700' : '' }}
                        {{ auth()->user()->isOperator() ? 'bg-amber-900/50 text-amber-300 border border-amber-700' : '' }}
                        {{ auth()->user()->isCustomer() ? 'bg-emerald-900/50 text-emerald-300 border border-emerald-700' : '' }}">
                        {{ auth()->user()->role }}
                    </span>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 bg-slate-800 hover:bg-rose-900/40 text-slate-300 hover:text-rose-300 border border-slate-700 rounded-lg text-xs font-medium transition">
                        Keluar
                    </button>
                </form>
            </div>
            @endauth
        </div>
    </header>

    <!-- Flash Messages (Notifikasi Sukses / Error) -->
    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 w-full space-y-4">
        @if (session('success'))
            <div class="p-4 rounded-xl bg-emerald-950/60 border border-emerald-600/40 text-emerald-300 text-xs flex items-center justify-between">
                <span>✓ {{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white">✕</button>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-950/60 border border-rose-600/40 text-rose-300 text-xs space-y-1">
                <strong>Ada beberapa kendala input:</strong>
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer Standar UAS -->
    <footer class="border-t border-slate-800/80 py-4 text-center text-xs text-slate-500">
        CargoTrace • Web Programming 2 Final Project • Laravel 12 + Leaflet.js + OpenStreetMap
    </footer>

    @stack('scripts')
</body>
</html>