<?php

namespace App\Http\Controllers;

use App\Models\Queue;
use Illuminate\Support\Facades\Auth;

class MainController extends Controller
{
    public function index()
    {

        // carregar queues e tickets do usuário autenticado
        $queues = $this->getUserQueues();
        dd($queues);

        return view('dashboard', [
            'subtitle' => 'Dashboard',
            'queues' => $queues,
        ]);
    }

    private function getUserQueues()
    {
        return Queue::where('id_company', Auth::user()->id_company)
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->withCount('tickets')->get()->sortBy('name');
    }
}
