<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PenempatanSeeder extends Seeder
{
    public function run(): void
    {
        $penempatan = [
            // LK3
            ['nama_penempatan' => 'K3 & KAM', 'id_bidang_fk' => 5],
            ['nama_penempatan' => 'LINGKUNGAN', 'id_bidang_fk' => 5],
            ['nama_penempatan' => 'MANAJER', 'id_bidang_fk' => 5],
            
            
            // OP - Operasi
            ['nama_penempatan' => 'PERENCANAAN & PENGENDALIAN OPERASI', 'id_bidang_fk' => 2],
            ['nama_penempatan' => 'CCR PLTGU', 'id_bidang_fk' => 2],
            ['nama_penempatan' => 'CCR PLTU 1-2', 'id_bidang_fk' => 2],
            ['nama_penempatan' => 'CCR PLTU 3-4', 'id_bidang_fk' => 2],
            ['nama_penempatan' => 'CCR PLTU CNG', 'id_bidang_fk' => 2],
            ['nama_penempatan' => 'PRODUKSI PLTU-PLTG (A,B,C,D)', 'id_bidang_fk' => 2],
            ['nama_penempatan' => 'PRODUKSI PLTGU & CNG (A,B,C,D)', 'id_bidang_fk' => 2],
            ['nama_penempatan' => 'NIAGA & BAHAN BAKAR', 'id_bidang_fk' => 2],
            ['nama_penempatan' => 'KIMIA & LAB', 'id_bidang_fk' => 2],
            ['nama_penempatan' => 'MANAJER', 'id_bidang_fk' => 2],

            // HAR - Pemeliharaan
            ['nama_penempatan' => 'PERENCANAAN & PENGENDALIAN PEMELIHARAAN', 'id_bidang_fk' => 4],
            ['nama_penempatan' => 'OUTAGE MANAGEMENT', 'id_bidang_fk' => 4],
            ['nama_penempatan' => 'PEMELIHARAAN LISTRIK PLTGU & CNG', 'id_bidang_fk' => 4],
            ['nama_penempatan' => 'PEMELIHARAAN LISTRIK PLTU-G', 'id_bidang_fk' => 4],
            ['nama_penempatan' => 'PEMELIHARAAN KONTROL & INSTRUMEN PLTGU dan CNG', 'id_bidang_fk' => 4],
            ['nama_penempatan' => 'PEMELIHARAAN KONTROL & PLTU-G', 'id_bidang_fk' => 4],
            ['nama_penempatan' => 'PEMELIHARAAN MESIN & SIPIL PLTGU dan CNG', 'id_bidang_fk' => 4],
            ['nama_penempatan' => 'PEMELIHARAAN MESIN & SIPIL PLTU-G', 'id_bidang_fk' => 4],
            ['nama_penempatan' => 'INVENTORI KONTROL & GUDANG', 'id_bidang_fk' => 4],
            ['nama_penempatan' => 'MANAJER', 'id_bidang_fk' => 4],

            // EQA - Enginiring & Quality Assurance
            ['nama_penempatan' => 'SYSTEM OWNER', 'id_bidang_fk' => 1],
            ['nama_penempatan' => 'CONDITION BASED MAINTENANCE', 'id_bidang_fk' => 1],
            ['nama_penempatan' => 'MANAJEMEN MUTU, RISIKO & KEPATUHAN', 'id_bidang_fk' => 1],
            ['nama_penempatan' => 'SINFO', 'id_bidang_fk' => 1],
            ['nama_penempatan' => 'IT', 'id_bidang_fk' => 1],
            ['nama_penempatan' => 'MANAJER', 'id_bidang_fk' => 1],

            // BS - Business Support
            ['nama_penempatan' => 'KEUANGAN', 'id_bidang_fk' => 3],
            ['nama_penempatan' => 'SDM, UMUM & CSR', 'id_bidang_fk' => 3],
            ['nama_penempatan' => 'PENGADAAN', 'id_bidang_fk' => 3],
            ['nama_penempatan' => 'MANAJER', 'id_bidang_fk' => 3],
        ];

        DB::table('penempatan')->insert($penempatan);
    }
}
