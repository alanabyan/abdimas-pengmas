<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QurbanPayment extends Model
{
    protected $fillable = [
        'qurban_registration_id',
        'payment_number',
        'amount',
        'paid_at',
        'note',
    ];

    protected $casts = [
        'payment_number' => 'integer',
        'amount' => 'integer',
        'paid_at' => 'date',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(QurbanRegistration::class, 'qurban_registration_id');
    }
}
