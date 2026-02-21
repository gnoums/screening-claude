<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Pruebas de tamizaje (PHQ-9, GAD-7)
        $this->call(AssessmentSeeder::class);

        // Usuario de prueba local (solo en desarrollo)
        if (app()->isLocal()) {
            User::factory()->create([
                'name'  => 'Psicóloga Demo',
                'email' => 'demo@psicoscreen.test',
            ]);
        }
    }
}
