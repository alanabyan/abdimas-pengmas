<?php

namespace Database\Seeders;

use App\Models\Bendahara;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BendaharaSeeder extends Seeder
{
    public function run(): void
    {
        $emails = [
            'bendahara@abdimas-pengmas.org',
            'bendahara@abdimaspengmas.com',
        ];

        foreach ($emails as $email) {
            Bendahara::firstOrCreate(
                ['email' => $email],
                [
                    'nama_bendahara' => 'Bendahara Abdimas Pengmas',
                    'password' => Hash::make('password123'),
                    'aktif' => true,
                ]
            );
        }
    }
}