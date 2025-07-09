<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategori = [
            ['nama_kategori' => 'KEBUTUHAN ATK'],
            ['nama_kategori' => 'KEBUTUHAN NON-ATK'],

        ];

        DB::table('kategori_pengadaan')->insert($kategori);
    }
}
