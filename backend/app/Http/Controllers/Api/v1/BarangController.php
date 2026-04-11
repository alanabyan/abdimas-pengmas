<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use Illuminate\Http\Request;

class BarangController extends Controller
{
    public function index()
    {
        // Ambil semua data barang
        $barang = Barang::all();
        
        return response()->json([
            'status' => 'success',
            'data' => $barang
        ]);
    }
}