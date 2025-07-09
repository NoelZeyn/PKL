<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // Ambil id_penempatan_fk yang ada di tabel penempatan
        $penempatanIds = DB::table('penempatan')->pluck('id')->toArray();

        if (empty($penempatanIds)) {
            $this->command->warn('Seeder Admin gagal: Tabel Penempatan kosong.');
            return;
        }

        $tingkatanList = ['user', 'user_review', 'admin', 'superadmin', 'anggaran'];
        $accessList = ['pending', 'active', 'inactive'];

        for ($i = 0; $i < 10; $i++) {
            Admin::create([
                'NID'                => $faker->unique()->numerify('NID#######'),
                'password'           => Hash::make('password123'), // Password default
                'id_penempatan_fk'   => $faker->randomElement($penempatanIds),
                'tingkatan_otoritas' => $faker->randomElement($tingkatanList),
                'access'             => $faker->randomElement($accessList),
                'password_changed_at' => now(),
            ]);
        }
    }
}
