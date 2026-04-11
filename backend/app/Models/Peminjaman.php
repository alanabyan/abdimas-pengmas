<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peminjaman extends Model
{
    use HasFactory;

    // Kasih tau Laravel kalau tabelnya namanya 'peminjamans'
    protected $table = 'peminjamans';

    // Daftar kolom yang boleh diisi (sesuaikan dengan migration lu)
    protected $fillable = [
        'barang_id',
        'warga_id',
        'marbot_id',
        'keperluan',
        'jumlah',
        'kondisi_pinjam',
        'kondisi_kembali',
        'tgl_pinjam',
        'tgl_rencana_kembali',
        'tgl_kembali',
        'status',
    ];

    // --- RELASI (Biar lu bisa tau siapa yang pinjam & barang apa yang dipinjam) ---

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function warga()
    {
<<<<<<< Updated upstream
        return $this->belongsTo(Warga::class);
=======
        return $this->belongsTo(Warga::class, 'warga_id', 'id_warga');
>>>>>>> Stashed changes
    }
    
    public function marbot()
    {
        return $this->belongsTo(User::class, 'marbot_id');
    }
}