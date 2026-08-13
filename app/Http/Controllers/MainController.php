<?php

namespace App\Http\Controllers;

use App\Models\Queue;
use App\Models\QueueTicket;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class MainController extends Controller
{
    public function index()
    {

        // carregar queues e tickets do usuário autenticado
        $data = [
            'subtitle' => 'Dashboard',
            'queues' => $this->getUserQueues(),
            'companyName' => Auth::user()->company->company_name,
            'companyTotals' => $this->getCompanyTotals()
        ];

        return view('main.dashboard', $data);
    }


    private function getUserQueues()
    {
        $companyId = Auth::user()->id_company;
        return Queue::where('id_company', $companyId)
            // ->where('status', 'active')
            // ->whereNull('deleted_at')
            ->withCount([
                'tickets as total_tickets' => function ($query) {
                    $query->whereNotnull('queue_ticket_status')
                        ->whereNull('deleted_at');
                },
                'tickets as total_dismissed' => function ($query) {
                    $query->where('queue_ticket_status', 'dismissed')
                        ->whereNull('deleted_at');
                },
                'tickets as total_not_attended' => function ($query) {
                    $query->where('queue_ticket_status', 'not_attended')
                        ->whereNull('deleted_at');
                },
                'tickets as total_called' => function ($query) {
                    $query->where('queue_ticket_status', 'called')
                        ->whereNull('deleted_at');
                },
                'tickets as total_waiting' => function ($query) {
                    $query->where('queue_ticket_status', 'waiting')
                        ->whereNull('deleted_at');
                },
            ])->get();
    }

    private function getCompanyTotals()
    {
        $companyId = Auth::user()->id_company;
        $totalQueues = Queue::where('id_company', $companyId)->count();

        // Obter todos os tickets da empresa

        $tickets = QueueTicket::whereHas('queue', function ($query) use ($companyId) {
            $query->where('id_company', $companyId);
        })->get();

        return [
            'totals_queues' => $totalQueues,
            'total_tickets' => $tickets->count(),
            'total_dismissed' => $tickets->where('queue_ticket_status', 'dismissed')
                ->whereNull('deleted_at')
                ->count(),
            'total_not_attended' => $tickets->where('queue_ticket_status', 'not_attended')
                ->whereNull('deleted_at')
                ->count(),
            'total_called' => $tickets->where('queue_ticket_status', 'called')
                ->whereNull('deleted_at')
                ->count(),
            'total_waiting' => $tickets->where('queue_ticket_status', 'waiting')
                ->whereNull('deleted_at')
                ->count(),
        ];
    }

    public function queueDetails($id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (\Exception $e) {
            abort(403, 'ID inválido!');
        }

        // Se a fila existe e o usuário logado pertence a empresa na qual pertence a fila
        $queue = Queue::where('id', $id)
            ->where('id_company', Auth::user()->id_company)
            ->withCount([
                'tickets as total_tickets' => function ($query) {
                    $query->whereNotnull('queue_ticket_status')
                        ->whereNull('deleted_at');
                },
                'tickets as total_dismissed' => function ($query) {
                    $query->where('queue_ticket_status', 'dismissed')
                        ->whereNull('deleted_at');
                },
                'tickets as total_not_attended' => function ($query) {
                    $query->where('queue_ticket_status', 'not_attended')
                        ->whereNull('deleted_at');
                },
                'tickets as total_called' => function ($query) {
                    $query->where('queue_ticket_status', 'called')
                        ->whereNull('deleted_at');
                },
                'tickets as total_waiting' => function ($query) {
                    $query->where('queue_ticket_status', 'waiting')
                        ->whereNull('deleted_at');
                },
            ])
            ->firstOrFail();

        if (!$queue) {
            abort(404, 'Fila não encontrada...');
        }

        $tickets = $queue->tickets()->get();

        $data = [
            'subtitle' => 'Detalhes',
            'queue' => $queue,
            'tickets' => $tickets
        ];

        return view('main.queue_details', $data);
    }

    public function createQueue()
    {
        $data = [
            'subtitle' => 'Criar fila',
        ];
        return view('main.queue_create_form', $data);
    }

    public function createQueueSubmit(Request $request)
    {

    }
}
