<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QrCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'description',
        'points_awarded',
        'start_at',
        'expires_at',
        'is_active',
        'per_customer_limit',
        'total_limit',
        'total_used_count',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'points_awarded' => 'integer',
            'start_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
            'per_customer_limit' => 'integer',
            'total_limit' => 'integer',
            'total_used_count' => 'integer',
        ];
    }

    /**
     * Get all usages for this QR code.
     */
    public function usages(): HasMany
    {
        return $this->hasMany(QrCodeUsage::class);
    }

    /**
     * Get the admin who created this QR code.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    /**
     * Check if QR code is currently valid at given time.
     */
    public function isCurrentlyValid(?\DateTime $now = null): bool
    {
        $now = $now ?? now();

        if (!$this->is_active) {
            return false;
        }

        if ($this->start_at && $now < $this->start_at) {
            return false;
        }

        if ($this->expires_at && $now > $this->expires_at) {
            return false;
        }

        // Check if total limit exceeded
        if ($this->total_limit !== null && $this->total_used_count >= $this->total_limit) {
            return false;
        }

        return true;
    }

    /**
     * Calculate remaining total uses.
     */
    public function remainingTotalUses(): ?int
    {
        if ($this->total_limit === null) {
            return null; // Unlimited
        }

        return max(0, $this->total_limit - $this->total_used_count);
    }

    /**
     * Calculate remaining uses for a specific customer.
     */
    public function remainingForCustomer(int $customerId): ?int
    {
        if ($this->per_customer_limit === null) {
            return null; // Unlimited
        }

        $customerUsageCount = $this->usages()
            ->where('customer_id', $customerId)
            ->count();

        return max(0, $this->per_customer_limit - $customerUsageCount);
    }

    /**
     * Check if a customer can use this QR code.
     */
    public function canBeUsedByCustomer(int $customerId): bool
    {
        if (!$this->isCurrentlyValid()) {
            return false;
        }

        // Check per-customer limit
        if ($this->per_customer_limit !== null) {
            $customerUsageCount = $this->usages()
                ->where('customer_id', $customerId)
                ->count();

            if ($customerUsageCount >= $this->per_customer_limit) {
                return false;
            }
        }

        return true;
    }

    /**
     * Scope: Active QR codes.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Valid QR codes (active, within date range, not expired).
     */
    public function scopeValid($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_at')
                  ->orWhere('start_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope: Expired QR codes.
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
            ->where('expires_at', '<=', now());
    }
}

