<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Alat;
use App\Models\Penempatan;
use Illuminate\Support\Facades\DB;

class AlatPenempatanSeeder extends Seeder
{
    public function run(): void
    {
        $alatList = Alat::all();
        $penempatanList = Penempatan::all();

        foreach ($alatList as $alat) {
            $totalStock = $alat->stock;
            if ($totalStock <= 0) continue;

            // Ambil sejumlah penempatan acak (antara 2 - total)
            $jumlahPenempatan = rand(2, min(6, $penempatanList->count())); // bisa disesuaikan
            $penempatanTerpilih = $penempatanList->random($jumlahPenempatan);

            // Bagi stok secara acak tapi totalnya tetap sama
            $stokDistribusi = $this->bagiStokSecaraAcak($totalStock, $jumlahPenempatan);

            foreach ($penempatanTerpilih as $i => $penempatan) {
                $stok = $stokDistribusi[$i];
                DB::table('alat_penempatan')->insert([
                    'id_alat_fk' => $alat->id_alat,
                    'id_penempatan_fk' => $penempatan->id,
                    'stock' => $stok,
                    'stock_min' => max(1, floor($stok * 0.3)),
                    'stock_max' => $stok + rand(1, 3),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function bagiStokSecaraAcak($total, $jumlah)
    {
        $pembagian = [];
        $sisa = $total;

        for ($i = 0; $i < $jumlah - 1; $i++) {
            $bagian = rand(0, $sisa);
            $pembagian[] = $bagian;
            $sisa -= $bagian;
        }

        $pembagian[] = $sisa;
        shuffle($pembagian); // acak urutannya

        return $pembagian;
    }
}
