<?php

namespace App\Models;

use App\Enums\DokumenStatus;
use App\Models\Scopes\SatkerScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;

class Kasus extends Model
{
    use HasFactory;

    protected $table = 'kasus';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'satker_id',
        'nomor_lp',
        'tanggal_lp',
        'nama_korban',
        'tempat_lahir_korban',
        'tanggal_lahir_korban',
        'alamat_korban',
        'hp_korban',
        'perkara_id',
        'dokumen_status',
        'penyelesaian_id',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_lp' => 'date',
            'tanggal_lahir_korban' => 'date',
            'dokumen_status' => DokumenStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new SatkerScope());

        static::creating(function (Kasus $kasus): void {
            if (! $kasus->created_by && Auth::id()) {
                $kasus->created_by = Auth::id();
            }
        });
    }

    public function satker(): BelongsTo
    {
        return $this->belongsTo(Satker::class);
    }

    public function perkara(): BelongsTo
    {
        return $this->belongsTo(Perkara::class);
    }

    public function penyelesaian(): BelongsTo
    {
        return $this->belongsTo(Penyelesaian::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function petugas(): BelongsToMany
    {
        return $this->belongsToMany(Petugas::class, 'kasus_petugas')
            ->withTimestamps();
    }

    public function rtls(): HasMany
    {
        return $this->hasMany(Rtl::class)
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc');
    }

    public function latestRtl(): HasOne
    {
        return $this->hasOne(Rtl::class)->latestOfMany('tanggal');
    }

    public function scopeForSatker(Builder $query, ?int $satkerId): Builder
    {
        if (! $satkerId) {
            return $query;
        }

        return $query->where('satker_id', $satkerId);
    }
}
