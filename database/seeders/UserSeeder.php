<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Satker;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = Hash::make('password');

        User::query()->updateOrCreate(
            ['email' => 'super_admin@example.com'],
            [
                'name' => 'Super Admin',
                'role' => UserRole::SuperAdmin,
                'satker_id' => null,
                'password' => $password,
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'atasan@example.com'],
            [
                'name' => 'Atasan',
                'role' => UserRole::Atasan,
                'satker_id' => null,
                'password' => $password,
            ],
        );

        Satker::query()->orderBy('id')->get()->each(function (Satker $satker) use ($password): void {
            User::query()->updateOrCreate(
                ['email' => 'admin.'.Str::lower($satker->kode).'@example.com'],
                [
                    'name' => 'Admin '.$satker->nama,
                    'role' => UserRole::Admin,
                    'satker_id' => $satker->id,
                    'password' => $password,
                ],
            );
        });
    }
}
