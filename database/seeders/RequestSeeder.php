<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Request; // Pastikan modelnya sudah ada
use App\Models\Alat;
use App\Models\Admin;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class RequestSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        $alatList = Alat::all()->pluck('id_alat')->toArray();
        $adminList = Admin::all()->pluck('id')->toArray();

        if (empty($alatList) || empty($adminList)) {
            $this->command->warn('Seeder Request gagal: Tabel Alat atau Admin kosong.');
            return;
        }

        $statuses = ['draft', 'waiting_approval_1', 'approved_1', 'waiting_approval_2', 'approved_2', 'rejected'];

        for ($i = 0; $i < 10; $i++) {
            DB::table('request')->insert([
                'id_inventoris_fk'   => $faker->randomElement($alatList),
                'id_users_fk'        => $faker->randomElement($adminList),
                'tanggal_permintaan' => $faker->date(),
                'status'             => $faker->randomElement($statuses),
                'jumlah'             => $faker->numberBetween(1, 50),
                'total'              => $faker->numberBetween(50000, 1000000),
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        }
    }
}
