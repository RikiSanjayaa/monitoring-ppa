<?php

namespace App\Observers;

use App\Models\Petugas;
use App\Support\AuditChangeFormatter;
use App\Support\AuditLogger;
use Illuminate\Support\Arr;

class PetugasObserver
{
    public function created(Petugas $petugas): void
    {
        AuditLogger::log(
            module: 'petugas',
            action: 'create',
            summary: sprintf('Petugas %s ditambahkan.', $petugas->nama),
            auditable: $petugas,
            satkerId: $petugas->satker_id,
        );
    }

    public function updated(Petugas $petugas): void
    {
        $changes = Arr::except($petugas->getChanges(), ['updated_at']);

        if ($changes === []) {
            return;
        }

        AuditLogger::log(
            module: 'petugas',
            action: 'update',
            summary: sprintf('Petugas %s diperbarui.', $petugas->nama),
            auditable: $petugas,
            satkerId: $petugas->satker_id,
            changes: AuditChangeFormatter::format($petugas, $changes),
        );
    }

    public function deleted(Petugas $petugas): void
    {
        AuditLogger::log(
            module: 'petugas',
            action: 'delete',
            summary: sprintf('Petugas %s dihapus.', $petugas->nama),
            auditable: null,
            satkerId: $petugas->satker_id,
        );
    }
}
