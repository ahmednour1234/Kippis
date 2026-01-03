<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QrCodeUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'qr_code_id',
        'customer_id',
        'used_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'used_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    /**
     * Get the QR code that was used.
     */
    public function qrCode(): BelongsTo
    {
        return $this->belongsTo(QrCode::class);
    }

    /**
     * Get the customer who used the QR code.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}

