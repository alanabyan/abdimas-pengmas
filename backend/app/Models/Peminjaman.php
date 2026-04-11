<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Peminjaman extends Model
{
    use HasFactory;

    protected $table = 'peminjamans';

    // Status yang tersedia
    const STATUS_MENUNGGU    = 'Menunggu';
    const STATUS_AKTIF       = 'Aktif';
    const STATUS_SELESAI     = 'Selesai';
    const STATUS_TERLAMBAT   = 'Terlambat';
    const STATUS_BATAL       = 'Batal';
    const STATUS_RUSAK_HILANG = 'Rusak/Hilang';

    const STATUS_AKTIF_LIST = [
        self::STATUS_MENUNGGU,
        self::STATUS_AKTIF,
        self::STATUS_TERLAMBAT,
    ];

    protected $fillable = [
        'barang_id',
        'warga_id',
        'marbot_id',
        'keperluan',
        'jumlah',
        'kondisi_pinjam',
        'tgl_pinjam',
        'tgl_rencana_kembali',
        'tgl_kembali_aktual',
        'kondisi_kembali',
        'status',
        'catatan',
    ];

    protected $casts = [
        'jumlah'              => 'integer',
        'tgl_pinjam'          => 'date',
        'tgl_rencana_kembali' => 'date',
        'tgl_kembali_aktual'  => 'date',
    ];

    // ── Relasi ──────────────────────────────────────────────────────────

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    public function warga()
    {
        return $this->belongsTo(Warga::class, 'warga_id');
    }

    public function marbot()
    {
        return $this->belongsTo(Marbot::class, 'marbot_id');
    }

    // ── Accessor ────────────────────────────────────────────────────────

    /** true jika melewati tanggal rencana kembali dan belum dikembalikan */
    public function getTerlambatAttribute(): bool
    {
        return in_array($this->status, [self::STATUS_AKTIF, self::STATUS_MENUNGGU])
            && $this->tgl_rencana_kembali
            && $this->tgl_rencana_kembali->isPast();
    }

    /** Jumlah hari terlambat (0 jika belum terlambat) */
    public function getHariTerlambatAttribute(): int
    {
        if (! $this->terlambat) return 0;
        return $this->tgl_rencana_kembali->diffInDays(Carbon::today());
    }

    // ── Scope ────────────────────────────────────────────────────────────

    public function scopeAktif($query)
    {
        return $query->whereIn('status', self::STATUS_AKTIF_LIST);
    }

    public function scopeBelumDikembalikan($query)
    {
        return $query->whereIn('status', [self::STATUS_AKTIF, self::STATUS_TERLAMBAT])
            ->whereNull('tgl_kembali_aktual');
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByTanggal($query, ?string $dari, ?string $sampai)
    {
        if ($dari)   $query->where('tgl_pinjam', '>=', $dari);
        if ($sampai) $query->where('tgl_pinjam', '<=', $sampai);
        return $query;
    }

    // ── Helper ───────────────────────────────────────────────────────────

    public function bisaDiEdit(): bool
    {
        return $this->status === self::STATUS_MENUNGGU;
    }

    public function bisaDibatalkan(): bool
    {
        return in_array($this->status, [self::STATUS_MENUNGGU, self::STATUS_AKTIF]);
    }
}
