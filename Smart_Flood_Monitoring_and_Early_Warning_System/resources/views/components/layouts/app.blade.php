<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Smart Flood Monitoring Bandung' }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    {{-- Alamat layanan untuk pemanggilan dari browser. Kosong = pakai host
         halaman ini (perilaku produksi). Diisi saat development lokal agar
         halaman menampilkan data ASLI, bukan nilai contoh dari controller. --}}
    <script>
        window.SFMEWS_ENDPOINT = {
            api: @json(config('sfmews.api_public_url')),
            ai: @json(config('sfmews.ai_public_url')),
        };
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#F5F7FA] text-slate-800 antialiased">
    <div class="pointer-events-none fixed inset-x-0 top-0 -z-10 h-72 bg-[radial-gradient(circle_at_18%_8%,rgba(14,165,233,0.13),transparent_32%),radial-gradient(circle_at_78%_2%,rgba(34,197,94,0.10),transparent_28%)]"></div>

    {{ $slot }}

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    @stack('scripts')
</body>
</html>
