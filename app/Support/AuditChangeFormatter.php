<?php

namespace App\Support;

use App\Enums\DokumenStatus;
use App\Models\Kasus;
use App\Models\KasusKorban;
use App\Models\KasusPelaku;
use App\Models\KasusSaksi;
use App\Models\Penyelesaian;
use App\Models\Perkara;
use App\Models\Petugas;
use App\Models\Rtl;
use App\Models\Satker;
use Illuminate\Database\Eloquent\Model;

class AuditChangeFormatter
{
    public static function format(Model $model, array $changes): array
    {
        $original = $model->getOriginal();

        return collect($changes)
            ->mapWithKeys(function ($newValue, string $field) use ($model, $original): array {
                $label = self::label($model, $field);

                return [
                    $label => [
                        'old' => self::displayValue($field, $original[$field] ?? null),
                        'new' => self::displayValue($field, $newValue),
                    ],
                ];
            })
            ->all();
    }

    private static function label(Model $model, string $field): string
    {
        $labels = match (true) {
            $model instanceof Kasus => [
                'satker_id' => 'Satker',
                'nomor_lp' => 'Nomor LP',
                'tanggal_lp' => 'Tanggal LP',
                'perkara_id' => 'Jenis Kasus',
                'dokumen_status' => 'Status Dokumen',
                'penyelesaian_id' => 'Penyelesaian',
                'kronologi_kejadian' => 'Kronologi Kejadian',
                'tindak_pidana_pasal' => 'Tindak Pidana/Pasal',
                'hubungan_pelaku_dengan_korban' => 'Hub. Tersangka Dengan Korban',
                'proses_pidana' => 'Proses Pidana',
            ],
            $model instanceof Petugas => [
                'satker_id' => 'Satker',
                'nama' => 'Nama',
                'nrp' => 'NRP',
                'pangkat' => 'Pangkat',
                'no_hp' => 'No HP',
            ],
            $model instanceof Rtl => [
                'kasus_id' => 'Kasus',
                'tanggal' => 'Tanggal RTL',
                'keterangan' => 'Keterangan RTL',
            ],
            $model instanceof KasusKorban => [
                'kasus_id' => 'Kasus',
                'nama' => 'Nama Korban',
                'tempat_lahir' => 'Tempat Lahir',
                'tanggal_lahir' => 'Tanggal Lahir',
                'alamat' => 'Alamat',
                'hp' => 'No HP',
            ],
            $model instanceof KasusPelaku => [
                'kasus_id' => 'Kasus',
                'nama' => 'Nama Tersangka',
                'tempat_lahir' => 'Tempat Lahir',
                'tanggal_lahir' => 'Tanggal Lahir',
                'alamat' => 'Alamat',
                'hp' => 'No HP',
            ],
            $model instanceof KasusSaksi => [
                'kasus_id' => 'Kasus',
                'nama' => 'Nama Saksi',
                'tempat_lahir' => 'Tempat Lahir',
                'tanggal_lahir' => 'Tanggal Lahir',
                'alamat' => 'Alamat',
                'hp' => 'No HP',
            ],
            default => [],
        };

        return $labels[$field] ?? str_replace('_', ' ', ucfirst($field));
    }

    private static function displayValue(string $field, mixed $value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        if ($field === 'satker_id') {
            return Satker::query()->find($value)?->nama ?? (string) $value;
        }

        if ($field === 'perkara_id') {
            return Perkara::query()->find($value)?->nama ?? (string) $value;
        }

        if ($field === 'penyelesaian_id') {
            return Penyelesaian::query()->find($value)?->nama ?? (string) $value;
        }

        if ($field === 'kasus_id') {
            return Kasus::query()->withoutGlobalScopes()->find($value)?->nomor_lp ?? '#'.(string) $value;
        }

        if ($field === 'dokumen_status') {
            return DokumenStatus::options()[(string) $value] ?? strtoupper((string) $value);
        }

        if (in_array($field, ['tanggal_lp', 'tanggal', 'tanggal_lahir'], true) && strtotime((string) $value) !== false) {
            return date('d-m-Y', strtotime((string) $value));
        }

        if (is_bool($value)) {
            return $value ? 'Ya' : 'Tidak';
        }

        return (string) $value;
    }
}
