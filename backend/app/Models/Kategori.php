<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    use HasFactory;

    protected $table = 'kategoris';

    // Tidak ada updated_at karena kategori jarang diubah
    // tapi tetap aktifkan untuk konsistensi
    public $timestamps = true;

    protected $fillable = [
        'nama',
        'deskripsi',
        'ikon',
    ];

    // ── Relasi ──────────────────────────────────────────────────────────

    public function barangs()
    {
        return $this->hasMany(Barang::class, 'kategori_id');
    }

    // ── Accessor ────────────────────────────────────────────────────────

    /** Jumlah barang dalam kategori ini */
    public function getJumlahBarangAttribute(): int
    {
        return $this->barangs()->count();
    }
}
