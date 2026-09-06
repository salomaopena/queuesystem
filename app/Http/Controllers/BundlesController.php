<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Bundle;
use Illuminate\Support\Facades\Crypt;
use App\Models\Queue;

class BundlesController extends Controller
{
    public function index()
    {
        $data = [
            'subtitle' => 'Bundles',
            'bundles' => auth()->user()->company->bundles()->withTrashed()->get(),
        ];
        return view('bundles.home', $data);
    }

    public function createBundle()
    {
        $data = [
            'subtitle' => 'Criar bundle',
            'queues' => auth()->user()->company->queues()->get()
        ];

        return view('bundles.create_bundle_form', $data);
    }

    public function createBundleSubmit(Request $request)
    {
        // validação do formulário
        $request->validate(
            [
                'bundle_name' => 'required|max:100|min:5',
                'credential_username' => 'required|string|size:64',
                'credential_password' => 'required|string|size:64',
            ],
            [
                'bundle_name.required' => 'O nome do bundle é obrigatório',
                'bundle_name.max' => 'O nome do bundle não pode exceder 100 caracteres',
                'bundle_name.min' => 'O nome do bundle deve ter pelo menos 5 caracteres',
                'credential_username.string' => 'O nome de usuário da credencial deve ser uma string',
                'credential_username.size' => 'O nome de usuário da credencial deve ter exatamente 64 caracteres',
                'credential_username.required' => 'O nome de usuário da credencial é obrigatório',
                'credential_password.required' => 'A senha da credencial é obrigatória',
                'credential_password.string' => 'A senha da credencial deve ser uma string',
                'credential_password.size' => 'A senha da credencial deve ter exatamente 64 caracteres',
            ]
        );

        // Verificar se o queue_list é um json válido e não está vazio
        if (
            empty($request->queue_list) ||
            json_decode($request->queue_list, true) === null ||
            empty(json_decode($request->queue_list, true))
        ) {
            return redirect()->back()
                ->withErrors(['queue_list' => 'A lista de filas é obrigatória e deve ser um array.'])
                ->withInput();
        }

        // Verificar se o nome do bundle já existe para a mesma empresa
        $bundle_name = $request->bundle_name;
        $existingBundle = auth()
            ->user()
            ->company
            ->bundles()
            ->where('name', $bundle_name)
            ->first();

        if ($existingBundle) {
            return redirect()
                ->back()
                ->withErrors(['bundle_name' => 'O nome do bundle já está em uso.'])
                ->withInput();
        }

        // Verificar se a queue na queue_list existe e pertence à mesma empresa do usuário
        $queue_list = json_decode($request->queue_list, true);

        $queues_hash_codes = array_map(function ($queue) {
            return $queue['hash_code'] ?? null;
        }, $queue_list);

        $valid_queues = auth()
            ->user()
            ->company
            ->queues()
            ->whereIn('hash_code', $queues_hash_codes)
            ->pluck('hash_code')
            ->toArray();

        if (count($valid_queues) !== count($queues_hash_codes)) {
            return redirect()
                ->back()
                ->withErrors(['queue_list' => 'Uma ou mais filas na lista não existem ou não pertencem à sua empresa.'])
                ->withInput();
        }

        // criar a nova bundle
        $newBundle = new Bundle();
        $newBundle->id_company = auth()->user()->company->id;
        $newBundle->name = $bundle_name;
        $newBundle->queues = json_encode($valid_queues);
        $newBundle->credential_username = $request->credential_username;
        $newBundle->credential_password = bcrypt($request->credential_password);
        $newBundle->save();

        return redirect()->route('bundles.home')->with('success', 'Bundle criado com sucesso!');
    }

    public function generateCredentialValue(string $num_chars)
    {
        if (!is_numeric($num_chars) || $num_chars <= 0 || $num_chars > 64) {
            return response()->json(['error' => 'Número inválido de caracteres'], 400);
        }
        $credentialValue = Str::random($num_chars);

        while (Bundle::where('credential_username', $credentialValue)->exists()) {
            $credentialValue = Str::random($num_chars);
        }

        return response()->json(['hash' => $credentialValue]);
    }

    public function editBundle(string $id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (\Exception $e) {
            abort(403, 'ID de fila inválido...');
        }

        // Encontrar o bundle pelo seu ID
        $bundle  = Bundle::find($id);

        if (!$bundle || $bundle->id_company !== auth()->user()->company->id) {
            return redirect()->back()->with(['error' => 'Bundle não encontrado ou não pertence à sua empresa.']);
        }

        $data = [
            'subtitle' => 'Editar Bundle',
            'bundle' => $bundle,
            'bundle_queue_list' => $this->getBundleQueueList($id),
            'queues' => auth()->user()->company->queues()->get()
        ];
        return view('bundles.bundle_edit_form', $data);
    }

    public function editBundleSubmit(Request $request)
    {
        // validação do formulário
        $request->validate(
            [
                'bundle_name' => 'required|max:100|min:5',
                'queue_list' => 'required',
            ],
            [
                'bundle_name.required' => 'O nome do bundle é obrigatório',
                'bundle_name.max' => 'O nome do bundle não pode exceder 100 caracteres',
                'bundle_name.min' => 'O nome do bundle deve ter pelo menos 5 caracteres',
                'queue_list.required' => 'A lista de filas é obrigatória',
            ]
        );

        try {
            $bundle_id = Crypt::decrypt($request->bundle_id);
        } catch (\Exception $e) {
            abort(403, 'ID de fila inválido...');
        }


        // Verificar se o bundle ID é valido
        if (empty($bundle_id) || !is_numeric($bundle_id)) {
            return redirect()->route('bundles.home')->with(['error' => 'ID do bundle inválido.']);
        }
        // Tentar decriptar o ID do bundle

        // Verificar se o queue_list é um json válido e não está vazio
        if (
            empty($request->queue_list) ||
            json_decode($request->queue_list, true) === null ||
            empty(json_decode($request->queue_list, true))
        ) {
            return redirect()->back()
                ->withErrors(['queue_list' => 'A lista de filas é obrigatória e deve ser um array.'])
                ->withInput();
        }

        // Verificar se o nome do bundle já existe para a mesma empresa
        $bundle_name = $request->bundle_name;
        $existingBundle = auth()
            ->user()
            ->company
            ->bundles()
            ->where('name', $bundle_name)
            ->where('id', '!=', $bundle_id) // Excluir o bundle atual da verificação
            ->first();

        if ($existingBundle) {
            return redirect()
                ->back()
                ->withErrors(['bundle_name' => 'O nome do bundle já está em uso.'])
                ->withInput();
        }

        // Verificar se a queue na queue_list existe e pertence à mesma empresa do usuário
        $queue_list = json_decode($request->queue_list, true);

        $queues_hash_codes = array_map(function ($queue) {
            return $queue['hash_code'] ?? null;
        }, $queue_list);

        $valid_queues = auth()
            ->user()
            ->company
            ->queues()
            ->whereIn('hash_code', $queues_hash_codes)
            ->pluck('hash_code')
            ->toArray();

        if (count($valid_queues) !== count($queues_hash_codes)) {
            return redirect()
                ->back()
                ->withErrors(['queue_list' => 'Uma ou mais filas na lista não existem ou não pertencem à sua empresa.'])
                ->withInput();
        }

        // Atualizar o bundle
        $bundle = Bundle::find($bundle_id);
        if (!$bundle || $bundle->id_company !== auth()->user()->company->id) {
            return redirect()
                ->route('bundles.home')
                ->with(['error' => 'Bundle não encontrado ou acesso negado.']);
        }


        $bundle->name = $bundle_name;
        $bundle->queues = json_encode($valid_queues);
        $bundle->save();
        return redirect()->route('bundles.home')->with('success', 'Bundle atualizado com sucesso!');
    }

    private function getBundleQueueList(int $bundleId)
    {
        $queues = json_decode(Bundle::find($bundleId)->queues, true);
        $companyQueues = Queue::where('id_company', auth()->user()->company->id)
            ->whereIn('hash_code', $queues)->get()->toArray();

        $queueList = [];

        foreach ($companyQueues as $queue) {
            $queueList[] = [
                'hash_code' => $queue['hash_code'],
                'name' => $queue['name'],
            ];
        }

        return $queueList;
    }

    public function deleteBundle(string $id)
    {
        // implementar lógica de exclusão de bundle
        try {
            $id = Crypt::decrypt($id);
        } catch (\Exception $e) {
            redirect()
                ->route('bundles.home')
                ->with(['error' => 'ID de bundle inválido...']);
        }

        // Encontrar o bundle pelo seu ID e verificar se pertence à mesma empresa do usuário
        $bundle  = Bundle::find($id);

        if (!$bundle || $bundle->id_company !== auth()->user()->company->id) {
            return redirect()
                ->route('bundles.home')
                ->with(['error' => 'Bundle não encontrado ou acesso negado.']);
        }

        // prepara os dados para a view de confirmação de exclusão
        $data = [
            'subtitle' => 'Confirmar exclusão de bundle',
            'bundle' => $bundle,
        ];
        return view('bundles.bundle_delete', $data);
    }

    public function deleteBundleConfirm(string $id)
    {
        // implementar lógica de exclusão de bundle
        try {
            $id = Crypt::decrypt($id);
        } catch (\Exception $e) {
            redirect()
                ->route('bundles.home')
                ->with(['error' => 'ID de bundle inválido...']);
        }

        // Encontrar o bundle pelo seu ID e verificar se pertence à mesma empresa do usuário
        $bundle  = Bundle::find($id);

        if (!$bundle || $bundle->id_company !== auth()->user()->company->id) {
            return redirect()
                ->route('bundles.home')
                ->with(['error' => 'Bundle não encontrado ou acesso negado.']);
        }

        // Excluir o bundle
        $bundle->delete();
        return redirect()->route('bundles.home')->with('success', 'Bundle excluído com sucesso!');
    }

    public function restoreBundle(string $id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (\Exception $e) {
            redirect()
                ->route('bundles.home')
                ->with(['error' => 'ID de bundle inválido...']);
        }

        // Encontrar o bundle pelo seu ID e verificar se pertence à mesma empresa do usuário
        $bundle  = Bundle::withTrashed()
            ->find($id);


        if (!$bundle || $bundle->id_company !== auth()->user()->company->id) {
            return redirect()
                ->route('bundles.home')
                ->with(['error' => 'Bundle não encontrado ou acesso negado.']);
        }

        // Restaurar o bundle
        $bundle->restore();
        return redirect()
            ->route('bundles.home')
            ->with('success', 'Bundle restaurado com sucesso!');
    }

    
}
