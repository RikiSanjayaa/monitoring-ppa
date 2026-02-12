<?php

namespace App\Imports;

use App\Models\Petugas;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PetugasImport implements SkipsEmptyRows, ToCollection, WithHeadingRow
{
    public function __construct(
        private readonly int $satkerId,
    ) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $raw = $row->toArray();

            $nama = trim((string) $this->value($raw, ['nama']));

            if ($nama === '') {
                continue;
            }

            Petugas::query()->updateOrCreate(
                [
                    'satker_id' => $this->satkerId,
                    'nama' => $nama,
                ],
                [
                    'nrp' => $this->nullableString($this->value($raw, ['nrp'])),
                    'pangkat' => $this->nullableString($this->value($raw, ['pangkat'])),
                    'no_hp' => $this->nullableString($this->value($raw, ['no_hp', 'no hp', 'hp'])),
                ],
            );
        }
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

    private function nullableString(mixed $value): ?string
    {
        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }
}
