<?php

namespace Database\Seeders;

use App\Models\KategoriPengadaan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class AlatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $id_kategori = KategoriPengadaan::pluck('id_kategori')->toArray();
        for ($i = 0; $i < count($id_kategori); $i++) {
            DB::table('alat')->insert([
                'id_kategori_fk' => $id_kategori[$i],
                'nama_barang' => 'Alat ' . $faker->randomElement(['Pensil', 'Penghapus', 'Spidol', 'Kertas', 'Pulpen']),
                'stock_min' => 1,
                'stock_max' => 10,
                'stock' => 5,
                'satuan' => $faker->randomElement(['Rim', 'Pak', 'Buah', 'Pasang', 'Set']),
                'harga_satuan' => 10000,
                'harga_estimasi' => 12000,
                'order' => $i,
                'keterangan' => 'Keterangan untuk alat ' . $i,
            ]);
        }

        // DB::table('kategori_pengadaan')->insert($kategori);
    }
}
