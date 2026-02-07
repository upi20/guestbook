<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TamuBaru implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $tamu;
    public int $total;
    public int $totalRombongan;
    public array $perKategori;

    public function __construct(array $tamu, int $total, int $totalRombongan, array $perKategori)
    {
        $this->tamu = $tamu;
        $this->total = $total;
        $this->totalRombongan = $totalRombongan;
        $this->perKategori = $perKategori;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('guestbook');
    }

    public function broadcastAs(): string
    {
        return 'tamu.baru';
    }
}
