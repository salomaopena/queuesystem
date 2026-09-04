<?php

namespace App\Http\Controllers;

use App\Models\Queue;
use App\Models\QueueTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

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
        return Queue::withTrashed()
            ->where('id_company', $companyId)
            /* ->where('status', 'active')
            ->whereNull('deleted_at')*/
            ->withCount([
                'tickets as total_tickets' => function ($query) {
                    $query->whereNotnull('queue_ticket_status')
                        /* ->whereNull('deleted_at')*/ ;
                },
                'tickets as total_dismissed' => function ($query) {
                    $query->where('queue_ticket_status', 'dismissed')
                        /*->whereNull('deleted_at')*/ ;
                },
                'tickets as total_not_attended' => function ($query) {
                    $query->where('queue_ticket_status', 'not_attended')
                        /*->whereNull('deleted_at')*/ ;
                },
                'tickets as total_called' => function ($query) {
                    $query->where('queue_ticket_status', 'called')
                        /*->whereNull('deleted_at')*/ ;
                },
                'tickets as total_waiting' => function ($query) {
                    $query->where('queue_ticket_status', 'waiting')
                        /*->whereNull('deleted_at')*/ ;
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
        // Validar as requisiçõe
        $request->validate(
            [
                'name' => 'required|min:5|max:100',
                'description' => 'required|min:5|max:255',
                'service' => 'required|min:5|max:50',
                'desk' => 'required|min:5|max:20',
                'prefix' => 'required|regex:/^[A-Z\-]{1}$/',
                'total_digits' => 'required|Integer|min:2|max:4',
                'color_1' => 'required|regex:/^#[a-f0-9]{6}$/',
                'color_2' => 'required|regex:/^#[a-f0-9]{6}$/',
                'color_3' => 'required|regex:/^#[a-f0-9]{6}$/',
                'color_4' => 'required|regex:/^#[a-f0-9]{6}$/',
                'hidden_hash_code' => 'required|size:64',
                'status' => 'required|in:active,inactive',
            ],
            [
                'name.required' => 'O nome da fila é obrigatório',
                'name.max' => 'O nome da fila deve ter no máximo 100 carácteres',
                'name.min' => 'O nome da fila deve ter pelo menos 5 carácteres',
                'description.required' => 'A descrição da fila é obrigatória',
                'description.max' => 'A descrição da fila deve ter no máximo 255 caracteres',
                'description.min' => 'A descrição da fila deve ter pelo menos 5 caracteres',
                'desk.required' => 'O balcão da fila é obrigatório',
                'desk.max' => 'O balcão deve ter no máximo 20 caracteres',
                'desk.min' => 'O balcão deve ter pelo menos 5 caracteres',
                'service.required' => 'O serviço da fila é obrigatório',
                'service.max' => 'O serviço deve deve no máximo 50 caracteres',
                'service.min' => 'O serviço deve ter pelo menos 5 caracteres',
                'prefix.required' => 'O prefixo da fila é obrigatório',
                'prefix.regex' => 'O prefixo não tem o valor correto',
                'total_digits.required' => 'O total de dígitos é obrigatório',
                'total_digits.Integer' => 'O total de dígitos deve ser um número inteiro',
                'total_digits.max' => 'O total de dígitos deve ser no máximo de 4 carácteres',
                'total_digits.min' => 'O total de dígitos deve ser no mínimo de 2 carácteres',
                'color_1.required' => 'A cor de fundo do prefíxo é obrigatória',
                'color_1.regex' => 'A cor de fundo do prefixo deve ser um código hexadecima válido (ex: #ffffff)',
                'color_2.required' => 'A cor do texto do prefíxo é obrigatória',
                'color_2.regex' => 'A cor do texto prefixo deve ser um código hexadecima válido (ex: #ffffff)',
                'color_3.required' => 'A cor de fundo do número é obrigatória',
                'color_3.regex' => 'A cor do texto do número deve ser um código hexadecima válido (ex: #ffffff)',
                'color_4.required' => 'A cor do texto do número é obrigatória',
                'color_4.regex' => 'A cor de fundo do texto deve ser um código hexadecima válido (ex: #ffffff)',
                'hidden_hash_code.required' => 'O código hash é obrigatório',
                'hidden_hash_code.size' => 'O tamanho do código hash deve ser de até 64 caracteres',
                'status.required' => 'O estado da fila é o obrigatório',
                'status.in' => 'O estado da fila deve ser ativo ou inativo',
            ]
        );

        // Verificar se o nome da fila é único
        $companyId = Auth::user()->id_company;

        $queueExists = Queue::where('id_company', $companyId)
            ->where('name', $request->name)->exists();


        if ($queueExists) {
            return redirect()->back()->withInput()
                ->with('server_error', 'Já existe uma fila de espera com este nome. Por favor defina outro nome.');
        }

        // Verificar se a hash code é única?

        $hashCode = $request->hidden_hash_code;

        $hashExists = Queue::where('hash_code', $hashCode)->exists();

        if ($hashExists) {
            return redirect()->back()->withInput()
                ->with('server_error', 'O código hash da fila já existe. Por favor defina outro código.');
        }

        $newQueue = new Queue();
        $newQueue->id_company = Auth::user()->id_company;
        $newQueue->name = trim($request->name);
        $newQueue->description = trim($request->description);
        $newQueue->service_name = trim($request->service);
        $newQueue->service_desk = trim($request->desk);
        $newQueue->queue_prefix = strtoupper(trim($request->prefix));
        $newQueue->queue_total_digits = intval(trim($request->total_digits));
        $newQueue->queue_colors = json_encode([
            'prefix_bg_color' => trim($request->color_1),
            'prefix_text_color' => trim($request->color_2),
            'number_bg_color' => trim($request->color_3),
            'number_text_color' => trim($request->color_4),
        ]);
        $newQueue->hash_code = trim($request->hidden_hash_code);
        $newQueue->status = trim($request->status);
        $newQueue->created_at = date('Y-m-d H:i:s');

        // Persistir os dados no banco de dados
        $newQueue->save();

        return redirect()->route('dashboard')->with('message', 'Fila criada com sucesso.');
    }


    public function genarateQueueHash()
    {
        // Gerar uma única hash de 64 caracteres
        $hash = hash("sha256", Str::random(40));

        // Certificar de que a hash é única no sistema

        while (Queue::where('hash_code', $hash)->exists()) {
            $hash = hash("sha256", Str::random(40));
        }

        // Devolver a hash
        //return response()->json(['hash'=>$hash]);
        return response(['hash' => $hash]);
    }

    public function editQueue($id)
    {
        // Verificar se o id desencriptado é valido
        try {
            $id = Crypt::decrypt($id);
        } catch (\Exception $e) {
            abort(403, 'ID de fila enválido');
        }

        // Verificar se a fila existe e pertence ao ID deste usuário
        $companyId = Auth::user()->id_company;
        $queue = Queue::where('id', $id)
            ->where('id_company', $companyId)->firstOrFail();

        if (!$queue) {
            abort(404, 'Fila não encontrada.');
        }

        // Mostrar o formulário de edição
        $data = [
            'subtitle' => 'Editar fila',
            'queue' => $queue,
            'queueColor' => json_decode($queue->queue_colors, true)
        ];

        return view('main.queue_edit_form', $data);

    }

    public function editQueueSubmit(Request $request)
    {
        // Validar as requisiçõe
        $request->validate(
            [
                'name' => 'required|min:5|max:100',
                'description' => 'required|min:5|max:255',
                'service' => 'required|min:5|max:50',
                'desk' => 'required|min:5|max:20',
                'prefix' => 'required|regex:/^[A-Z\-]{1}$/',
                'color_1' => 'required|regex:/^#[a-f0-9]{6}$/',
                'color_2' => 'required|regex:/^#[a-f0-9]{6}$/',
                'color_3' => 'required|regex:/^#[a-f0-9]{6}$/',
                'color_4' => 'required|regex:/^#[a-f0-9]{6}$/',
                'status' => 'required|in:active,inactive,done',
            ],
            [
                'name.required' => 'O nome da fila é obrigatório',
                'name.max' => 'O nome da fila deve ter no máximo 100 carácteres',
                'name.min' => 'O nome da fila deve ter pelo menos 5 carácteres',
                'description.required' => 'A descrição da fila é obrigatória',
                'description.max' => 'A descrição da fila deve ter no máximo 255 caracteres',
                'description.min' => 'A descrição da fila deve ter pelo menos 5 caracteres',
                'desk.required' => 'O balcão da fila é obrigatório',
                'desk.max' => 'O balcão deve ter no máximo 20 caracteres',
                'desk.min' => 'O balcão deve ter pelo menos 5 caracteres',
                'service.required' => 'O serviço da fila é obrigatório',
                'service.max' => 'O serviço deve deve no máximo 50 caracteres',
                'service.min' => 'O serviço deve ter pelo menos 5 caracteres',
                'prefix.required' => 'O prefixo da fila é obrigatório',
                'prefix.regex' => 'O prefixo não tem o valor correto',
                'color_1.required' => 'A cor de fundo do prefíxo é obrigatória',
                'color_1.regex' => 'A cor de fundo do prefixo deve ser um código hexadecima válido (ex: #ffffff)',
                'color_2.required' => 'A cor do texto do prefíxo é obrigatória',
                'color_2.regex' => 'A cor do texto prefixo deve ser um código hexadecima válido (ex: #ffffff)',
                'color_3.required' => 'A cor de fundo do número é obrigatória',
                'color_3.regex' => 'A cor do texto do número deve ser um código hexadecima válido (ex: #ffffff)',
                'color_4.required' => 'A cor do texto do número é obrigatória',
                'color_4.regex' => 'A cor de fundo do texto deve ser um código hexadecima válido (ex: #ffffff)',
                'status.required' => 'O estado da fila é o obrigatório',
                'status.in' => 'O estado da fila deve ser Ativo, Inativo ou Terminada',
            ]
        );

        // verificar se o ID da fila está disponível

        if (!$request->has('queue_id')) {
            abort(403, "Operação inválida!");
        }

        try {
            $queueId = Crypt::decrypt($request->queue_id);
        } catch (\Exception $th) {
            abort(403, "Operação inválida!");
        }

        // Validar se o ID pertence a uma fila do da empresa do usuário autenticado.

        $companyId = Auth::user()->id_company;
        $queue = Queue::where('id', $queueId)->where('id_company', $companyId)->firstOrFail();

        if (!$queue) {
            abort(404, 'Operação inválida!');
        }

        $queueExists = Queue::where('id_company', $companyId)
            ->where('name', $request->name)
            ->where('id', '!=', $queueId)->exists();

        if ($queueExists) {
            return redirect()->back()->withInput()->with('server_error', 'Já existe outra fila com o mesmo nome. Por favor defina outro nome');
        }

        // Preparar os dados para enviar ao banco de dados.

        $queue->name = trim($request->name);
        $queue->description = trim($request->description);
        $queue->service_name = trim($request->service);
        $queue->service_desk = trim($request->desk);
        $queue->queue_prefix = trim($request->prefix);
        $queue->queue_colors = json_encode([
            'prefix_bg_color' => trim($request->color_1),
            'prefix_text_color' => trim($request->color_2),
            'number_bg_color' => trim($request->color_3),
            'number_text_color' => trim($request->color_4),
        ]);
        $queue->status = trim($request->status);
        $queue->updated_at = date('Y-m-d H:i:s');
        $queue->save();

        return redirect()->route('dashboard')->with('message', 'Fila atualizada com sucesso');

    }

    public function cloneQueue($id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (\Exception $e) {
            abort(403, 'ID de fila inválido...');
        }

        // Verificar se a fila existe e se pertence ao usuário logado
        $companyId = Auth::user()->id_company;
        $queue = Queue::where('id', $id)->where('id_company', $companyId)->firstOrFail();

        if (!$queue) {
            abort(403, 'Fila não encontrada...');
        }

        // Mostrar o formulário para clonagem da fila

        $data = [
            'subtitle' => 'Duplicar fila',
            'queue' => $queue,
        ];
        return view('main.queue_clone_form', $data);
    }

    public function cloneQueueSubmit(Request $request)
    {
        // Validar o formulário
        $request->validate([
            'name' => 'required|min:5|max:100'
        ], [
            'name.required' => 'O nome da fila é obrigatório',
            'name.min' => 'O nome da fila deve ter pelo menos 5 caracteres',
            'name.max' => 'O nome da fila deve ter no máximo 100 caracteres'
        ]);

        if (!$request->has('original_queue_id')) {
            abort(403, 'Operação inválida');
        }

        try {
            $queueId = Crypt::decrypt($request->original_queue_id);
        } catch (\Exception $e) {
            abort(403, 'Operação inválida');
        }

        // Verificar se a fila pertence ao utilizador autenticado
        $companyId = Auth::user()->id_company;
        $queue = Queue::where('id', $queueId)->where('id_company', $companyId)->firstOrFail();

        if (!$queue) {
            abort(403, 'Operação inválida');
        }

        // Verificar se o nome atribuido já existe na empresa em causa.
        $queueExists = Queue::where('name', trim($request->name))
            ->where('id_company', $companyId)->exists();

        if ($queueExists) {
            return redirect()->back()->withInput()->with('server_error', 'Já existe uma fila com este nome. Por favor, defina outro nome');
        }

        // Prepara dados para salvar
        $newQueue = new Queue();
        $newQueue->id_company = $companyId;
        $newQueue->name = trim($request->name);
        $newQueue->description = $queue->description;
        $newQueue->service_name = $queue->service_name;
        $newQueue->service_desk = $queue->service_desk;
        $newQueue->queue_prefix = $queue->queue_prefix;
        $newQueue->queue_total_digits = $queue->queue_total_digits;
        $newQueue->queue_colors = $queue->queue_colors;
        $newQueue->status = $queue->status;

        // gerar nova hash code
        $hash_code = hash('sha256', Str::random(40));
        while (Queue::where('hash_code', $hash_code)->exists()) {
            $hash_code = hash('sha256', Str::random(40));
        }

        $newQueue->hash_code = $hash_code;
        $newQueue->save();

        return redirect()->route('dashboard')->with('message', 'Fila adicionada com sucesso');
    }

    public function deleteQueue($id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (\Exception $e) {
            abort(403, 'ID de fila inválido...');
        }

        // Verificar se a fila existe e se pertence ao usuário logado
        $companyId = Auth::user()->id_company;
        $queue = Queue::where('id', $id)->where('id_company', $companyId)->firstOrFail();

        if (!$queue) {
            abort(403, 'Fila não encontrada...');
        }

        $data = [
            'subtitle' => 'Eliminar fila',
            'queue' => $queue,
        ];
        return view('main.queue_delete', $data);
    }

    public function deleteQueueConfirm(string $id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (\Exception $e) {
            abort(403, 'ID de fila inválido...');
        }

        // Verificar se a fila existe e se pertence ao usuário logado
        $companyId = Auth::user()->id_company;
        $queue = Queue::where('id', $id)->where('id_company', $companyId)->firstOrFail();

        if (!$queue) {
            abort(403, 'Fila não encontrada...');
        }

        $queue->delete();
        return redirect()->route('dashboard')->with(['message' => 'Fila eliminada com sucesso.']);
    }

    public function restoreQueue(string $id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (\Exception $e) {
            abort(403, 'ID de fila inválido...');
        }

        // Verificar se a fila existe e se pertence ao usuário logado
        $companyId = Auth::user()->id_company;
        $queue = Queue::withTrashed()
            ->where('id', $id)->where('id_company', $companyId)->firstOrFail();

        if (!$queue) {
            abort(403, 'Fila não encontrada...');
        }

        // Restaurar a fila excluída
        $queue->restore();
        return redirect()->route('dashboard')->with('message', 'Fila restaura com sucesso');

    }
}
