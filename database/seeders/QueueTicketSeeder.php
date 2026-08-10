<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;

class QueueTicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \DB::table('queue_tickets')->truncate(); // Limpa a tabela antes de inserir novos registros
        $queuesIDs = \DB::table('queues')->pluck('id')->toArray(); // Obtém todos os IDs das filas existentes

        foreach ($queuesIDs as $queueID) {
            $totalTickets = rand(60, 500); // Gera um número aleatório de tickets entre 5 e 15 para cada fila

            $createdAt = now()->subDays(rand(0, 30)); // Gera uma data aleatória nos últimos 30 dias
            $calledAt = $createdAt->copy()->addMinutes(2); // Gera uma data de chamado aleatória após a criação do ticket

            for ($i = 0; $i <= $totalTickets; $i++) {
                $status = '';
                $statusTemp = rand(0, 3); //'waiting','called','not_attended','dismissed'

                if ($statusTemp == 0) {
                    $status = 'waiting';
                } elseif ($statusTemp == 1) {
                    $status = 'called';
                } elseif ($statusTemp == 2) {
                    $status = 'not_attended';
                } else {
                    $status = 'dismissed';
                }

                \DB::table('queue_tickets')->insert([
                    'id_queue' => $queueID,
                    'queue_ticket_number' => $i + 1, // Número do ticket (incremental)
                    'queue_ticket_created_at' => $createdAt,
                    'queue_ticket_called_at' => $status === 'called' ? $calledAt : null,
                    'queue_ticket_called_by' => $status === 'called' ? 'Utilizador_' . rand(1, 100) : null,
                    'queue_ticket_status' => $status,
                    'created_at' => $createdAt,
                    'updated_at' => now(),
                ]);
                $createdAt = $createdAt->copy()->addMinutes(rand(1, 10)); // Incrementa a data de criação para o próximo ticket
                $calledAt = $createdAt->copy()->addMinutes(rand(1, 10)); // Incrementa a data de chamado para o próximo ticket
            }
        }
    }
}
