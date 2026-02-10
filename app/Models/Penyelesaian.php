<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Penyelesaian extends Model
{
    use HasFactory;

    protected $table = 'penyelesaians';

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
