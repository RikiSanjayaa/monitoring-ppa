<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KasusKorban extends Model
{
    use HasFactory;

    protected $table = 'kasus_korbans';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'kasus_id',
        'nama',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'hp',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
        ];
    }

    public function kasus(): BelongsTo
    {
        return $this->belongsTo(Kasus::class);
    }
}
