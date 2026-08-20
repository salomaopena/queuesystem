<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BundlesController extends Controller
{
    public function index()
    {
        $data = [
            'subtitle' => 'Bundles',
            'bundles' => collect(['test' => 'Teste']) // 
        ];
        return view('bundles.home', $data);
    }
}
