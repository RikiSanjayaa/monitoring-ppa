<?php

namespace App\Imports;

use App\Models\Kasus;
use App\Models\Penyelesaian;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PenyelesaianImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public function __construct(
        private readonly int $satkerId,
    ) {
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $raw = $row->toArray();

            $nomorLp = trim((string) $this->value($raw, ['nomor_lp', 'nomor lp', 'nomorlp']));
            $penyelesaianNama = trim((string) $this->value($raw, ['penyelesaian']));

            if ($nomorLp === '' || $penyelesaianNama === '') {
                continue;
            }

            $penyelesaian = Penyelesaian::query()->firstOrCreate(
                ['nama' => $penyelesaianNama],
                ['is_active' => true],
            );

            Kasus::withoutGlobalScopes()
                ->where('satker_id', $this->satkerId)
                ->where('nomor_lp', $nomorLp)
                ->update(['penyelesaian_id' => $penyelesaian->id]);
        }
    }

    /**
     * @param array<string, mixed> $row
     * @param array<int, string> $keys
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
}
