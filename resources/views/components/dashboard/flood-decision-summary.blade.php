@props(['locations'])

<section class="dashboard-card p-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-xs font-bold uppercase tracking-wide text-cyan-600">Rule-Based Early Warning</p>
            <h2 class="text-lg font-bold text-slate-950">Flood Decision System</h2>
            <p class="text-sm font-medium text-slate-500">Status realtime dihitung dari level air, curah hujan, AI CCTV, dan cuaca.</p>
        </div>
        <div class="flex flex-wrap gap-2 text-xs font-bold">
            <span class="soft-badge bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100">AMAN: <span data-flood-count-safe>-</span></span>
            <span class="soft-badge bg-amber-50 text-amber-700 ring-1 ring-amber-100">WASPADA: <span data-flood-count-warning>-</span></span>
            <span class="soft-badge bg-red-50 text-red-700 ring-1 ring-red-100">BANJIR: <span data-flood-count-danger>-</span></span>
        </div>
    </div>

    <div class="mt-5 grid gap-4 md:grid-cols-3">
        @foreach ($locations as $location)
            <article
                data-flood-location-id="{{ $location['id'] }}"
                class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-lg"
            >
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ $location['district'] }}</p>
                        <h3 class="truncate text-base font-extrabold text-slate-950">{{ $location['short_name'] ?? $location['name'] }}</h3>
                    </div>
                    <span data-flood-status-icon class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white/70 text-slate-700 ring-1 ring-slate-200">
                        <i data-lucide="activity" class="h-5 w-5"></i>
                    </span>
                </div>

                <div class="mt-4 flex items-center justify-between gap-3">
                    <span data-flood-status-badge class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-2 text-xs font-extrabold text-slate-600 ring-1 ring-slate-200">
                        <span data-flood-status-dot class="h-2.5 w-2.5 rounded-full bg-slate-400"></span>
                        <span data-flood-status-label>ANALYZING</span>
                    </span>
                    <span class="text-xs font-bold text-slate-500">Updated <span data-flood-updated>-</span></span>
                </div>

                <p data-flood-status-reason class="mt-3 min-h-10 text-sm font-semibold leading-5 text-slate-600">
                    Menunggu data realtime...
                </p>

                <div class="mt-4 grid grid-cols-3 gap-2 text-xs">
                    <div class="rounded-xl bg-white/70 p-3 ring-1 ring-slate-200">
                        <p class="font-bold uppercase text-slate-500">Water</p>
                        <p data-flood-water class="mt-1 font-extrabold text-slate-950">-</p>
                    </div>
                    <div class="rounded-xl bg-white/70 p-3 ring-1 ring-slate-200">
                        <p class="font-bold uppercase text-slate-500">Rain</p>
                        <p data-flood-rain class="mt-1 font-extrabold text-slate-950">-</p>
                    </div>
                    <div class="rounded-xl bg-white/70 p-3 ring-1 ring-slate-200">
                        <p class="font-bold uppercase text-slate-500">AI</p>
                        <p data-flood-ai class="mt-1 font-extrabold text-slate-950">-</p>
                    </div>
                </div>
            </article>
        @endforeach
    </div>
</section>
