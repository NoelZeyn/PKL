<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BidangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bidang = [
            ['nama_bidang' => 'EQA'],
            ['nama_bidang' => 'OP'],
            ['nama_bidang' => 'BS'],
            ['nama_bidang' => 'HAR'],
            ['nama_bidang' => 'LK3'],
        ];

        DB::table('bidang')->insert($bidang);
    }
}
