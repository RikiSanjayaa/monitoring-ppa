<?php

namespace App\Observers;

use App\Models\Kasus;
use App\Models\KasusKorban;
use App\Support\AuditChangeFormatter;
use App\Support\AuditLogger;
use Illuminate\Support\Arr;

class KasusKorbanObserver
{
    public function created(KasusKorban $korban): void
    {
        $kasus = $this->resolveKasus($korban);

        AuditLogger::log(
            module: 'kasus_korban',
            action: 'create',
            summary: sprintf('Data korban pada kasus %s ditambahkan: %s.', $kasus?->nomor_lp ?? '#'.$korban->kasus_id, $korban->nama),
            auditable: $korban,
            satkerId: $kasus?->satker_id,
        );
    }

    public function updated(KasusKorban $korban): void
    {
        $changes = Arr::except($korban->getChanges(), ['updated_at']);

        if ($changes === []) {
            return;
        }

        $kasus = $this->resolveKasus($korban);

        AuditLogger::log(
            module: 'kasus_korban',
            action: 'update',
            summary: sprintf('Data korban pada kasus %s diperbarui: %s.', $kasus?->nomor_lp ?? '#'.$korban->kasus_id, $korban->nama),
            auditable: $korban,
            satkerId: $kasus?->satker_id,
            changes: AuditChangeFormatter::format($korban, $changes),
        );
    }

    public function deleted(KasusKorban $korban): void
    {
        $kasus = $this->resolveKasus($korban);

        AuditLogger::log(
            module: 'kasus_korban',
            action: 'delete',
            summary: sprintf('Data korban pada kasus %s dihapus: %s.', $kasus?->nomor_lp ?? '#'.$korban->kasus_id, $korban->nama),
            auditable: null,
            satkerId: $kasus?->satker_id,
        );
    }

    private function resolveKasus(KasusKorban $korban): ?Kasus
    {
        return $korban->kasus()->withoutGlobalScopes()->first();
    }
}
