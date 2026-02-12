<?php

namespace App\Support;

use App\Models\PengaturanLaporan;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ExportDocumentTemplate
{
    /**
     * @var array<string, PengaturanLaporan|null>
     */
    private static array $settingsCache = [];

    /**
     * @return array<int, string>
     */
    public static function kopSuratLines(?int $userId = null, ?int $satkerId = null): array
    {
        $settings = self::settings($userId, $satkerId);

        if ($settings) {
            return [
                $settings->kop_baris_1,
                $settings->kop_baris_2,
                $settings->kop_baris_3,
            ];
        }

        return [
            'KEPOLISIAN NEGARA REPUBLIK INDONESIA',
            'DAERAH NUSA TENGGARA BARAT',
            'DIREKTORAT RESERSE PPA DAN PPO',
        ];
    }

    public static function mainTitle(?int $userId = null, ?int $satkerId = null): string
    {
        return self::withCurrentPeriod('REKAP DATA PENANGANAN KASUS BERDASARKAN JENIS KASUS BULAN JANUARI TH 2026 JAJARAN POLDA NTB');
    }

    public static function recapTitle(?int $userId = null, ?int $satkerId = null): string
    {
        return self::withCurrentPeriod('REKAP DATA ANGKA PENANGANAN KASUS BERDASARKAN JENIS KASUS BULAN JANUARI TH 2026 JAJARAN POLDA NTB');
    }

    /**
     * @param  Collection<int, \App\Models\Kasus>  $records
     * @return array{main: string, recap: string}
     */
    public static function automaticTitles(Collection $records, ?int $userId = null, ?int $satkerId = null): array
    {
        Carbon::setLocale('id');

        $satkerPart = self::satkerTitlePart($records, $satkerId);
        $subjectPart = self::subjectTitlePart($records);

        $month = strtoupper(now()->translatedFormat('F'));
        $year = (string) now()->year;

        $tanggalSample = $records
            ->pluck('tanggal_lp')
            ->filter()
            ->first();

        if ($tanggalSample instanceof Carbon) {
            $month = strtoupper($tanggalSample->translatedFormat('F'));
            $year = (string) $tanggalSample->year;
        }

        return [
            'main' => sprintf('REKAP DATA PENANGANAN %s BULAN %s TH %s %s', $subjectPart, $month, $year, $satkerPart),
            'recap' => sprintf('REKAP DATA ANGKA PENANGANAN %s BULAN %s TH %s %s', $subjectPart, $month, $year, $satkerPart),
        ];
    }

    /**
     * @return array{line1: string, line2: string, name: string, rank: string}
     */
    public static function signatureBlock(?int $userId = null, ?int $satkerId = null): array
    {
        $settings = self::settings($userId, $satkerId);

        if ($settings) {
            return [
                'line1' => $settings->ttd_baris_1,
                'line2' => $settings->ttd_baris_2,
                'name' => $settings->ttd_nama,
                'rank' => $settings->ttd_pangkat_nrp,
            ];
        }

        return [
            'line1' => 'An. KEPALA KEPOLISIAN DAERAH NUSA TENGGARA BARAT',
            'line2' => 'DIRRES PPA DAN PPO POLDA NTB',
            'name' => 'BAMBANG PAMUNGKAS,S.I.K.,M.M.',
            'rank' => 'KOMBESPOL NRP 12345678',
        ];
    }

    /**
     * @return array{0: string, 1: string}
     */
    public static function clipLabels(): array
    {
        return ['PARAF', 'TTD'];
    }

    private static function settings(?int $userId, ?int $satkerId): ?PengaturanLaporan
    {
        $key = (string) ($userId ?? 0).':'.(string) ($satkerId ?? 0);

        if (! array_key_exists($key, self::$settingsCache)) {
            $query = PengaturanLaporan::query();

            if ($userId) {
                $query->where('user_id', $userId);
            } elseif ($satkerId) {
                $query->whereNull('user_id')->where('satker_id', $satkerId);
            } else {
                $query->whereNull('user_id')->whereNull('satker_id');
            }

            self::$settingsCache[$key] = $query->first();
        }

        return self::$settingsCache[$key];
    }

    private static function withCurrentPeriod(string $value): string
    {
        Carbon::setLocale('id');

        $month = strtoupper(now()->translatedFormat('F'));
        $year = (string) now()->year;

        $replaced = preg_replace('/BULAN\s+.*?\s+TH\s+\d{4}/iu', "BULAN {$month} TH {$year}", $value);

        return is_string($replaced) && $replaced !== '' ? $replaced : $value;
    }

    /**
     * @param  Collection<int, \App\Models\Kasus>  $records
     */
    private static function satkerTitlePart(Collection $records, ?int $satkerId): string
    {
        $satkerNames = $records->pluck('satker.nama')->filter()->unique()->values();

        if ($satkerNames->count() === 1) {
            return strtoupper((string) $satkerNames->first());
        }

        return 'JAJARAN POLDA NTB';
    }

    /**
     * @param  Collection<int, \App\Models\Kasus>  $records
     */
    private static function subjectTitlePart(Collection $records): string
    {
        $jenis = $records->pluck('perkara.nama')->filter()->unique()->values();

        if ($jenis->count() === 1) {
            return 'KASUS '.strtoupper((string) $jenis->first());
        }

        return 'KASUS BERDASARKAN JENIS KASUS';
    }
}
