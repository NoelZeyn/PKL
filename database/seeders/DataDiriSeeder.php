<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DataDiriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 22; $i++) {
            DB::table('data_diri')->insert([
                'id_admin_user_fk' => $i,
                'nama_lengkap' => 'Admin User ' . $i,
                'foto_profil' => null,
                'jabatan' => 'Jabatan ' . $i,
                'bpjs' => rand(0,1),
                'kontak' => '0812345678' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
