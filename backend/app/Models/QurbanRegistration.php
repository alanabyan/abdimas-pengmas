<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QurbanRegistration extends Model
{
    protected $table = 'qurban_registrations';

    protected $fillable = [
        'nama',
        'telepon',
        'email',
        'jumlah',
        'nominal_awal',
        'nominal_terbaru',
        'selisih_nominal',
        'bagian',
        'kuota_total',
        'kuota_tersedia',
        'catatan',
        'status',
        'tanggal_konfirmasi',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'jumlah' => 'integer',
        'nominal_awal' => 'integer',
        'nominal_terbaru' => 'integer',
        'selisih_nominal' => 'integer',
        'kuota_total' => 'integer',
        'kuota_tersedia' => 'integer',
        'tanggal_konfirmasi' => 'datetime',
    ];
}
