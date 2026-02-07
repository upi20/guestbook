<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tamu extends Model
{
    protected $table = 'tamu';

    protected $fillable = [
        'nama_rombongan',
        'jumlah_orang',
        'kategori',
        'keterangan',
        'foto',
        'waktu_datang',
    ];

    protected $casts = [
        'waktu_datang' => 'datetime',
        'jumlah_orang' => 'integer',
    ];

    protected $appends = ['foto_url'];

    public function getFotoUrlAttribute(): ?string
    {
        return $this->foto ? asset($this->foto) : null;
    }
}
