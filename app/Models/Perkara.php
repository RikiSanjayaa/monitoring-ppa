<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Perkara extends Model
{
    use HasFactory;

    protected $table = 'perkaras';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nama',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function kasus(): HasMany
    {
        return $this->hasMany(Kasus::class);
    }
}
