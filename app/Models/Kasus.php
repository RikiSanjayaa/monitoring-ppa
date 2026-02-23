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
        'perkara_id',
        'dokumen_status',
        'kronologi_kejadian',
        'laporan_polisi_file',
        'tindak_pidana_pasal',
        'hubungan_pelaku_dengan_korban',
        'proses_pidana',
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
            'dokumen_status' => DokumenStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new SatkerScope);

        static::creating(function (Kasus $kasus): void {
            static::ensureUniqueNomorLp($kasus);

            if (! $kasus->created_by && Auth::id()) {
                $kasus->created_by = Auth::id();
            }
        });

        static::updating(function (Kasus $kasus): void {
            if ($kasus->isDirty(['satker_id', 'nomor_lp'])) {
                static::ensureUniqueNomorLp($kasus);
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

    public function korbans(): HasMany
    {
        return $this->hasMany(KasusKorban::class)
            ->orderBy('id');
    }

    public function tersangkas(): HasMany
    {
        return $this->hasMany(KasusPelaku::class)
            ->orderBy('id');
    }

    public function pelakus(): HasMany
    {
        return $this->tersangkas();
    }

    public function saksis(): HasMany
    {
        return $this->hasMany(KasusSaksi::class)
            ->orderBy('id');
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

    public function korbanList(): string
    {
        $names = $this->korbans->pluck('nama')->filter()->values();

        if ($names->isNotEmpty()) {
            return $names->join(', ');
        }

        return '-';
    }

    public function tersangkaList(): string
    {
        $names = $this->tersangkas->pluck('nama')->filter()->values();

        if ($names->isNotEmpty()) {
            return $names->join(', ');
        }

        return '-';
    }

    public function pelakuList(): string
    {
        return $this->tersangkaList();
    }

    private static function ensureUniqueNomorLp(Kasus $kasus): void
    {
        if (! $kasus->satker_id) {
            return;
        }

        $base = trim((string) $kasus->nomor_lp);

        if ($base === '') {
            $base = sprintf('LP/SATKER-%d/%s', $kasus->satker_id, now()->format('YmdHis'));
        }

        $candidate = $base;
        $counter = 2;

        while (static::withoutGlobalScopes()
            ->where('satker_id', $kasus->satker_id)
            ->where('nomor_lp', $candidate)
            ->when($kasus->exists, fn (Builder $query): Builder => $query->whereKeyNot($kasus->getKey()))
            ->exists()) {
            $candidate = sprintf('%s (%d)', $base, $counter);
            $counter++;
        }

        $kasus->nomor_lp = $candidate;
    }
}
