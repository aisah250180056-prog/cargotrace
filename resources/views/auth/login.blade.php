@extends('layouts.app')

@section('title', 'Login - CargoTrace Platform')

@section('content')
<div class="max-w-md mx-auto my-12 bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-6">
    <div class="text-center space-y-1">
        <h2 class="text-xl font-bold text-white">Masuk ke CargoTrace</h2>
        <p class="text-xs text-slate-400">Platform Pelacakan Pengiriman & Visibilitas Global</p>
    </div>

    <!-- Quick Demo Accounts (Slide 18) -->
    <div class="bg-slate-950/60 border border-slate-800 p-3 rounded-xl space-y-2">
        <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">⚡ Akun Demo Pengujian (1-Klik):</span>
        <div class="grid grid-cols-3 gap-2">
            <button type="button" onclick="quickFill('admin@cargotrace.com')" class="px-2 py-1.5 text-[11px] bg-purple-950/60 text-purple-300 border border-purple-700/50 rounded-lg hover:bg-purple-900 transition">
                Admin
            </button>
            <button type="button" onclick="quickFill('operator@cargotrace.com')" class="px-2 py-1.5 text-[11px] bg-amber-950/60 text-amber-300 border border-amber-700/50 rounded-lg hover:bg-amber-900 transition">
                Operator
            </button>
            <button type="button" onclick="quickFill('customer@cargotrace.com')" class="px-2 py-1.5 text-[11px] bg-emerald-950/60 text-emerald-300 border border-emerald-700/50 rounded-lg hover:bg-emerald-900 transition">
                Customer
            </button>
        </div>
    </div>

    <!-- Form Login -->
    <form action="{{ route('login') }}" method="POST" class="space-y-4 text-xs">
        @csrf
        <div>
            <label class="block font-medium text-slate-300 mb-1">Email Akun</label>
            <input type="email" id="email" name="email" value="{{ old('email', 'admin@cargotrace.com') }}" required class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
        </div>

        <div>
            <label class="block font-medium text-slate-300 mb-1">Password</label>
            <input type="password" id="password" name="password" value="password" required class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
        </div>

        <div class="flex items-center justify-between text-[11px] text-slate-400">
            <label class="flex items-center space-x-1.5 cursor-pointer">
                <input type="checkbox" name="remember" class="rounded bg-slate-800 border-slate-700 text-blue-600 focus:ring-0">
                <span>Ingat saya</span>
            </label>
            <span class="text-slate-500">Default pass: password</span>
        </div>

        <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg font-semibold shadow-md shadow-blue-600/20 transition">
            Masuk ke Sistem
        </button>
    </form>
</div>

<script>
    function quickFill(email) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = 'password';
    }
</script>
@endsection