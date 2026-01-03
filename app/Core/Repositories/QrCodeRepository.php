<?php

namespace App\Core\Repositories;

use App\Core\Models\QrCode;

class QrCodeRepository
{
    /**
     * Find QR code by code string.
     */
    public function findByCode(string $code): ?QrCode
    {
        return QrCode::where('code', $code)->first();
    }

    /**
     * Increment total usage count for a QR code.
     */
    public function incrementUsageCount(QrCode $qrCode): bool
    {
        return $qrCode->increment('total_used_count');
    }

    /**
     * Get usage count for a specific customer.
     */
    public function getUsageCountForCustomer(QrCode $qrCode, int $customerId): int
    {
        return $qrCode->usages()
            ->where('customer_id', $customerId)
            ->count();
    }

    /**
     * Create a new QR code.
     */
    public function create(array $data): QrCode
    {
        return QrCode::create($data);
    }

    /**
     * Update QR code.
     */
    public function update(QrCode $qrCode, array $data): bool
    {
        return $qrCode->update($data);
    }
}

