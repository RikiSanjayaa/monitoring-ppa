<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rtl extends Model
{
    use HasFactory;

    protected $table = 'rtls';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'kasus_id',
        'tanggal',
        'keterangan',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function kasus(): BelongsTo
    {
        return $this->belongsTo(Kasus::class);
    }
}
