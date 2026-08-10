<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Adicionar usuários de exemplo

        $users = [
            [
                'email' => 'sysadmin@localhost.com',
                'password' => bcrypt('MinhaSenha123'),
                'id_company' => 0,
                'role' => 'sys-admin',
                'active' => true,
            ],
            [
                'email' => 'admin1@localhost.com',
                'password' => bcrypt('MinhaSenha123'),
                'id_company' => 1,
                'role' => 'client-admin',
                'active' => true,
            ],
            [
                'email' => 'admin2@localhost.com',
                'password' => bcrypt('MinhaSenha123'),
                'id_company' => 2,
                'role' => 'client-admin',
                'active' => true,
            ],
        ];

        DB::table('users')->insert($users);
        echo count($users) . " usuários de exemplo adicionados com sucesso.\n";
    }
}
