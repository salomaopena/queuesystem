<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class BundleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bundles = [];

        for ($index = 1; $index <= 3; $index++) {
            $bundles [] = array(
                'id_company' => 1,
                'name' => 'Bundle ' . $index,
                'queues' => json_encode([
                    Str::random(64),
                    Str::random(64),
                    Str::random(64),
                    Str::random(64),
                ]),
                'credential_username' => str_repeat('a', 30) . $index,
                'credential_password' => bcrypt(str_repeat('b', 30)) . $index,
                'created_at' => now(),
            );
        }

        DB::table('bundles')->insert($bundles);

        echo count($bundles) . ' bundles adicionados com sucesso!' . PHP_EOL;
    }
}
