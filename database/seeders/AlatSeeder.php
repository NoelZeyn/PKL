<?php

namespace Database\Seeders;

use App\Models\KategoriPengadaan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AlatSeeder extends Seeder
{
    public function run(): void
    {
        // $atkItems = [
        //     'Amplop kabinet + lem',
        //     'Amplop kabinet tanpa lem',
        //     'Amplop persegi + lem',
        //     'Amplop persegi tanpa lem',
        //     'Ballpoint 4 warna',
        //     'Ballpoint BOXY (Biru)',
        //     'Ballpoint Faster c6 (Biru)',
        //     'Ballpoint HI-TEC-C 0.4 Pilot (Biru)',
        //     'Ballpoint Kenko K-1 Gel (Biru)',
        //     'Ballpoint PENTEL (Biru)',
        //     'Ballpoint Pillot BPTP (Hitam)',
        //     'Ballpoint Pillot G-2.07 (Hitam)',
        //     'Ballpoint Pillot Super Grip (Biru)',
        //     'Bantalan Stempel Artline Besar (Biru)',
        //     'Bantalan Stempel Artline Kecil (Biru)',
        //     'Baterry AA Alkalin',
        //     'Baterry AAA Alkalin',
        //     'Baterry besar Alkalin',
        //     'Baterry megger 9 V',
        //     'Battery AA rechargeable + Charger',
        //     'Binder clip No. 105',
        //     'Binder clip No. 107',
        //     'Binder clip No. 111 tanggung',
        //     'Binder clip No. 155',
        //     'Binder clip No. 200',
        //     'Binder clip No. 260 besar',
        //     'Bufallo Folio PAITON',
        // ];

        // $nonAtkItems = [
        //     'Calculator 12 digit Besar',
        //     'Calculator Solar "KARCE® KC-D50E"',
        //     'Calculator Casio fx 3650P',
        //     'Card Lan D-Link 10/100 nabps',
        //     'Catridge Tinta Canon PG-40(B)',
        //     'Catridge Tinta Canon PG-41(C)',
        //     'CD Blank',
        //     'CDR Blank Verbatim',
        //     'DVD Blank',
        //     'DVD Room RW  Samsung',
        //     'Epson LQ 2180',
        //     'Flashdisk 16 Gb',
        //     'Refill Ink Canon  IP 1600 (hitam) merk Refill',
        //     'Refill Ink Canon  IP 1600 (warna) merk Refill',
        //     'Tinta E-Print EFC 019 PLC (Black)',
        //     'Tinta E-Print EFC 026 PLC (Cyan)',
        // ];

        // $allItems = array_merge($atkItems, $nonAtkItems);
        // $id_kategori_list = KategoriPengadaan::pluck('id_kategori')->toArray();

        // $i = 0;
        // foreach ($allItems as $item) {
        //     DB::table('alat')->insert([
        //         'id_kategori_fk' => $id_kategori_list[array_rand($id_kategori_list)],
        //         'nama_barang' => $item,
        //         'stock_min' => rand(1, 5),
        //         'stock_max' => rand(6, 20),
        //         'stock' => rand(0, 15),
        //         'satuan' => fake()->randomElement(['Rim', 'Pak', 'Buah', 'Set', 'Pcs']),
        //         'harga_satuan' => rand(5000, 150000),
        //         'harga_estimasi' => rand(6000, 160000),
        //         'keterangan' => 'Keterangan untuk ' . $item,
        //     ]);
        //     $i++;
        // }
               $atkItems = [
            'Amplop kabinet + lem',
            'Amplop kabinet tanpa lem',
            'Amplop persegi + lem',
            'Amplop persegi tanpa lem',
            'Ballpoint 4 warna',
            'Ballpoint BOXY (Biru)',
            'Ballpoint Faster c6 (Biru)',
            'Ballpoint HI-TEC-C 0.4 Pilot (Biru)',
            'Ballpoint Kenko K-1 Gel (Biru)',
            'Ballpoint PENTEL (Biru)',
            'Ballpoint Pillot BPTP (Hitam)',
            'Ballpoint Pillot G-2.07 (Hitam)',
            'Ballpoint Pillot Super Grip (Biru)',
            'Bantalan Stempel Artline Besar (Biru)',
            'Bantalan Stempel Artline Kecil (Biru)',
            'Baterry AA Alkalin',
            'Baterry AAA Alkalin',
            'Baterry besar Alkalin',
            'Baterry megger 9 V',
            'Battery AA rechargeable + Charger',
            'Binder clip No. 105',
            'Binder clip No. 107',
            'Binder clip No. 111 tanggung',
            'Binder clip No. 155',
            'Binder clip No. 200',
            'Binder clip No. 260 besar',
            'Bufallo Folio PAITON',
            // ... Tambahkan jika ada ATK lainnya
        ];

        $nonAtkItems = [
            'Calculator 12 digit Besar',
            'Calculator Solar "KARCE® KC-D50E"',
            'Calculator Casio fx 3650P',
            'Card Lan D-Link 10/100 nabps',
            'Catridge Tinta Canon PG-40(B)',
            'Catridge Tinta Canon PG-41(C)',
            'CD Blank',
            'CDR Blank Verbatim',
            'DVD Blank',
            'DVD Room RW  Samsung',
            'Epson LQ 2180',
            'Flashdisk 16 Gb',
            'Refill Ink Canon  IP 1600 (hitam) merk Refill',
            'Refill Ink Canon  IP 1600 (warna) merk Refill',
            'Tinta E-Print EFC 019 PLC (Black)',
            'Tinta E-Print EFC 026 PLC (Cyan)',
            // ... Tambahkan jika ada Non-ATK lainnya
        ];

        // ✅ Tambahkan 200 alat dummy tambahan ke non-ATK
        for ($j = 1; $j <= 200; $j++) {
            $nonAtkItems[] = "Alat Dummy Tambahan $j";
        }

        $allItems = array_merge($atkItems, $nonAtkItems);
        $id_kategori_list = KategoriPengadaan::pluck('id_kategori')->toArray();

        $i = 0;
        foreach ($allItems as $item) {
            DB::table('alat')->insert([
                'id_kategori_fk' => $id_kategori_list[array_rand($id_kategori_list)],
                'nama_barang' => $item,
                'stock_min' => rand(1, 5),
                'stock_max' => rand(6, 20),
                'stock' => rand(0, 15),
                'satuan' => fake()->randomElement(['Rim', 'Pak', 'Buah', 'Set', 'Pcs']),
                'harga_satuan' => rand(5000, 150000),
                'harga_estimasi' => rand(6000, 160000),
                'keterangan' => 'Keterangan untuk ' . $item,
            ]);
            $i++;
        }
    }
}
