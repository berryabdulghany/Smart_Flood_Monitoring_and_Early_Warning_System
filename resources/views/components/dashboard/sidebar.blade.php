@php
    $menus = [
        ['label' => 'Dashboard', 'icon' => 'layout-dashboard', 'route' => 'dashboard', 'key' => 'nav.dashboard'],
        ['label' => 'Flood Map GIS', 'icon' => 'map', 'route' => 'flood-map', 'key' => 'nav.flood-map'],
        ['label' => 'CCTV Monitoring', 'icon' => 'video', 'route' => 'cctv', 'key' => 'nav.cctv'],
        ['label' => 'IoT Monitoring', 'icon' => 'activity', 'route' => 'iot', 'key' => 'nav.iot'],
        ['label' => 'Flood Event History', 'icon' => 'history', 'route' => 'history', 'key' => 'nav.history'],
    ];
@endphp

<div id="mobile-sidebar-backdrop" class="fixed inset-0 z-30 hidden bg-slate-900/30 backdrop-blur-sm lg:hidden"></div>

<aside id="dashboard-sidebar" class="fixed inset-y-0 left-0 z-40 w-72 -translate-x-full border-r border-slate-200/80 bg-white/[0.92] px-4 py-5 shadow-2xl shadow-slate-200/70 backdrop-blur-xl transition-transform duration-300 lg:translate-x-0">
    <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50/80 p-3">
        <div class="grid h-11 w-11 place-items-center rounded-xl bg-cyan-500 text-white shadow-lg shadow-cyan-200">
            <i data-lucide="waves" class="h-6 w-6"></i>
        </div>
        <div>
            <p class="text-sm font-bold uppercase tracking-wide text-slate-900">SFMEWS</p>
            <p class="text-xs font-medium text-slate-500" data-i18n="brand.sub">Bandung Smart City</p>
        </div>
    </div>

    <nav class="mt-7 space-y-1.5">
        @foreach ($menus as $menu)
            @php $active = request()->routeIs($menu['route']); @endphp
            <a href="{{ route($menu['route']) }}" class="group relative flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold transition duration-200 {{ $active ? 'bg-cyan-50 text-cyan-700 shadow-sm ring-1 ring-cyan-100' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="absolute left-0 top-1/2 h-6 w-1 -translate-y-1/2 rounded-r-full transition {{ $active ? 'bg-cyan-500 opacity-100' : 'bg-cyan-500 opacity-0 group-hover:opacity-40' }}"></span>
                <i data-lucide="{{ $menu['icon'] }}" class="h-5 w-5 {{ $active ? 'text-cyan-600' : 'text-slate-400 group-hover:text-cyan-600' }}"></i>
                <span data-i18n="{{ $menu['key'] }}">{{ $menu['label'] }}</span>
            </a>
        @endforeach
    </nav>

    {{-- ====== AREA ADMIN: hanya tampil setelah login (use case Admin) ====== --}}
    @php $admin = session('admin_user'); @endphp
    @if ($admin)
        <p class="mt-6 px-3 text-[10px] font-extrabold uppercase tracking-widest text-slate-400">Panel Admin</p>
        <nav class="mt-2 space-y-1.5">
            @php $aktifTitik = request()->routeIs('admin.points'); @endphp
            <a href="{{ route('admin.points') }}"
               class="group relative flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold transition duration-200 {{ $aktifTitik ? 'bg-cyan-50 text-cyan-700 shadow-sm ring-1 ring-cyan-100' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="absolute left-0 top-1/2 h-6 w-1 -translate-y-1/2 rounded-r-full transition {{ $aktifTitik ? 'bg-cyan-500 opacity-100' : 'bg-cyan-500 opacity-0 group-hover:opacity-40' }}"></span>
                <i data-lucide="map-pin" class="h-5 w-5 {{ $aktifTitik ? 'text-cyan-600' : 'text-slate-400 group-hover:text-cyan-600' }}"></i>
                <span data-i18n="nav.points">Kelola Titik Monitoring</span>
            </a>

            @php $aktifSistem = request()->routeIs('admin.system'); @endphp
            <a href="{{ route('admin.system') }}"
               class="group relative flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold transition duration-200 {{ $aktifSistem ? 'bg-cyan-50 text-cyan-700 shadow-sm ring-1 ring-cyan-100' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="absolute left-0 top-1/2 h-6 w-1 -translate-y-1/2 rounded-r-full transition {{ $aktifSistem ? 'bg-cyan-500 opacity-100' : 'bg-cyan-500 opacity-0 group-hover:opacity-40' }}"></span>
                <i data-lucide="sliders-horizontal" class="h-5 w-5 {{ $aktifSistem ? 'text-cyan-600' : 'text-slate-400 group-hover:text-cyan-600' }}"></i>
                <span data-i18n="nav.system">Kelola Sistem</span>
            </a>
        </nav>

        <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50/70 p-3">
            <p class="truncate text-xs font-bold text-slate-700">{{ $admin['name'] ?? 'Administrator' }}</p>
            <p class="truncate text-[11px] font-medium text-slate-400">{{ $admin['email'] ?? '' }}</p>
            <form method="POST" action="{{ route('logout') }}" class="mt-2">
                @csrf
                <button type="submit"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-600 transition hover:border-red-100 hover:bg-red-50 hover:text-red-600">
                    <i data-lucide="log-out" class="h-3.5 w-3.5"></i>
                    <span data-i18n="nav.logout">Keluar</span>
                </button>
            </form>
        </div>
    @endif
    {{-- Tautan Login sengaja TIDAK ditampilkan di sidebar publik: halaman ini
         ditujukan untuk masyarakat, bukan pengelola. Admin masuk lewat URL
         /login secara langsung. Catatan: ini alasan kepantasan tampilan, BUKAN
         pengamanan — perlindungan sesungguhnya ada pada hash kata sandi,
         pembatasan percobaan login, dan middleware `admin`. --}}
</aside>
