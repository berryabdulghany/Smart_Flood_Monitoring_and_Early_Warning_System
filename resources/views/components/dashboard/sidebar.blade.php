@php
    $menus = [
        ['label' => 'Dashboard', 'icon' => 'layout-dashboard', 'route' => 'dashboard'],
        ['label' => 'Flood Map GIS', 'icon' => 'map', 'route' => 'flood-map'],
        ['label' => 'CCTV Monitoring', 'icon' => 'video', 'route' => 'cctv'],
        ['label' => 'IoT Monitoring', 'icon' => 'activity', 'route' => 'iot'],
        ['label' => 'Flood Event History', 'icon' => 'history', 'route' => 'history'],
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
            <p class="text-xs font-medium text-slate-500">Bandung Smart City</p>
        </div>
    </div>

    <nav class="mt-7 space-y-1.5">
        @foreach ($menus as $menu)
            @php $active = request()->routeIs($menu['route']); @endphp
            <a href="{{ route($menu['route']) }}" class="group relative flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold transition duration-200 {{ $active ? 'bg-cyan-50 text-cyan-700 shadow-sm ring-1 ring-cyan-100' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="absolute left-0 top-1/2 h-6 w-1 -translate-y-1/2 rounded-r-full transition {{ $active ? 'bg-cyan-500 opacity-100' : 'bg-cyan-500 opacity-0 group-hover:opacity-40' }}"></span>
                <i data-lucide="{{ $menu['icon'] }}" class="h-5 w-5 {{ $active ? 'text-cyan-600' : 'text-slate-400 group-hover:text-cyan-600' }}"></i>
                <span>{{ $menu['label'] }}</span>
            </a>
        @endforeach
    </nav>


</aside>
