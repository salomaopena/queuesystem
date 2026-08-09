<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = [];

        for ($i = 1; $i <= 3; $i++) {
            $companies[] = [
                'company_name' => 'Empresa ' . $i,
                'company_logo' => 'empresa_0' . $i . '.png',
                'uuid' => \Illuminate\Support\Str::uuid(),
                'phone' => '123456789' . $i,
                'email' => 'empresa' . $i . '@localhost.com',
                'address' => 'Rua da empresa ' . $i. ", 123, bairro, cidade, estado, país",
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('companies')->insert($companies);
        echo count($companies) . " empresas de exemplo adicionadas com sucesso.\n";
    }
}
