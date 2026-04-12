<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Warga;
use Illuminate\Http\Request;

class WargaController extends Controller
{
    public function index()
    {
        // Ambil semua data warga dari database
        $warga = Warga::all();
        
        return response()->json([
            'status' => 'success',
            'data' => $warga
        ]);
    }
}