<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Buku Tamu')</title>

    {{-- Google Fonts: Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Bootstrap 5 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --app-bg: #f5f6f8;
            --card-border: #e2e5ea;
        }

        * { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }

        body {
            background-color: var(--app-bg);
            color: #1f2937;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* ── No gradients anywhere ── */
        .btn, .card, .alert { background-image: none !important; }

        .btn {
            font-weight: 500;
            border-radius: 8px;
            padding: 0.5rem 1.25rem;
        }
        .btn-lg {
            padding: 0.75rem 1.5rem;
            font-size: 1.05rem;
        }

        .card {
            border: 1px solid var(--card-border);
            border-radius: 10px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
        }
        .card-body { padding: 1.25rem; }

        .form-control, .form-select {
            border-radius: 8px;
            border-color: #d1d5db;
            padding: 0.6rem 0.85rem;
            font-size: 0.95rem;
        }
        .form-control:focus, .form-select:focus {
            border-color: #93c5fd;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
        }
        .form-label {
            font-weight: 500;
            font-size: 0.875rem;
            color: #374151;
            margin-bottom: 0.35rem;
        }

        .table { font-size: 0.9rem; }
        .table thead th {
            background-color: #f9fafb;
            font-weight: 600;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #6b7280;
            border-bottom: 2px solid var(--card-border);
        }

        .text-muted { color: #6b7280 !important; }

        /* ── Counter ── */
        .counter-value {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1;
            color: #111827;
            font-variant-numeric: tabular-nums;
        }
        @media (min-width: 768px) {
            .counter-value { font-size: 5rem; }
        }
        .counter-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        /* ── Row flash on new entry ── */
        @keyframes rowFlash {
            from { background-color: #dcfce7; }
            to   { background-color: transparent; }
        }
        .row-flash { animation: rowFlash 2s ease-out; }

        /* ── Live dot ── */
        .live-dot {
            display: inline-block;
            width: 8px; height: 8px;
            border-radius: 50%;
            background-color: #22c55e;
            margin-right: 6px;
            animation: pulseDot 2s ease-in-out infinite;
        }
        .live-dot.disconnected {
            background-color: #ef4444;
            animation: none;
        }
        @keyframes pulseDot {
            0%,100% { opacity: 1; }
            50%     { opacity: 0.35; }
        }

        /* ── Photo thumbnails ── */
        .foto-preview {
            max-width: 120px; max-height: 120px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid var(--card-border);
        }
        .foto-thumb {
            width: 36px; height: 36px;
            object-fit: cover;
            border-radius: 4px;
        }
    </style>
    @stack('styles')
</head>
<body>

    @yield('content')

    {{-- jQuery --}}
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>

    {{-- Bootstrap 5 JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
    </script>

    @stack('scripts')
</body>
</html>
