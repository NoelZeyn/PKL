<?php

namespace Database\Seeders;

use App\Models\User;
use GuzzleHttp\Psr7\Request;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
public function run(): void
{
    $this->call(PenempatanSeeder::class);
    $this->call(KategoriSeeder::class);
    $this->call(AlatSeeder::class);
    $this->call(AdminSeeder::class);
    $this->call(RequestSeeder::class);
}

}
