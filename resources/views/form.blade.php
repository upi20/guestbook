@extends('layouts.app')

@section('title', 'Form Buku Tamu')

@push('styles')
<style>
    /* ── Radio pill buttons ── */
    .radio-pills { display: flex; flex-wrap: wrap; gap: 8px; }
    .radio-pills input[type="radio"] { display: none; }
    .radio-pills label {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 44px; height: 44px; padding: 0 14px;
        border: 1px solid #d1d5db; border-radius: 8px;
        font-weight: 600; font-size: 0.95rem; color: #374151;
        cursor: pointer; user-select: none; background: #fff;
        transition: all .15s;
    }
    .radio-pills input[type="radio"]:checked + label {
        background-color: #2563eb; border-color: #2563eb; color: #fff;
    }

    /* ── Kategori pills with icons & per-category colors ── */
    .kat-pills label {
        gap: 6px; padding: 0 16px; font-weight: 500;
    }
    .kat-pills label .kat-icon {
        font-size: 1rem; transition: color .15s;
    }
    .kat-pills .kat-keluarga input:checked + label { background-color: #1d4ed8; border-color: #1d4ed8; }
    .kat-pills .kat-keluarga label .kat-icon { color: #3b82f6; }
    .kat-pills .kat-keluarga input:checked + label .kat-icon { color: #fff; }

    .kat-pills .kat-vip input:checked + label { background-color: #7c3aed; border-color: #7c3aed; }
    .kat-pills .kat-vip label .kat-icon { color: #8b5cf6; }
    .kat-pills .kat-vip input:checked + label .kat-icon { color: #fff; }

    .kat-pills .kat-undangan input:checked + label { background-color: #0e7490; border-color: #0e7490; }
    .kat-pills .kat-undangan label .kat-icon { color: #06b6d4; }
    .kat-pills .kat-undangan input:checked + label .kat-icon { color: #fff; }

    .kat-pills .kat-umum input:checked + label { background-color: #374151; border-color: #374151; }
    .kat-pills .kat-umum label .kat-icon { color: #6b7280; }
    .kat-pills .kat-umum input:checked + label .kat-icon { color: #fff; }

    /* ── Jumlah custom input ── */
    #jumlah_custom {
        width: 84px; height: 44px; text-align: center;
        font-size: 1.1rem; font-weight: 700; border-radius: 8px;
    }

    /* ── Camera button ── */
    .btn-camera {
        border-radius: 8px; padding: 10px 16px;
        display: inline-flex; align-items: center; gap: 6px;
        font-size: 0.9rem; font-weight: 500;
        cursor: pointer;
    }
    .btn-camera i { font-size: 1.1rem; }
    .foto-mini { width: 48px; height: 48px; object-fit: cover; border-radius: 6px; border: 1px solid #e2e5ea; }

    /* ── Fixed bottom bar ── */
    .fixed-bottom-bar {
        position: fixed; bottom: 0; left: 0; right: 0;
        background: #fff; border-top: 1px solid #e2e5ea;
        padding: 10px 16px; z-index: 1040;
        padding-bottom: max(10px, env(safe-area-inset-bottom));
    }
    /* Space so content doesn't hide behind fixed bar */
    .bottom-spacer { height: 80px; }
</style>
@endpush

@section('content')
<div class="container py-3" style="max-width: 560px;">

    {{-- Header --}}
    <div class="d-flex align-items-center mb-3">
        <a href="{{ route('gate') }}" class="btn btn-light btn-sm border me-2">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h1 class="h5 fw-bold mb-0">Form Buku Tamu</h1>
    </div>

    {{-- Toast container (fixed, tidak geser layout) --}}
    <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3" style="z-index:1080;" id="toastContainer"></div>

    {{-- Form --}}
    <div class="card mb-3">
        <div class="card-body">
            <form id="formTamu" novalidate>

                {{-- Nama Rombongan --}}
                <div class="mb-3">
                    <label for="nama_rombongan" class="form-label">
                        Nama Rombongan <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control" id="nama_rombongan" name="nama_rombongan"
                           placeholder="Contoh: Keluarga Budi" required autofocus>
                </div>

                {{-- Jumlah Orang — radio pills + custom --}}
                <div class="mb-3">
                    <label class="form-label">
                        Jumlah Orang <span class="text-danger">*</span>
                    </label>
                    <div class="radio-pills" id="jumlahPills">
                        @for($i = 1; $i <= 5; $i++)
                        <div>
                            <input type="radio" name="jumlah_radio" id="jml_{{ $i }}" value="{{ $i }}" {{ $i === 1 ? 'checked' : '' }}>
                            <label for="jml_{{ $i }}">{{ $i }}</label>
                        </div>
                        @endfor
                        <div>
                            <input type="radio" name="jumlah_radio" id="jml_custom" value="custom">
                            <label for="jml_custom">Lain</label>
                        </div>
                        <input type="number" class="form-control" id="jumlah_custom"
                               min="1" max="999" placeholder="…" inputmode="numeric" style="display:none;">
                    </div>
                    {{-- Hidden field yang dikirim ke server --}}
                    <input type="hidden" id="jumlah_orang" name="jumlah_orang" value="1">
                </div>

                {{-- Kategori — radio pills with icons, default Undangan --}}
                <div class="mb-3">
                    <label class="form-label">Kategori</label>
                    <div class="radio-pills kat-pills" id="kategoriPills">
                        <div class="kat-keluarga">
                            <input type="radio" name="kategori" id="kat_keluarga" value="Keluarga">
                            <label for="kat_keluarga"><i class="bi bi-house-heart kat-icon"></i> Keluarga</label>
                        </div>
                        <div class="kat-vip">
                            <input type="radio" name="kategori" id="kat_vip" value="VIP">
                            <label for="kat_vip"><i class="bi bi-star kat-icon"></i> VIP</label>
                        </div>
                        <div class="kat-undangan">
                            <input type="radio" name="kategori" id="kat_undangan" value="Undangan" checked>
                            <label for="kat_undangan"><i class="bi bi-envelope-paper kat-icon"></i> Undangan</label>
                        </div>
                        <div class="kat-umum">
                            <input type="radio" name="kategori" id="kat_umum" value="Umum">
                            <label for="kat_umum"><i class="bi bi-people kat-icon"></i> Umum</label>
                        </div>
                    </div>
                </div>

                {{-- Keterangan --}}
                <div class="mb-3">
                    <label for="keterangan" class="form-label">
                        Keterangan <small class="text-muted fw-normal">(opsional)</small>
                    </label>
                    <textarea class="form-control" id="keterangan" name="keterangan" rows="2"
                              placeholder="Catatan tambahan..."></textarea>
                </div>

                {{-- Foto — compact camera button --}}
                <div class="mb-3">
                    <label class="form-label">
                        Foto <small class="text-muted fw-normal">(opsional)</small>
                    </label>
                    <input type="file" id="foto" name="foto" accept="image/*" capture="environment" class="d-none">

                    {{-- State 1: Belum ada foto --}}
                    <div id="fotoEmpty">
                        <label for="foto" class="btn btn-outline-secondary btn-camera mb-0" title="Ambil foto">
                            <i class="bi bi-camera me-1"></i> Ambil Foto
                        </label>
                    </div>

                    {{-- State 2: Sudah ada foto --}}
                    <div id="fotoPreview" class="d-none">
                        <div class="d-inline-flex align-items-center gap-2 p-2 rounded" style="background:#f9fafb; border:1px solid #e2e5ea;">
                            <img id="fotoPreviewImg" src="" class="foto-mini" alt="Preview">
                            <span class="small text-muted" id="fotoName">foto.jpg</span>
                            <button type="button" class="btn btn-sm btn-outline-danger border-0" id="hapusFoto" title="Hapus foto">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>

    {{-- Counter (dipindah ke bawah) --}}
    <div class="card mb-3">
        <div class="card-body text-center py-3">
            <div class="counter-label">Total Hadir</div>
            <div class="counter-value" id="totalHadir">{{ number_format($total, 0, ',', '.') }}</div>
            <div class="counter-label">orang</div>
        </div>
    </div>

    {{-- Recent entries --}}
    <div class="card mb-3" id="recentCard">
        <div class="card-body">
            <h6 class="fw-semibold mb-3">
                <i class="bi bi-clock-history me-1"></i> Entri Terakhir
            </h6>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Nama</th>
                            <th class="text-end">Jml</th>
                        </tr>
                    </thead>
                    <tbody id="recentTable">
                        @forelse($recent as $t)
                        <tr>
                            <td class="text-muted">{{ $t->waktu_datang->format('H:i') }}</td>
                            <td>{{ $t->nama_rombongan }}</td>
                            <td class="text-end fw-semibold">{{ $t->jumlah_orang }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-3">Belum ada data.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Spacer for fixed button --}}
    <div class="bottom-spacer"></div>
</div>

{{-- Fixed Simpan button --}}
<div class="fixed-bottom-bar">
    <div class="container" style="max-width: 560px;">
        <button type="button" class="btn btn-primary btn-lg w-100" id="btnSimpan">
            <i class="bi bi-check-lg me-1"></i> Simpan
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {

    // ── Jumlah orang: auto-set 1, radio pills ──
    function syncJumlah() {
        var val = $('input[name="jumlah_radio"]:checked').val();
        if (val === 'custom') {
            $('#jumlah_custom').show().focus();
            $('#jumlah_orang').val($('#jumlah_custom').val() || 1);
        } else {
            $('#jumlah_custom').hide();
            $('#jumlah_orang').val(val);
        }
    }

    $('input[name="jumlah_radio"]').on('change', syncJumlah);
    $('#jumlah_custom').on('input', function () {
        var v = parseInt($(this).val()) || 1;
        $('#jumlah_orang').val(v);
    });

    // Auto-fill 1 when nama_rombongan gets typed
    $('#nama_rombongan').on('input', function () {
        if ($(this).val().length === 1 && !$('#jumlah_orang').val()) {
            $('input[name="jumlah_radio"][value="1"]').prop('checked', true);
            syncJumlah();
        }
    });

    // ── Photo: camera button ──
    $('#foto').on('change', function () {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#fotoPreviewImg').attr('src', e.target.result);
                $('#fotoName').text(file.name.length > 20 ? file.name.substring(0, 17) + '…' : file.name);
                $('#fotoEmpty').addClass('d-none');
                $('#fotoPreview').removeClass('d-none');
            };
            reader.readAsDataURL(file);
        }
    });

    $('#hapusFoto').on('click', function () {
        $('#foto').val('');
        $('#fotoPreview').addClass('d-none');
        $('#fotoEmpty').removeClass('d-none');
    });

    // ── Submit (triggered by fixed button) ──
    $('#btnSimpan').on('click', function () {
        $('#formTamu').trigger('submit');
    });

    $('#formTamu').on('submit', function (e) {
        e.preventDefault();

        // Validate
        var nama = $('#nama_rombongan').val().trim();
        if (!nama) {
            $('#nama_rombongan').focus().addClass('is-invalid');
            return;
        }
        $('#nama_rombongan').removeClass('is-invalid');

        var $btn = $('#btnSimpan');
        $btn.prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

        var formData = new FormData(this);

        $.ajax({
            url: '{{ route("store") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                if (!res.success) return;

                // Update counter
                $('#totalHadir').text(Number(res.total).toLocaleString('id-ID'));

                // Prepend to recent table
                var waktu = new Date(res.tamu.waktu_datang)
                                .toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                var row = '<tr class="row-flash">' +
                    '<td class="text-muted">' + waktu + '</td>' +
                    '<td>' + $('<span>').text(res.tamu.nama_rombongan).html() + '</td>' +
                    '<td class="text-end fw-semibold">' + res.tamu.jumlah_orang + '</td>' +
                    '</tr>';

                if ($('#recentTable tr td[colspan]').length) {
                    $('#recentTable').empty();
                }
                $('#recentTable').prepend(row);
                while ($('#recentTable tr').length > 5) {
                    $('#recentTable tr:last').remove();
                }

                showToast('success', '<strong>' + $('<span>').text(res.tamu.nama_rombongan).html() +
                    '</strong> — ' + res.tamu.jumlah_orang + ' orang berhasil dicatat.');

                // Reset form — keep kategori default
                $('#formTamu')[0].reset();
                $('#fotoPreview').addClass('d-none');
                $('#fotoEmpty').removeClass('d-none');
                $('#jumlah_custom').hide();
                // Re-check defaults after reset
                $('input[name="jumlah_radio"][value="1"]').prop('checked', true);
                $('input[name="kategori"][value="Undangan"]').prop('checked', true);
                syncJumlah();
                $('#nama_rombongan').focus();
            },
            error: function (xhr) {
                var msg = 'Terjadi kesalahan.';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                }
                showToast('danger', msg);
            },
            complete: function () {
                $btn.prop('disabled', false)
                    .html('<i class="bi bi-check-lg me-1"></i> Simpan');
            }
        });
    });

    function showToast(type, html) {
        var icon = type === 'success' ? 'bi-check-circle-fill text-success' : 'bi-exclamation-circle-fill text-danger';
        var el = $(
            '<div class="toast align-items-center border-0 shadow-sm" role="alert" data-bs-delay="3500">' +
            '  <div class="d-flex">' +
            '    <div class="toast-body d-flex align-items-center gap-2">' +
            '      <i class="bi ' + icon + '"></i> ' + html +
            '    </div>' +
            '    <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>' +
            '  </div>' +
            '</div>'
        );
        $('#toastContainer').append(el);
        var toast = new bootstrap.Toast(el[0]);
        toast.show();
        el.on('hidden.bs.toast', function () { el.remove(); });
    }
});
</script>
@endpush
