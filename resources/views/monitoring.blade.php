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

    /* ── Admin styles ── */
    .admin-actions { white-space: nowrap; }
    .admin-actions .btn { padding: 2px 6px; font-size: 0.78rem; }
    .bulk-bar {
        position: fixed; bottom: 0; left: 0; right: 0;
        background: #fff; border-top: 2px solid #dc3545;
        padding: 10px 16px; z-index: 1040;
        display: none;
        padding-bottom: max(10px, env(safe-area-inset-bottom));
    }
    .bulk-bar.show { display: block; }
    .chk-cell { width: 36px; text-align: center; }
    .chk-cell input[type="checkbox"] { width: 16px; height: 16px; cursor: pointer; }
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

        {{-- Spacer for bulk bar --}}
        <div id="bulkSpacer" style="height:0;"></div>
    </div>
</div>

{{-- Bulk delete bar --}}
<div class="bulk-bar" id="bulkBar">
    <div class="container-fluid d-flex align-items-center justify-content-between">
        <span><strong id="bulkCount">0</strong> item dipilih</span>
        <div>
            <button class="btn btn-outline-secondary btn-sm me-1" id="bulkCancel">
                Batal
            </button>
            <button class="btn btn-danger btn-sm" id="bulkDeleteBtn">
                <i class="bi bi-trash me-1"></i> Hapus Terpilih
            </button>
        </div>
    </div>
</div>

{{-- Edit modal --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-semibold">Edit Data Tamu</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editId">
                <div class="mb-3">
                    <label class="form-label">Nama Rombongan</label>
                    <input type="text" class="form-control" id="editNama" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Jumlah Orang</label>
                    <input type="number" class="form-control" id="editJumlah" min="1" max="999" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Kategori</label>
                    <select class="form-select" id="editKategori">
                        <option value="Keluarga">Keluarga</option>
                        <option value="VIP">VIP</option>
                        <option value="Undangan">Undangan</option>
                        <option value="Umum">Umum</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Keterangan</label>
                    <textarea class="form-control" id="editKeterangan" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="editSaveBtn">
                    <i class="bi bi-check-lg me-1"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Delete confirmation modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <i class="bi bi-exclamation-triangle text-danger" style="font-size:2rem;"></i>
                <p class="mt-2 mb-1 fw-semibold">Hapus data ini?</p>
                <p class="text-muted small mb-0" id="deleteInfo"></p>
            </div>
            <div class="modal-footer justify-content-center border-0 pt-0">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger btn-sm" id="deleteConfirmBtn">
                    <i class="bi bi-trash me-1"></i> Hapus
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Pusher Channels JS --}}
<script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>

<script>
$(function () {

    var IS_ADMIN = new URLSearchParams(window.location.search).get('superadmin') === '1';

    // ── Setup admin columns ──
    if (IS_ADMIN) {
        // Add checkbox header + actions header
        $('#recentTable').closest('table').find('thead tr')
            .prepend('<th class="chk-cell"><input type="checkbox" id="chkAll"></th>')
            .append('<th style="width:80px;"></th>');
    }

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
            var row = '<tr class="row-flash" data-id="' + t.id + '">';
            if (IS_ADMIN) {
                row += '<td class="chk-cell"><input type="checkbox" class="chk-row" value="' + t.id + '"></td>';
            }
            row += '<td class="text-muted">' + waktu + '</td>' +
                '<td class="fw-medium">' + esc(t.nama_rombongan) + '</td>' +
                '<td class="text-end fw-semibold">' + t.jumlah_orang + '</td>' +
                '<td>' + badge(t.kategori) + '</td>' +
                '<td>' + fotoHtml + '</td>';
            if (IS_ADMIN) {
                row += '<td class="admin-actions">' +
                    '<button class="btn btn-outline-secondary btn-edit" data-id="' + t.id + '" data-nama="' + escAttr(t.nama_rombongan) + '" data-jumlah="' + t.jumlah_orang + '" data-kategori="' + escAttr(t.kategori || '') + '" data-keterangan="' + escAttr(t.keterangan || '') + '" title="Edit">' +
                    '<i class="bi bi-pencil"></i></button> ' +
                    '<button class="btn btn-outline-danger btn-delete" data-id="' + t.id + '" data-nama="' + escAttr(t.nama_rombongan) + '" title="Hapus">' +
                    '<i class="bi bi-trash"></i></button>' +
                    '</td>';
            }
            row += '</tr>';

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
        var cols = IS_ADMIN ? 7 : 5;
        if (!items || items.length === 0) {
            $('#recentTable').html(
                '<tr><td colspan="' + cols + '" class="text-center text-muted py-4">Belum ada tamu terdaftar.</td></tr>'
            );
            updateBulkBar();
            return;
        }
        var html = '';
        $.each(items, function (i, t) {
            var fotoHtml = t.foto_url
                ? '<img src="' + t.foto_url + '" class="foto-thumb" alt="Foto">'
                : '';
            html += '<tr data-id="' + t.id + '">';
            if (IS_ADMIN) {
                html += '<td class="chk-cell"><input type="checkbox" class="chk-row" value="' + t.id + '"></td>';
            }
            html += '<td class="text-muted">' + fmtTime(t.waktu_datang) + '</td>' +
                '<td class="fw-medium">' + esc(t.nama_rombongan) + '</td>' +
                '<td class="text-end fw-semibold">' + t.jumlah_orang + '</td>' +
                '<td>' + badge(t.kategori) + '</td>' +
                '<td>' + fotoHtml + '</td>';
            if (IS_ADMIN) {
                html += '<td class="admin-actions">' +
                    '<button class="btn btn-outline-secondary btn-edit" data-id="' + t.id + '" data-nama="' + escAttr(t.nama_rombongan) + '" data-jumlah="' + t.jumlah_orang + '" data-kategori="' + escAttr(t.kategori || '') + '" data-keterangan="' + escAttr(t.keterangan || '') + '" title="Edit">' +
                    '<i class="bi bi-pencil"></i></button> ' +
                    '<button class="btn btn-outline-danger btn-delete" data-id="' + t.id + '" data-nama="' + escAttr(t.nama_rombongan) + '" title="Hapus">' +
                    '<i class="bi bi-trash"></i></button>' +
                    '</td>';
            }
            html += '</tr>';
        });
        $('#recentTable').html(html);
        updateBulkBar();
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

    function escAttr(text) {
        return (text || '').replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/'/g,'&#39;').replace(/</g,'&lt;');
    }

    // ── Admin: bulk select ──
    function updateBulkBar() {
        if (!IS_ADMIN) return;
        var count = $('.chk-row:checked').length;
        $('#bulkCount').text(count);
        if (count > 0) {
            $('#bulkBar').addClass('show');
            $('#bulkSpacer').css('height', '60px');
        } else {
            $('#bulkBar').removeClass('show');
            $('#bulkSpacer').css('height', '0');
        }
    }

    $(document).on('change', '.chk-row', updateBulkBar);
    $(document).on('change', '#chkAll', function () {
        $('.chk-row').prop('checked', this.checked);
        updateBulkBar();
    });

    $('#bulkCancel').on('click', function () {
        $('.chk-row, #chkAll').prop('checked', false);
        updateBulkBar();
    });

    // ── Admin: bulk delete ──
    $('#bulkDeleteBtn').on('click', function () {
        var ids = $('.chk-row:checked').map(function () { return +this.value; }).get();
        if (!ids.length) return;
        if (!confirm('Hapus ' + ids.length + ' data tamu?')) return;

        var $btn = $(this);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        $.ajax({
            url: '/api/tamu/bulk-delete',
            type: 'POST',
            data: JSON.stringify({ ids: ids }),
            contentType: 'application/json',
            headers: { 'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content') },
            success: function () {
                loadData();
                $('#chkAll').prop('checked', false);
                updateBulkBar();
            },
            error: function () { alert('Gagal menghapus.'); },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="bi bi-trash me-1"></i> Hapus Terpilih');
            }
        });
    });

    // ── Admin: single edit ──
    var editModal = null;
    $(document).on('click', '.btn-edit', function () {
        var $b = $(this);
        $('#editId').val($b.data('id'));
        $('#editNama').val($b.data('nama'));
        $('#editJumlah').val($b.data('jumlah'));
        $('#editKategori').val($b.data('kategori') || 'Undangan');
        $('#editKeterangan').val($b.data('keterangan'));
        if (!editModal) editModal = new bootstrap.Modal('#editModal');
        editModal.show();
    });

    $('#editSaveBtn').on('click', function () {
        var id = $('#editId').val();
        var $btn = $(this);
        $btn.prop('disabled', true);

        $.ajax({
            url: '/api/tamu/' + id,
            type: 'PUT',
            data: JSON.stringify({
                nama_rombongan: $('#editNama').val(),
                jumlah_orang: parseInt($('#editJumlah').val()) || 1,
                kategori: $('#editKategori').val(),
                keterangan: $('#editKeterangan').val()
            }),
            contentType: 'application/json',
            headers: { 'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content') },
            success: function () {
                editModal.hide();
                loadData();
            },
            error: function () { alert('Gagal menyimpan.'); },
            complete: function () { $btn.prop('disabled', false); }
        });
    });

    // ── Admin: single delete ──
    var deleteModal = null;
    var deleteId = null;
    $(document).on('click', '.btn-delete', function () {
        deleteId = $(this).data('id');
        $('#deleteInfo').text($(this).data('nama'));
        if (!deleteModal) deleteModal = new bootstrap.Modal('#deleteModal');
        deleteModal.show();
    });

    $('#deleteConfirmBtn').on('click', function () {
        if (!deleteId) return;
        var $btn = $(this);
        $btn.prop('disabled', true);

        $.ajax({
            url: '/api/tamu/' + deleteId,
            type: 'DELETE',
            headers: { 'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content') },
            success: function () {
                deleteModal.hide();
                loadData();
            },
            error: function () { alert('Gagal menghapus.'); },
            complete: function () { $btn.prop('disabled', false); deleteId = null; }
        });
    });
});
</script>
@endpush
