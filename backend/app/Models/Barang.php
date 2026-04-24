<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;

    protected $table = 'barangs';
    protected $appends = ['stok_dipinjam', 'tersedia'];

    protected $fillable = [
        'kategori_id',
        'nama_barang',
        'deskripsi',
        'stok_total',
        'stok_tersedia',
        'kondisi',
        'foto_url',
        'lokasi',
    ];

    protected $casts = [
        'stok_total'    => 'integer',
        'stok_tersedia' => 'integer',
    ];

    // ── Relasi ──────────────────────────────────────────────────────────

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    public function peminjamans()
    {
        return $this->hasMany(Peminjaman::class, 'barang_id');
    }

    /** Peminjaman yang sedang berjalan (stok sedang terpinjam) */
    public function peminjamanAktif()
    {
        return $this->hasMany(Peminjaman::class, 'barang_id')
            ->whereIn('status', ['Menunggu', 'Aktif', 'Terlambat']);
    }

    // ── Accessor ────────────────────────────────────────────────────────

    /** true jika minimal ada 1 unit yang bisa dipinjam */
    public function getTersediaAttribute(): bool
    {
        return $this->stok_tersedia > 0;
    }

    /** Jumlah unit yang sedang dipinjam */
    public function getStokDipinjamAttribute(): int
    {
        return $this->stok_total - $this->stok_tersedia;
    }

    // ── Scope ────────────────────────────────────────────────────────────

    public function scopeCari($query, string $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('nama_barang', 'like', "%{$keyword}%")
                ->orWhere('deskripsi', 'like', "%{$keyword}%")
                ->orWhere('lokasi', 'like', "%{$keyword}%");
        });
    }

    public function scopeByKategori($query, int $kategoriId)
    {
        return $query->where('kategori_id', $kategoriId);
    }

    public function scopeTersedia($query)
    {
        return $query->where('stok_tersedia', '>', 0);
    }

    // ── Helper ───────────────────────────────────────────────────────────

    /** Kurangi stok saat barang dipinjam */
    public function kurangiStok(int $jumlah): void
    {
        $this->decrement('stok_tersedia', $jumlah);
    }

    /** Tambah stok kembali saat barang dikembalikan */
    public function tambahStok(int $jumlah): void
    {
        $this->increment('stok_tersedia', $jumlah);
    }
}
