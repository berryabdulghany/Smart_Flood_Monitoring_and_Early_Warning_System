<x-layouts.app title="Login Admin - Smart Flood Monitoring Bandung">
    <main class="flex min-h-screen items-center justify-center p-4">
        <div class="w-full max-w-md">

            <div class="mb-6 flex items-center justify-center gap-3">
                <span class="grid h-12 w-12 place-items-center rounded-2xl bg-cyan-500 text-white shadow-lg shadow-cyan-200">
                    <i data-lucide="waves" class="h-6 w-6"></i>
                </span>
                <div>
                    <p class="text-base font-extrabold uppercase tracking-wide text-slate-900">SFMEWS</p>
                    <p class="text-xs font-medium text-slate-500">Bandung Smart City</p>
                </div>
            </div>

            <section class="dashboard-card p-6">
                <h1 class="text-xl font-extrabold text-slate-950">Login Admin</h1>
                <p class="mt-1 text-sm font-medium text-slate-500">
                    Halaman pemantauan dapat diakses publik tanpa login. Login hanya diperlukan
                    untuk mengelola sistem.
                </p>

                @if ($errors->any())
                    <div class="mt-4 rounded-xl border border-red-100 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="mt-5 space-y-4">
                    @csrf

                    <div>
                        <label for="email" class="text-xs font-bold uppercase tracking-wide text-slate-500">Email</label>
                        <input id="email" name="email" type="email" required autofocus
                               value="{{ old('email') }}" autocomplete="username"
                               class="mt-1.5 h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm font-medium outline-none transition focus:border-cyan-300 focus:ring-4 focus:ring-cyan-100">
                    </div>

                    <div>
                        <label for="password" class="text-xs font-bold uppercase tracking-wide text-slate-500">Kata Sandi</label>
                        <input id="password" name="password" type="password" required
                               autocomplete="current-password"
                               class="mt-1.5 h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm font-medium outline-none transition focus:border-cyan-300 focus:ring-4 focus:ring-cyan-100">
                    </div>

                    <label class="flex items-center gap-2 text-sm font-medium text-slate-600">
                        <input type="checkbox" name="remember" value="1"
                               class="h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-200">
                        Ingat saya
                    </label>

                    <button type="submit"
                            class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-cyan-600 text-sm font-bold text-white shadow-lg shadow-cyan-100 transition hover:bg-cyan-700">
                        <i data-lucide="log-in" class="h-4 w-4"></i>
                        Masuk
                    </button>
                </form>
            </section>

            <a href="{{ route('dashboard') }}"
               class="mt-4 flex items-center justify-center gap-2 text-sm font-bold text-slate-500 transition hover:text-cyan-700">
                <i data-lucide="arrow-left" class="h-4 w-4"></i>
                Kembali ke halaman pemantauan
            </a>
        </div>
    </main>

    @push('scripts')
        <script>window.lucide?.createIcons();</script>
    @endpush
</x-layouts.app>
