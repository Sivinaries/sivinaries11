<?php

namespace Database\Seeders;

use App\Models\Profil;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProfilSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fetch or create a user to associate the profil with
        $user = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('123456'),
                'level' => 'Admin',
                'qr_token' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
                'device_id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            ]
        );

        // Seed the profil for the user
        Profil::create([
            'user_id' => $user->id, // Associate with the user
            'name' => 'Sivinaries Coffee',
            'alamat' => 'Jalan Satrio Wibowo III, RW 04, Tlogosari Kulon, Pedurungan, Semarang, Central Java, Java, 50162, Indonesia',
            'jam' => '10.00 - 23.00 WIB',
            'no_wa' => '6287733839260',
            'deskripsi' => 'Buka Senin - Minggu | Sejak Dulu | Kami Ada Karena Kamu #CahSkena',
        ]);
    }
}
