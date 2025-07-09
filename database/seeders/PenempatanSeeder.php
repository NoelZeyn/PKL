<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PenempatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $penempatan = [
            ['nama_penempatan' => 'HAR MESIN PLTU-G'],
            ['nama_penempatan' => 'HAR MESIN PLTGU & CNG'],
            ['nama_penempatan' => 'HAR LISTRIK PLTU-G'],
            ['nama_penempatan' => 'HAR LISTRIK PLTGU & CNG'],
            ['nama_penempatan' => 'HAR KONIN PLTU-G'],
            ['nama_penempatan' => 'HAR KONIN PLTGU & CNG'],
            ['nama_penempatan' => 'RENDAL HAR'],
            ['nama_penempatan' => 'RENDAL OP'],
            ['nama_penempatan' => 'ENGINEERING SO TU & CNG'],
            ['nama_penempatan' => 'ENGINEERING SO GU'],
            ['nama_penempatan' => 'CONDITION BASE MAINTENANCE (ENGINEERING TO)'],
            ['nama_penempatan' => 'CCR PLTU 3-4'],
            ['nama_penempatan' => 'CCR PLTGU'],
            ['nama_penempatan' => 'LABORATORIUM'],
            ['nama_penempatan' => 'SARANA'],
            ['nama_penempatan' => 'K3'],
            ['nama_penempatan' => 'LINGKUNGAN'],
            ['nama_penempatan' => 'SDM, UMUM & CSR'],
            ['nama_penempatan' => 'KEUANGAN'],
            ['nama_penempatan' => 'IT'],
            ['nama_penempatan' => 'SINFO'],
            ['nama_penempatan' => 'CNG'],
            ['nama_penempatan' => 'OM'],
            ['nama_penempatan' => 'INVENTORY DAN GUDANG'],
            ['nama_penempatan' => 'PENGADAAN'],
            ['nama_penempatan' => 'NIAGA'],
        ];

        DB::table('penempatan')->insert($penempatan);
    }
}
