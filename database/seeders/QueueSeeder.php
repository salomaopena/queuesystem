<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class QueueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'id_company' => 1,
                'name' => 'Fila de Atendimento 1',
                'description' => 'Fila de atendimento para clientes',
                'service_name' => 'Atendimento geral',
                'service_desk' => 'Balcão 1',
                'queue_prefix' => 'A',
                'queue_total_digits' => 3,
                'queue_colors' => json_encode(
                    [
                        'prefix_bg_color' => '#FFFF00',
                        'prefix_text_color' => '#000000',
                        'number_bg_color' => '#AAAAAA',
                        'number_text_color' => '#000000',
                    ]
                ),
                'hash_code' => Str::random(64),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_company' => 1,
                'name' => 'Fila de Atendimento 2',
                'description' => 'Fila de atendimento para consultas familiares',
                'service_name' => 'Atendimento geral',
                'service_desk' => 'Balcão 2',
                'queue_prefix' => 'B',
                'queue_total_digits' => 3,
                'queue_colors' => json_encode(
                    [
                        'prefix_bg_color' => '#000FF0',
                        'prefix_text_color' => '#000000',
                        'number_bg_color' => '#AAAAAA',
                        'number_text_color' => '#000000',
                    ]
                ),
                'hash_code' => Str::random(64),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_company' => 2,
                'name' => 'Fila de Atendimento 1',
                'description' => 'Fila de atendimento para clientes',
                'service_name' => 'Atendimento geral',
                'service_desk' => 'Balcão 1',
                'queue_prefix' => 'C',
                'queue_total_digits' => 3,
                'queue_colors' => json_encode(
                    [
                        'prefix_bg_color' => '#FFFF00',
                        'prefix_text_color' => '#000000',
                        'number_bg_color' => '#AAAAAA',
                        'number_text_color' => '#000000',
                    ]
                ),
                'hash_code' => Str::random(64),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        \DB::table('queues')->insert($data);
        echo count($data) . " filas de atendimento foram inseridas na tabela 'queues'." . PHP_EOL;
    }
}
