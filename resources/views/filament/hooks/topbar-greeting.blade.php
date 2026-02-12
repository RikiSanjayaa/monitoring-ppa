@php
    $roleLabel = match ($user?->role?->value) {
        'super_admin' => 'Super Admin',
        'admin' => 'Admin',
        'atasan' => 'Atasan',
        default => 'User',
    };
@endphp

<div class="hidden md:flex items-center gap-2 px-3 py-1 rounded-lg border border-gray-200/30 dark:border-white/10 bg-gray-50/60 dark:bg-white/5 text-sm">
    <span class="text-gray-500 dark:text-gray-400">Selamat datang,</span>
    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $user?->name ?? '-' }}</span>
    <span class="text-gray-500 dark:text-gray-400">({{ $roleLabel }})</span>
</div>
