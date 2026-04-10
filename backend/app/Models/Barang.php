<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $fillable = ['nama_barang', 'kategori_id', 'stok', 'kondisi', 'deskripsi'];
}