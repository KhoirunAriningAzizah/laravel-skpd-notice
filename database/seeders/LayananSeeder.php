<?php

namespace Database\Seeders;

use App\Models\Layanan;
use Illuminate\Database\Seeder;

class LayananSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $layanans = [
            [
                'kode_kasir' => 'KSR001',
                'nama' => 'Pajak Bumi dan Bangunan (PBB)',
            ],
            [
                'kode_kasir' => 'KSR002',
                'nama' => 'Pajak Kendaraan Bermotor',
            ],
            [
                'kode_kasir' => 'KSR003',
                'nama' => 'Pajak Hotel dan Restoran',
            ],
            [
                'kode_kasir' => 'KSR004',
                'nama' => 'Pajak Reklame',
            ],
            [
                'kode_kasir' => 'KSR005',
                'nama' => 'Pajak Air Tanah',
            ],
        ];

        foreach ($layanans as $layanan) {
            Layanan::create($layanan);
        }
    }
}
