<?php

namespace App\Http\Controllers;

use App\Events\TamuBaru;
use App\Models\Tamu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuestbookController extends Controller
{
    private array $kategoriList = ['Keluarga', 'VIP', 'Undangan', 'Umum'];

    public function gate(): View
    {
        $total = (int) Tamu::sum('jumlah_orang');
        $totalRombongan = Tamu::count();

        return view('gate', compact('total', 'totalRombongan'));
    }

    public function form(): View
    {
        $total = (int) Tamu::sum('jumlah_orang');
        $kategoriList = $this->kategoriList;
        $recent = Tamu::orderByDesc('waktu_datang')->limit(5)->get();

        return view('form', compact('total', 'kategoriList', 'recent'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama_rombongan' => 'required|string|max:255',
            'jumlah_orang'   => 'required|integer|min:1|max:999',
            'kategori'       => 'nullable|string|max:100',
            'keterangan'     => 'nullable|string|max:500',
            'foto'           => 'nullable|image|max:5120',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('foto-tamu', 'public');
        }

        $tamu = Tamu::create([
            'nama_rombongan' => $validated['nama_rombongan'],
            'jumlah_orang'   => $validated['jumlah_orang'],
            'kategori'       => $validated['kategori'] ?? null,
            'keterangan'     => $validated['keterangan'] ?? null,
            'foto'           => $fotoPath,
            'waktu_datang'   => now(),
        ]);

        $total          = (int) Tamu::sum('jumlah_orang');
        $totalRombongan = Tamu::count();
        $perKategori    = $this->perKategori();

        try {
            broadcast(new TamuBaru(
                $tamu->toArray(),
                $total,
                $totalRombongan,
                $perKategori,
            ));
        } catch (\Exception $e) {
            // Broadcasting belum dikonfigurasi — abaikan
        }

        return response()->json([
            'success'         => true,
            'message'         => 'Data tamu berhasil disimpan.',
            'tamu'            => $tamu,
            'total'           => $total,
            'total_rombongan' => $totalRombongan,
        ]);
    }

    public function monitoring(): View
    {
        return view('monitoring');
    }

    public function data(): JsonResponse
    {
        $total          = (int) Tamu::sum('jumlah_orang');
        $totalRombongan = Tamu::count();
        $perKategori    = $this->perKategori();
        $recent         = Tamu::orderByDesc('waktu_datang')->limit(20)->get();

        return response()->json([
            'total'           => $total,
            'total_rombongan' => $totalRombongan,
            'per_kategori'    => $perKategori,
            'recent'          => $recent,
        ]);
    }

    private function perKategori(): array
    {
        return Tamu::selectRaw('kategori, SUM(jumlah_orang) as total')
            ->groupBy('kategori')
            ->get()
            ->mapWithKeys(fn ($item) => [($item->kategori ?? 'Lainnya') => (int) $item->total])
            ->toArray();
    }
}
