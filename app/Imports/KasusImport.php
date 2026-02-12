<?php

namespace App\Imports;

use App\Enums\DokumenStatus;
use App\Models\Kasus;
use App\Models\Penyelesaian;
use App\Models\Perkara;
use App\Models\Petugas;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class KasusImport implements SkipsEmptyRows, ToCollection, WithHeadingRow
{
    public function __construct(
        private readonly int $satkerId,
        private readonly int $createdBy,
    ) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $raw = $row->toArray();

            $nomorLp = trim((string) $this->value($raw, ['nomor_lp', 'nomor lp', 'nomorlp']));

            if ($nomorLp === '') {
                continue;
            }

            $tanggalLp = $this->parseDate($this->value($raw, ['tanggal_lp', 'tgl_lp', 'tgl lp', 'tanggal lp']));

            if (! $tanggalLp) {
                continue;
            }

            $perkaraNama = (string) $this->value($raw, ['jenis_kasus', 'jenis kasus', 'perkara']);
            $perkara = Perkara::query()->firstOrCreate(
                ['nama' => $perkaraNama !== '' ? $perkaraNama : 'Lainnya'],
                ['is_active' => true],
            );

            $dokumenStatus = $this->normalizeDokumenStatus($this->value($raw, ['dokumen_giat', 'dokumen/giat', 'dokumen', 'giat', 'dokumen_status']));
            $penyelesaianNama = (string) $this->value($raw, ['penyelesaian']);

            $penyelesaian = $penyelesaianNama !== ''
                ? Penyelesaian::query()->firstOrCreate(
                    ['nama' => $penyelesaianNama],
                    ['is_active' => true],
                )
                : null;

            [$tempatLahir, $tanggalLahir] = $this->parseTtl(
                $this->value($raw, ['ttl']),
                $this->value($raw, ['tempat_lahir_korban', 'tempat lahir', 'tempat_lahir']),
                $this->parseDate($this->value($raw, ['tanggal_lahir_korban', 'tanggal lahir', 'tgl_lahir'])),
            );

            $korbanNames = $this->parseList((string) $this->value($raw, ['daftar_korban', 'daftar korban']));
            if ($korbanNames === []) {
                $singleKorban = trim((string) $this->value($raw, ['nama_korban', 'korban']));
                if ($singleKorban !== '') {
                    $korbanNames = [$singleKorban];
                }
            }

            $tersangkaNames = $this->parseList((string) $this->value($raw, ['daftar_tersangka', 'daftar tersangka', 'daftar_pelaku', 'daftar pelaku']));
            if ($tersangkaNames === []) {
                $singleTersangka = trim((string) $this->value($raw, ['nama_tersangka', 'nama tersangka', 'nama_pelaku', 'nama pelaku']));
                if ($singleTersangka !== '') {
                    $tersangkaNames = [$singleTersangka];
                }
            }

            $namaKorbanUtama = $korbanNames[0] ?? '-';
            $namaTersangkaUtama = $tersangkaNames[0] ?? null;

            $kasus = Kasus::withoutGlobalScopes()->updateOrCreate(
                [
                    'satker_id' => $this->satkerId,
                    'nomor_lp' => $nomorLp,
                ],
                [
                    'tanggal_lp' => $tanggalLp,
                    'nama_korban' => $namaKorbanUtama,
                    'tempat_lahir_korban' => $tempatLahir,
                    'tanggal_lahir_korban' => $tanggalLahir,
                    'alamat_korban' => (string) $this->value($raw, ['alamat_korban', 'alamat']),
                    'hp_korban' => (string) $this->value($raw, ['hp_korban', 'no_hp_korban', 'no hp', 'hp']),
                    'perkara_id' => $perkara->id,
                    'tindak_pidana_pasal' => (string) $this->value($raw, ['tindak_pidana_pasal', 'tindak pidana/pasal', 'pasal']),
                    'hubungan_pelaku_dengan_korban' => (string) $this->value($raw, ['hubungan_tersangka_dengan_korban', 'hubungan tersangka dengan korban', 'hubungan_pelaku_dengan_korban', 'hubungan pelaku dengan korban']),
                    'proses_pidana' => (string) $this->value($raw, ['proses_pidana', 'proses pidana']),
                    'kronologi_kejadian' => (string) $this->value($raw, ['kronologi_kejadian', 'kronologi kejadian']),
                    'laporan_polisi' => (string) $this->value($raw, ['laporan_polisi', 'laporan polisi']),
                    'nama_pelaku' => $namaTersangkaUtama,
                    'tempat_lahir_pelaku' => (string) $this->value($raw, ['tempat_lahir_tersangka', 'tempat lahir tersangka', 'tempat_lahir_pelaku', 'tempat lahir pelaku']),
                    'tanggal_lahir_pelaku' => $this->parseDate($this->value($raw, ['tanggal_lahir_tersangka', 'tanggal lahir tersangka', 'tanggal_lahir_pelaku', 'tanggal lahir pelaku'])),
                    'alamat_pelaku' => (string) $this->value($raw, ['alamat_tersangka', 'alamat tersangka', 'alamat_pelaku', 'alamat pelaku']),
                    'hp_pelaku' => (string) $this->value($raw, ['hp_tersangka', 'hp tersangka', 'no_hp_tersangka', 'no hp tersangka', 'hp_pelaku', 'hp pelaku', 'no_hp_pelaku', 'no hp pelaku']),
                    'dokumen_status' => $dokumenStatus,
                    'penyelesaian_id' => $penyelesaian?->id,
                ],
            );

            if (! $kasus->created_by) {
                $kasus->created_by = $this->createdBy;
                $kasus->save();
            }

            $petugasNames = $this->parsePetugas((string) $this->value($raw, ['petugas']));

            if ($petugasNames !== []) {
                $petugasIds = collect($petugasNames)
                    ->map(function (string $nama): int {
                        return Petugas::query()->firstOrCreate(
                            [
                                'satker_id' => $this->satkerId,
                                'nama' => $nama,
                            ],
                            [
                                'nrp' => null,
                                'pangkat' => null,
                                'no_hp' => null,
                            ],
                        )->id;
                    })
                    ->values()
                    ->all();

                $kasus->petugas()->sync($petugasIds);
            }

            $kasus->korbans()->delete();
            if ($korbanNames !== []) {
                $kasus->korbans()->createMany(
                    collect($korbanNames)
                        ->map(function (string $nama, int $index) use ($tempatLahir, $tanggalLahir, $raw): array {
                            return [
                                'nama' => $nama,
                                'tempat_lahir' => $index === 0 ? $tempatLahir : null,
                                'tanggal_lahir' => $index === 0 ? $tanggalLahir : null,
                                'alamat' => $index === 0 ? (string) $this->value($raw, ['alamat_korban', 'alamat']) : null,
                                'hp' => $index === 0 ? (string) $this->value($raw, ['hp_korban', 'no_hp_korban', 'no hp', 'hp']) : null,
                            ];
                        })
                        ->all(),
                );
            }

            $kasus->tersangkas()->delete();
            if ($tersangkaNames !== []) {
                $kasus->tersangkas()->createMany(
                    collect($tersangkaNames)
                        ->map(function (string $nama, int $index) use ($raw): array {
                            return [
                                'nama' => $nama,
                                'tempat_lahir' => $index === 0 ? (string) $this->value($raw, ['tempat_lahir_tersangka', 'tempat lahir tersangka', 'tempat_lahir_pelaku', 'tempat lahir pelaku']) : null,
                                'tanggal_lahir' => $index === 0 ? $this->parseDate($this->value($raw, ['tanggal_lahir_tersangka', 'tanggal lahir tersangka', 'tanggal_lahir_pelaku', 'tanggal lahir pelaku'])) : null,
                                'alamat' => $index === 0 ? (string) $this->value($raw, ['alamat_tersangka', 'alamat tersangka', 'alamat_pelaku', 'alamat pelaku']) : null,
                                'hp' => $index === 0 ? (string) $this->value($raw, ['hp_tersangka', 'hp tersangka', 'no_hp_tersangka', 'no hp tersangka', 'hp_pelaku', 'hp pelaku', 'no_hp_pelaku', 'no hp pelaku']) : null,
                            ];
                        })
                        ->all(),
                );
            }

            $saksiNames = $this->parseList((string) $this->value($raw, ['daftar_saksi', 'daftar saksi', 'saksi']));

            if ($saksiNames === []) {
                $singleSaksi = trim((string) $this->value($raw, ['nama_saksi', 'nama saksi']));

                if ($singleSaksi !== '') {
                    $saksiNames = [$singleSaksi];
                }
            }

            $kasus->saksis()->delete();

            if ($saksiNames !== []) {
                $kasus->saksis()->createMany(
                    collect($saksiNames)
                        ->map(fn (string $nama): array => [
                            'nama' => $nama,
                        ])
                        ->all(),
                );
            }
        }
    }

    private function normalizeDokumenStatus(mixed $value): string
    {
        $value = strtolower(trim((string) $value));

        return $value === DokumenStatus::Sidik->value ? DokumenStatus::Sidik->value : DokumenStatus::Lidik->value;
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<int, string>  $keys
     */
    private function value(array $row, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row) && $row[$key] !== null) {
                return $row[$key];
            }
        }

        return null;
    }

    /**
     * @return array{0: ?string, 1: ?string}
     */
    private function parseTtl(mixed $ttl, mixed $tempatLahir, ?string $tanggalLahir): array
    {
        $tempat = $tempatLahir ? trim((string) $tempatLahir) : null;
        $tanggal = $tanggalLahir;

        if ($ttl) {
            $segments = array_map('trim', explode(',', (string) $ttl, 2));

            if (! $tempat && isset($segments[0])) {
                $tempat = $segments[0] !== '' ? $segments[0] : null;
            }

            if (! $tanggal && isset($segments[1])) {
                $tanggal = $this->parseDate($segments[1]);
            }
        }

        return [$tempat, $tanggal];
    }

    /**
     * @return array<int, string>
     */
    private function parsePetugas(string $value): array
    {
        return $this->parseList($value);
    }

    /**
     * @return array<int, string>
     */
    private function parseList(string $value): array
    {
        if (trim($value) === '') {
            return [];
        }

        $parts = preg_split('/[,;]+/', $value) ?: [];

        return collect($parts)
            ->map(fn (string $item): string => trim($item))
            ->filter(fn (string $item): bool => $item !== '')
            ->unique()
            ->values()
            ->all();
    }

    private function parseDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value))->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }

        try {
            return Carbon::parse((string) $value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}
