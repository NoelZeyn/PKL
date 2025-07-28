<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Request;
use App\Models\Alat;
use App\Models\Admin;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class RequestSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $alatList = Alat::all();
        $adminList = Admin::all()->pluck('id')->toArray();

        if ($alatList->isEmpty() || empty($adminList)) {
            $this->command->warn('Seeder Request gagal: Tabel Alat atau Admin kosong.');
            return;
        }

        $keteranganList = [
            'Pengadaan rutin bulanan.',
            'Stock habis, diperlukan pengadaan segera.',
            'Permintaan untuk kebutuhan meeting internal.',
            'Penggantian alat yang rusak.',
            'Persiapan untuk kegiatan kantor mendatang.',
            'Permintaan tambahan karena kebutuhan mendesak.',
            'Penyediaan alat tulis untuk divisi baru.',
            'Kebutuhan operasional harian.',
            'Persediaan menipis, perlu restock.',
            'Permintaan alat tulis untuk keperluan pelatihan.'
        ];

        $statuses = ['waiting_approval_1', 'waiting_approval_2', 'waiting_approval_3', 'approved', 'rejected', 'purchasing', 'on_the_way', 'done'];

        $totalPerYear = 100; // jumlah data per tahun
        $yearsBack = 10; // 10 tahun terakhir

        foreach (range(0, $yearsBack - 1) as $i) {
            $year = now()->subYears($i)->year;

            for ($j = 0; $j < $totalPerYear; $j++) {
                $alat = $alatList->random();
                $jumlah = $faker->numberBetween(1, 50);
                $hargaSatuan = $alat->harga_satuan ?? 0;
                $total = $jumlah * $hargaSatuan;

                DB::table('request')->insert([
                    'id_inventoris_fk'   => $alat->id_alat,
                    'id_users_fk'        => $faker->randomElement($adminList),
                    'tanggal_permintaan' => $faker->dateTimeBetween("$year-01-01", "$year-12-31")->format('Y-m-d'),
                    'status'             => $faker->randomElement($statuses),
                    'jumlah'             => $jumlah,
                    'total'              => $total,
                    'keterangan'         => $faker->randomElement($keteranganList),
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
            }

            $this->command->info("Data tahun $year berhasil di-generate.");
        }

        $this->command->info('Seeder Request selesai.');
    }
}
