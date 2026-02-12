<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengaturanLaporan extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'satker_id',
        'kop_baris_1',
        'kop_baris_2',
        'kop_baris_3',
        'judul_utama',
        'judul_rekap',
        'ttd_baris_1',
        'ttd_baris_2',
        'ttd_nama',
        'ttd_pangkat_nrp',
    ];

    public function satker(): BelongsTo
    {
        return $this->belongsTo(Satker::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
