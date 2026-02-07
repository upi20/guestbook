@extends('layouts.app')

@section('title', 'Buku Tamu')

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center p-3">
    <div class="text-center" style="max-width: 420px; width: 100%;">

        {{-- Header --}}
        <div class="mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-white rounded-circle mb-3"
                 style="width: 64px; height: 64px; border: 1px solid #e2e5ea;">
                <i class="bi bi-journal-text" style="font-size: 1.75rem; color: #2563eb;"></i>
            </div>
            <h1 class="h3 fw-bold mb-1">Buku Tamu</h1>
            <p class="text-muted mb-0">Sistem pencatatan tamu acara</p>
        </div>

        {{-- Menu --}}
        <div class="d-grid gap-3 mb-4">
            <a href="{{ route('form') }}" class="card text-decoration-none">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="rounded d-flex align-items-center justify-content-center me-3"
                         style="width: 48px; height: 48px; background-color: #eff6ff; flex-shrink: 0;">
                        <i class="bi bi-pencil-square" style="font-size: 1.25rem; color: #2563eb;"></i>
                    </div>
                    <div class="text-start flex-grow-1">
                        <div class="fw-semibold" style="color: #111827;">Form Buku Tamu</div>
                        <small class="text-muted">Catat tamu yang datang</small>
                    </div>
                    <i class="bi bi-chevron-right text-muted"></i>
                </div>
            </a>

            <a href="{{ route('monitoring') }}" class="card text-decoration-none">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="rounded d-flex align-items-center justify-content-center me-3"
                         style="width: 48px; height: 48px; background-color: #f0fdf4; flex-shrink: 0;">
                        <i class="bi bi-bar-chart-line" style="font-size: 1.25rem; color: #16a34a;"></i>
                    </div>
                    <div class="text-start flex-grow-1">
                        <div class="fw-semibold" style="color: #111827;">Monitoring</div>
                        <small class="text-muted">Pantau jumlah tamu realtime</small>
                    </div>
                    <i class="bi bi-chevron-right text-muted"></i>
                </div>
            </a>
        </div>

        {{-- Counter --}}
        @if($total > 0)
        <div class="border-top pt-3">
            <small class="text-muted d-block mb-1">Total hadir saat ini</small>
            <span class="fw-bold fs-3">{{ number_format($total, 0, ',', '.') }}</span>
            <small class="text-muted"> orang</small>
            <small class="text-muted d-block mt-1">{{ $totalRombongan }} rombongan</small>
        </div>
        @endif

    </div>
</div>
@endsection
