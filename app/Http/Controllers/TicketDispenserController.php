<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bundle;
use Illuminate\Support\Facades\Hash;
use App\Models\Queue;

class TicketDispenserController extends Controller
{
    public function index()
    {
        $bundleData = $this->getBundleData(session()->get('ticket_dispenser_credential'))->getData();
        //$bundleData = $this->getBundleData('9459549589')->getData();

        $data = [
            'title' => 'Dispenser',
            'bundle' => $bundleData
        ];
        return view('dispenser.index', $data);
    }

    public function credentials()
    {
        $data = [
            'title' => 'Dispenser Credentials',
        ];
        return view('dispenser.credentials_form', $data);
    }

    public function credentialsSubmit(Request $request)
    {
        $request->validate(
            [
                'credential_username' => 'required|size:64',
                'credential_password' => 'required|size:64',
            ],
            [
                'credential_username.required' => 'O campo "Credential username" é obrigatório.',
                'credential_username.size' => 'O campo "Credential username" deve ter exatamente 64 caracteres.',
                'credential_password.required' => 'O campo "Credential password" é obrigatório.',
                'credential_password.size' => 'O campo "Credential password" deve ter exatamente 64 caracteres.',
            ]
        );

        // Verificar se as credenciais são válidas (exemplo: comparar com valores armazenados no banco de dados)
        $result = Bundle::where('credential_username', $request->input('credential_username'))
            ->first();

        if (!$result) {
            return redirect()
                ->back()
                ->with(['server_error' => 'Credenciais inválidas.'])
                ->withInput();
        }

        // verificar se a senha fornecida corresponde à senha armazenada (hash)
        if (!Hash::check($request->input('credential_password'), $result->credential_password)) {
            return redirect()
                ->back()
                ->with(['server_error' => 'Credenciais inválidas.'])
                ->withInput();
        }

        // Colocar na sessão as credenciais válidas
        session()->put('ticket_dispenser_credential', $request->input('credential_username'));

        return redirect()->route('dispenser');
    }

    private function getBundleData(string $credential_username)
    {
        // prepara um extrutura json com toda a informação sobre o bundle.

        $bundle = Bundle::where('credential_username', $credential_username)
            ->first();

        if (!$bundle) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Bundle não encontrado.',
                    'code' => 404
                ],
                404,
                [
                    'Content-Type' => 'application/json',
                    'Access-Control-Allow-Origin' => '*',
                    'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
                    'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
                ],
                JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
            );
        }

        // Obter todas as filas associadas ao bundle
        $queues = Queue::whereIn('hash_code', json_decode($bundle->queues))
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->get();

        if ($queues->isEmpty()) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Nenhuma fila ativa encontrada para este bundle.',
                    'code' => 404
                ],
                404,
                [
                    'Content-Type' => 'application/json',
                    'Access-Control-Allow-Origin' => '*',
                    'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
                    'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
                ],
                JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
            );
        }

        // Prepara os dados a serem retornados como JSON

        return response()->json(
            [
                'status' => 'success',
                'message' => 'success',
                'code' => 200,
                'queues' => $queues->map(function ($queue) {
                    return [
                        'id' => $queue->id,
                        'name' => $queue->name,
                        'description' => $queue->description,
                        'service' => $queue->service_name,
                        'desk' => $queue->service_desk,
                        'prefix' => $queue->queue_prefix,
                        'digits' => $queue->queue_total_digits,
                        'colors' => json_decode($queue->queue_colors, true)
                    ];
                })->values(),
            ],
            200,
            [
                'Content-Type' => 'application/json',
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
            ],
            JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
        );
    }
}
