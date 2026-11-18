<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class InfaqSubmission extends Model
{
    use HasUuids;

    protected $fillable = [
        'nama',
        'telepon',
        'blok_rumah',
        'public_token',
        'jumlah',
        'jenis',
        'proof_path',
        'status',
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        'public_token',
        'proof_path',
    ];

    protected $casts = [
        'jumlah' => 'integer',
    ];

    protected function uniqueIds(): array
    {
        return ['public_token'];
    }
}