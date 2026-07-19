<section class="dashboard-card overflow-hidden">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 p-4">
        <div>
            <p class="text-xs font-bold uppercase tracking-wide text-cyan-600">YOLO AI Engine</p>
            <h2 class="text-lg font-bold text-slate-950">AI CCTV Flood Detection</h2>
            <p class="text-sm font-medium text-slate-500">Video simulation for thesis demonstration and future realtime CCTV stream integration.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <span id="ai-engine-status-pill" class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-2 text-xs font-bold text-slate-600 ring-1 ring-slate-200">
                <span id="ai-engine-status-indicator" class="h-2.5 w-2.5 rounded-full bg-slate-400"></span>
                <span id="ai-engine-status">Standby</span>
            </span>
            <span class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-2 text-xs font-bold text-slate-500 ring-1 ring-slate-200">
                <i data-lucide="clock-3" class="h-4 w-4 text-cyan-600"></i>
                <span>Detection Time: <span id="ai-detection-timestamp">Waiting...</span></span>
            </span>
        </div>
    </div>

    <div class="grid gap-3 p-4 ai-cctv-grid xl:grid-cols-[minmax(0,1.35fr)_minmax(280px,0.65fr)]">
        <div class="space-y-3">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-950 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-white/10 bg-slate-900 px-4 py-3">
                    <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-red-500"></span>
                        <span class="h-2.5 w-2.5 rounded-full bg-amber-400"></span>
                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                    </div>
                    <select id="ai-monitoring-location" class="h-9 rounded-lg border border-white/10 bg-white/10 px-3 text-xs font-bold text-white outline-none transition hover:bg-white/15">
                        <option value="Kopo" data-location-id="kopo">Kopo</option>
                        <option value="Pasir Koja" data-location-id="pasir-koja">Pasir Koja</option>
                        <option value="Gedebage" data-location-id="gede-bage">Gedebage</option>
                    </select>
                    <select id="ai-video-source" class="h-9 rounded-lg border border-white/10 bg-white/10 px-3 text-xs font-bold text-white outline-none transition hover:bg-white/15">
                        <option value="/videos/banjir.mp4" data-location="Kopo" data-location-id="kopo">Flood simulation</option>
                        <option value="/videos/normal.mp4" data-location="Gedebage" data-location-id="gede-bage">Normal condition</option>
                        <option value="/videos/gedebage-flood-transition.mp4" data-location="Gedebage" data-location-id="gede-bage">Gedebage Flood Transition</option>
                        <option value="/videos/pasirkoja-flood-transition.mp4" data-location="Pasir Koja" data-location-id="pasir-koja">Pasirkoja Flood Transition</option>
                        <option value="/videos/kopo-flood-transition.mp4" data-location="Kopo" data-location-id="kopo">Kopo Flood Transition</option>
                    </select>
                </div>
                <div class="relative aspect-video bg-slate-950">
                    <video id="ai-cctv-video" class="h-full w-full object-cover" src="/videos/banjir.mp4" controls muted loop playsinline preload="metadata"></video>
                    <canvas id="ai-frame-canvas" class="hidden"></canvas>
                    <div id="ai-processing-overlay" class="pointer-events-none absolute inset-0 hidden place-items-center bg-slate-950/30 backdrop-blur-[1px]">
                        <div class="rounded-2xl bg-white/90 px-4 py-3 text-sm font-bold text-cyan-700 shadow-xl">
                            Processing AI detection...
                        </div>
                    </div>
                    <div class="absolute left-3 top-3 rounded-full bg-white/88 px-3 py-1.5 text-xs font-bold text-slate-700 shadow">
                        CCTV-SIM-BDG-01 · <span id="ai-selected-location-label">Kopo</span>
                    </div>
                    <div id="ai-video-badge" class="absolute bottom-3 left-3 rounded-xl bg-slate-950/75 px-3 py-2 text-xs font-bold text-white shadow">
                        Ready for AI detection
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <button id="ai-start-detection" class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 rounded-xl bg-cyan-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-cyan-100 transition hover:bg-cyan-700">
                    <i data-lucide="play" class="h-4 w-4"></i>
                    Start Detection
                </button>
                <button id="ai-stop-detection" class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-600 shadow-sm transition hover:border-red-100 hover:bg-red-50 hover:text-red-600" disabled>
                    <i data-lucide="square" class="h-4 w-4"></i>
                    Stop Detection
                </button>
            </div>
        </div>

        <div class="space-y-3">
            <div id="ai-alert-card" class="rounded-2xl border border-slate-200 bg-slate-50 p-4 transition duration-300">
                <div class="flex items-start gap-3">
                    <span id="ai-alert-icon" class="grid h-11 w-11 place-items-center rounded-xl bg-slate-100 text-slate-500">
                        <i data-lucide="radar" class="h-5 w-5"></i>
                    </span>
                    <div>
                        <p id="ai-alert-title" class="text-lg font-bold text-slate-950">AI Standby</p>
                        <p id="ai-alert-message" class="mt-1 text-sm font-medium text-slate-500">Start detection to analyze CCTV simulation frames.</p>
                    </div>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Detection Status</p>
                    <p id="ai-detection-status" class="mt-2 text-2xl font-extrabold text-slate-950">Waiting</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Confidence</p>
                    <p class="mt-2 text-2xl font-extrabold text-slate-950"><span id="ai-confidence-value">0</span><span class="text-lg text-slate-400">%</span></p>
                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100">
                        <div id="ai-confidence-bar" class="h-full w-0 rounded-full bg-cyan-500 transition-all duration-500"></div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold uppercase tracking-wide text-slate-900">Detection History</h3>
                    <span class="text-xs font-bold text-slate-400">Latest 6</span>
                </div>
                <div id="ai-detection-history" class="mt-3 space-y-2">
                    <div class="rounded-xl bg-slate-50 px-3 py-2 text-sm font-medium text-slate-500">No detection yet.</div>
                </div>
            </div>

            <div id="ai-error" class="hidden rounded-2xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                YOLO AI Engine offline atau endpoint /detect tidak dapat diakses.
            </div>
        </div>
    </div>
</section>
