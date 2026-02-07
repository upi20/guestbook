@extends('layouts.app')

@section('title', 'Form Buku Tamu')

@section('content')
<div class="container py-3" style="max-width: 560px;">

    {{-- Header --}}
    <div class="d-flex align-items-center mb-3">
        <a href="{{ route('gate') }}" class="btn btn-light btn-sm border me-2">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h1 class="h5 fw-bold mb-0">Form Buku Tamu</h1>
    </div>

    {{-- Counter --}}
    <div class="card mb-3">
        <div class="card-body text-center py-3">
            <div class="counter-label">Total Hadir</div>
            <div class="counter-value" id="totalHadir">{{ number_format($total, 0, ',', '.') }}</div>
            <div class="counter-label">orang</div>
        </div>
    </div>

    {{-- Form --}}
    <div class="card mb-3">
        <div class="card-body">
            <form id="formTamu" novalidate>

                <div class="mb-3">
                    <label for="nama_rombongan" class="form-label">
                        Nama Rombongan <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control" id="nama_rombongan" name="nama_rombongan"
                           placeholder="Contoh: Keluarga Budi" required autofocus>
                </div>

                <div class="mb-3">
                    <label for="jumlah_orang" class="form-label">
                        Jumlah Orang <span class="text-danger">*</span>
                    </label>
                    <input type="number" class="form-control" id="jumlah_orang" name="jumlah_orang"
                           min="1" max="999" placeholder="0" required inputmode="numeric"
                           style="font-size: 1.5rem; font-weight: 700; text-align: center; max-width: 140px;">
                </div>

                <div class="mb-3">
                    <label for="kategori" class="form-label">Kategori</label>
                    <select class="form-select" id="kategori" name="kategori">
                        <option value="">— Pilih kategori —</option>
                        @foreach($kategoriList as $kat)
                            <option value="{{ $kat }}">{{ $kat }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="keterangan" class="form-label">
                        Keterangan <small class="text-muted fw-normal">(opsional)</small>
                    </label>
                    <textarea class="form-control" id="keterangan" name="keterangan" rows="2"
                              placeholder="Catatan tambahan..."></textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label">
                        <i class="bi bi-camera me-1"></i>Foto
                        <small class="text-muted fw-normal">(opsional — bisa ambil dari kamera)</small>
                    </label>
                    <input type="file" class="form-control" id="foto" name="foto" accept="image/*">
                    <div id="fotoPreview" class="mt-2" style="display:none;">
                        <img id="fotoPreviewImg" src="" class="foto-preview" alt="Preview foto">
                        <button type="button" class="btn btn-sm btn-outline-secondary mt-1" id="hapusFoto">
                            <i class="bi bi-x-lg"></i> Hapus foto
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100" id="btnSimpan">
                    <i class="bi bi-check-lg me-1"></i> Simpan
                </button>
            </form>
        </div>
    </div>

    {{-- Alert container --}}
    <div id="alertContainer"></div>

    {{-- Recent entries --}}
    <div class="card" id="recentCard">
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

</div>
@endsection

@push('scripts')
<script>
$(function () {

    // ── Photo preview ──
    $('#foto').on('change', function () {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#fotoPreviewImg').attr('src', e.target.result);
                $('#fotoPreview').show();
            };
            reader.readAsDataURL(file);
        }
    });

    $('#hapusFoto').on('click', function () {
        $('#foto').val('');
        $('#fotoPreview').hide();
    });

    // ── Submit form ──
    $('#formTamu').on('submit', function (e) {
        e.preventDefault();

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

                // Remove "belum ada data" row
                if ($('#recentTable tr td[colspan]').length) {
                    $('#recentTable').empty();
                }
                $('#recentTable').prepend(row);

                // Keep max 5 rows
                while ($('#recentTable tr').length > 5) {
                    $('#recentTable tr:last').remove();
                }

                showAlert('success', '<strong>' + $('<span>').text(res.tamu.nama_rombongan).html() +
                    '</strong> — ' + res.tamu.jumlah_orang + ' orang berhasil dicatat.');

                // Reset form
                $('#formTamu')[0].reset();
                $('#fotoPreview').hide();
                $('#nama_rombongan').focus();
            },
            error: function (xhr) {
                var msg = 'Terjadi kesalahan.';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                }
                showAlert('danger', msg);
            },
            complete: function () {
                $btn.prop('disabled', false)
                    .html('<i class="bi bi-check-lg me-1"></i> Simpan');
            }
        });
    });

    function showAlert(type, html) {
        var el = $(
            '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
            html +
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
            '</div>'
        );
        $('#alertContainer').html(el);
        setTimeout(function () { el.alert('close'); }, 4000);
    }
});
</script>
@endpush
