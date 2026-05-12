<x-layouts.app title="Settings - Smart Flood Monitoring Bandung">
    <x-dashboard.sidebar />

    <div class="lg:pl-72">
        <x-dashboard.topbar :stats="$stats" :page-title="$pageTitle" :page-subtitle="$pageSubtitle" />

        <main class="grid gap-4 p-4 sm:p-6 xl:grid-cols-2 xl:p-8">
            <section class="dashboard-card p-5">
                <h2 class="text-lg font-bold text-slate-950">CCTV Source Config</h2>
                <div class="mt-4 space-y-3">
                    @foreach ($locations as $location)
                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">{{ $location['name'] }} stream URL</span>
                            <input value="rtsp://bandung-smartcity.local/{{ $location['cctv'] }}" class="mt-2 h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm font-medium outline-none transition focus:border-cyan-300 focus:ring-4 focus:ring-cyan-100">
                        </label>
                    @endforeach
                </div>
            </section>

            <section class="dashboard-card p-5">
                <h2 class="text-lg font-bold text-slate-950">AI Confidence Threshold</h2>
                <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-bold text-slate-700">YOLOv8 flood alert threshold</span>
                        <span class="soft-badge bg-cyan-50 text-cyan-700 ring-1 ring-cyan-100">75%</span>
                    </div>
                    <input type="range" min="40" max="95" value="75" class="mt-5 w-full accent-cyan-600">
                </div>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <label class="rounded-2xl border border-slate-200 bg-white p-4">
                        <span class="text-sm font-bold text-slate-700">Auto notification</span>
                        <input type="checkbox" checked class="mt-3 h-5 w-5 rounded border-slate-300 accent-cyan-600">
                    </label>
                    <label class="rounded-2xl border border-slate-200 bg-white p-4">
                        <span class="text-sm font-bold text-slate-700">Store detections</span>
                        <input type="checkbox" checked class="mt-3 h-5 w-5 rounded border-slate-300 accent-cyan-600">
                    </label>
                </div>
            </section>

            <section class="dashboard-card p-5">
                <h2 class="text-lg font-bold text-slate-950">API Settings</h2>
                <div class="mt-4 space-y-3">
                    <label class="block"><span class="text-sm font-bold text-slate-700">BMKG endpoint</span><input value="https://api.bmkg.go.id/publik/prakiraan-cuaca" class="mt-2 h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm font-medium outline-none transition focus:border-cyan-300 focus:ring-4 focus:ring-cyan-100"></label>
                    <label class="block"><span class="text-sm font-bold text-slate-700">Weather refresh interval</span><input value="10 menit" class="mt-2 h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm font-medium outline-none transition focus:border-cyan-300 focus:ring-4 focus:ring-cyan-100"></label>
                </div>
            </section>

            <section class="dashboard-card p-5">
                <h2 class="text-lg font-bold text-slate-950">Sensor Preferences</h2>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-white p-4"><p class="text-sm font-bold text-slate-700">Water level warning</p><p class="mt-2 text-2xl font-bold text-slate-950">140 cm</p></div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-4"><p class="text-sm font-bold text-slate-700">Water level danger</p><p class="mt-2 text-2xl font-bold text-slate-950">180 cm</p></div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-4"><p class="text-sm font-bold text-slate-700">Rain warning</p><p class="mt-2 text-2xl font-bold text-slate-950">25 mm/h</p></div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-4"><p class="text-sm font-bold text-slate-700">Rain danger</p><p class="mt-2 text-2xl font-bold text-slate-950">40 mm/h</p></div>
                </div>
            </section>
        </main>
    </div>
</x-layouts.app>
