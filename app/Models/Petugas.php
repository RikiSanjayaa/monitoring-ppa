<?php

namespace App\Models;

use App\Models\Scopes\SatkerScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Petugas extends Model
{
    use HasFactory;

    protected $table = 'petugas';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'satker_id',
        'nama',
        'nrp',
        'pangkat',
        'no_hp',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new SatkerScope);
    }

    public function satker(): BelongsTo
    {
        return $this->belongsTo(Satker::class);
    }

    public function kasus(): BelongsToMany
    {
        return $this->belongsToMany(Kasus::class, 'kasus_petugas')
            ->withTimestamps();
    }

    public function scopeForSatker(Builder $query, ?int $satkerId): Builder
    {
        if (! $satkerId) {
            return $query;
        }

        return $query->where('satker_id', $satkerId);
    }
}
