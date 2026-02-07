@extends('layouts.app')

@section('title', 'Monitoring')

@push('styles')
<style>
    .counter-value.xl {
        font-size: 5rem;
    }
    @media (min-width: 768px) {
        .counter-value.xl { font-size: 7rem; }
    }

    .stat-number {
        font-size: 1.75rem;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
    }

    .kategori-icon {
        width: 6px; height: 6px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 5px;
        vertical-align: middle;
    }
</style>
@endpush

@section('content')
<div class="min-vh-100 d-flex flex-column">

    {{-- Navbar --}}
    <nav class="bg-white border-bottom px-3 py-2 flex-shrink-0">
        <div class="container-fluid d-flex align-items-center">
            <a href="{{ route('gate') }}" class="btn btn-light btn-sm border me-2">
                <i class="bi bi-arrow-left"></i>
            </a>
            <span class="fw-semibold">Monitoring Tamu</span>
            <span class="ms-auto d-flex align-items-center small">
                <span class="live-dot disconnected" id="statusDot"></span>
                <span id="statusText" class="text-muted">Menghubungkan…</span>
            </span>
        </div>
    </nav>

    {{-- Content --}}
    <div class="container-fluid p-3 p-md-4 flex-grow-1">

        {{-- Main counter --}}
        <div class="card mb-3">
            <div class="card-body text-center py-4">
                <div class="counter-label">Total Hadir</div>
                <div class="counter-value xl" id="totalHadir">—</div>
                <div class="counter-label">orang</div>
                <div class="mt-2">
                    <span class="badge bg-light text-dark border" style="font-size: 0.85rem;">
                        <i class="bi bi-people me-1"></i>
                        <span id="totalRombongan">0</span> rombongan
                    </span>
                </div>
            </div>
        </div>

        {{-- Per-kategori cards --}}
        <div class="row g-2 mb-3" id="kategoriCards"></div>

        {{-- Recent entries --}}
        <div class="card">
            <div class="card-body">
                <h6 class="fw-semibold mb-3">
                    <i class="bi bi-clock-history me-1"></i> Tamu Terbaru
                </h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width:60px;">Waktu</th>
                                <th>Nama Rombongan</th>
                                <th class="text-end" style="width:50px;">Jml</th>
                                <th style="width:90px;">Kategori</th>
                                <th style="width:44px;"></th>
                            </tr>
                        </thead>
                        <tbody id="recentTable">
                            <tr><td colspan="5" class="text-center text-muted py-4">Memuat data…</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Timestamp --}}
        <div class="text-center mt-3 mb-2">
            <small class="text-muted">
                Terakhir diperbarui: <span id="lastUpdated">—</span>
                &nbsp;·&nbsp;
                <button class="btn btn-link btn-sm p-0 text-muted text-decoration-none" id="btnRefresh">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
            </small>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Pusher Channels JS --}}
<script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>

<script>
$(function () {

    var CATEGORY_COLORS = {
        'Keluarga': '#1d4ed8',
        'VIP':      '#7c3aed',
        'Undangan': '#0e7490',
        'Umum':     '#374151',
        'Lainnya':  '#6b7280'
    };
    var CATEGORY_ICONS = {
        'Keluarga': 'bi-house-heart',
        'VIP':      'bi-star',
        'Undangan': 'bi-envelope-paper',
        'Umum':     'bi-people',
        'Lainnya':  'bi-tag'
    };

    // ── Initial load ──
    loadData();
    $('#btnRefresh').on('click', loadData);

    // ── WebSocket via Pusher ──
    try {
        var pusher = new Pusher('{{ env("PUSHER_APP_KEY") }}', {
            cluster: '{{ env("PUSHER_APP_CLUSTER", "mt1") }}',
            forceTLS: true,
        });

        pusher.connection.bind('connected', function () {
            $('#statusDot').removeClass('disconnected');
            $('#statusText').text('Live');
        });
        pusher.connection.bind('disconnected', function () {
            $('#statusDot').addClass('disconnected');
            $('#statusText').text('Terputus');
        });
        pusher.connection.bind('error', function () {
            $('#statusDot').addClass('disconnected');
            $('#statusText').text('Offline — auto-refresh');
            startPolling();
        });

        var channel = pusher.subscribe('guestbook');
        channel.bind('tamu.baru', function (data) {
            updateDashboard(data);
        });

    } catch (e) {
        console.warn('WebSocket tidak tersedia, fallback ke polling.', e);
        $('#statusDot').addClass('disconnected');
        $('#statusText').text('Auto-refresh');
        startPolling();
    }

    var pollingInterval = null;
    function startPolling() {
        if (!pollingInterval) {
            pollingInterval = setInterval(loadData, 8000);
        }
    }

    // ── Functions ──
    function loadData() {
        $.getJSON('{{ route("api.data") }}', function (data) {
            $('#totalHadir').text(fmt(data.total));
            $('#totalRombongan').text(data.total_rombongan);
            renderKategori(data.per_kategori);
            renderRecent(data.recent);
            updateTimestamp();
        });
    }

    function updateDashboard(data) {
        $('#totalHadir').text(fmt(data.total));
        $('#totalRombongan').text(data.totalRombongan);
        renderKategori(data.perKategori);

        if (data.tamu) {
            var t = data.tamu;
            var waktu = fmtTime(t.waktu_datang);
            var fotoHtml = t.foto_url
                ? '<img src="' + t.foto_url + '" class="foto-thumb" alt="Foto">'
                : '';
            var row = '<tr class="row-flash">' +
                '<td class="text-muted">' + waktu + '</td>' +
                '<td class="fw-medium">' + esc(t.nama_rombongan) + '</td>' +
                '<td class="text-end fw-semibold">' + t.jumlah_orang + '</td>' +
                '<td>' + badge(t.kategori) + '</td>' +
                '<td>' + fotoHtml + '</td>' +
                '</tr>';

            if ($('#recentTable tr td[colspan]').length) {
                $('#recentTable').empty();
            }
            $('#recentTable').prepend(row);
            while ($('#recentTable tr').length > 20) {
                $('#recentTable tr:last').remove();
            }
        }
        updateTimestamp();
    }

    function renderKategori(obj) {
        if (!obj || Object.keys(obj).length === 0) {
            $('#kategoriCards').empty();
            return;
        }
        var html = '';
        $.each(obj, function (kat, total) {
            var color = CATEGORY_COLORS[kat] || '#4b5563';
            var icon = CATEGORY_ICONS[kat] || 'bi-tag';
            html += '<div class="col-6 col-md-3">' +
                '<div class="card"><div class="card-body text-center py-3">' +
                '<div class="stat-number" style="color:' + color + ';">' + fmt(total) + '</div>' +
                '<div class="counter-label"><i class="bi ' + icon + ' me-1" style="color:' + color + ';"></i>' + esc(kat) + '</div>' +
                '</div></div></div>';
        });
        $('#kategoriCards').html(html);
    }

    function renderRecent(items) {
        if (!items || items.length === 0) {
            $('#recentTable').html(
                '<tr><td colspan="5" class="text-center text-muted py-4">Belum ada tamu terdaftar.</td></tr>'
            );
            return;
        }
        var html = '';
        $.each(items, function (i, t) {
            var fotoHtml = t.foto_url
                ? '<img src="' + t.foto_url + '" class="foto-thumb" alt="Foto">'
                : '';
            html += '<tr>' +
                '<td class="text-muted">' + fmtTime(t.waktu_datang) + '</td>' +
                '<td class="fw-medium">' + esc(t.nama_rombongan) + '</td>' +
                '<td class="text-end fw-semibold">' + t.jumlah_orang + '</td>' +
                '<td>' + badge(t.kategori) + '</td>' +
                '<td>' + fotoHtml + '</td>' +
                '</tr>';
        });
        $('#recentTable').html(html);
    }

    function badge(kat) {
        var label = kat || '—';
        var color = CATEGORY_COLORS[kat] || '#6b7280';
        var icon = CATEGORY_ICONS[kat] || 'bi-tag';
        return '<span class="badge bg-light border" style="color:' + color + ';"><i class="bi ' + icon + ' me-1"></i>' + esc(label) + '</span>';
    }

    function fmt(n) {
        return Number(n || 0).toLocaleString('id-ID');
    }

    function fmtTime(dt) {
        return new Date(dt).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    }

    function updateTimestamp() {
        $('#lastUpdated').text(new Date().toLocaleTimeString('id-ID'));
    }

    function esc(text) {
        var d = document.createElement('div');
        d.appendChild(document.createTextNode(text || ''));
        return d.innerHTML;
    }
});
</script>
@endpush
