<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Satker extends Model
{
    use HasFactory;

    protected $table = 'satkers';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nama',
        'tipe',
        'kode',
        'urutan',
    ];

    protected $casts = [
        'urutan' => 'integer',
    ];

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderByRaw('CASE WHEN urutan IS NULL THEN 1 ELSE 0 END')
            ->orderBy('urutan')
            ->orderBy('kode');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function petugas(): HasMany
    {
        return $this->hasMany(Petugas::class);
    }

    public function kasus(): HasMany
    {
        return $this->hasMany(Kasus::class);
    }
}
