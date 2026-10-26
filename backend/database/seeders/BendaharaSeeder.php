<?php

namespace Database\Seeders;

use App\Models\Bendahara;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BendaharaSeeder extends Seeder
{
    public function run(): void
    {
        Bendahara::firstOrCreate(
            ['email' => 'bendahara@abdimas-pengmas.org'],
            [
                'nama_bendahara' => 'Bendahara Abdimas Pengmas',
                'password' => Hash::make('password123'),
                'aktif' => true,
            ]
        );
    }
}