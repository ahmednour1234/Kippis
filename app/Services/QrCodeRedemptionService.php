<?php

namespace App\Services;

use App\Core\Models\Customer;
use App\Core\Models\QrCode;
use App\Core\Models\QrCodeUsage;
use App\Core\Repositories\LoyaltyWalletRepository;
use App\Core\Repositories\QrCodeRepository;
use Illuminate\Support\Facades\DB;

class QrCodeRedemptionService
{
    public function __construct(
        private QrCodeRepository $qrCodeRepository,
        private LoyaltyWalletRepository $loyaltyWalletRepository
    ) {
    }

    /**
     * Validate QR code exists.
     */
    public function validateCode(string $code): ?QrCode
    {
        return $this->qrCodeRepository->findByCode($code);
    }

    /**
     * Check eligibility for redemption.
     *
     * @return array ['eligible' => bool, 'error' => string|null, 'error_code' => string|null]
     */
    public function checkEligibility(QrCode $qrCode, Customer $customer): array
    {
        if (!$qrCode->is_active) {
            return [
                'eligible' => false,
                'error' => 'QR code is not active.',
                'error_code' => 'QR_CODE_INACTIVE',
            ];
        }

        $now = now();

        if ($qrCode->start_at && $now < $qrCode->start_at) {
            return [
                'eligible' => false,
                'error' => 'QR code has not started yet.',
                'error_code' => 'QR_CODE_NOT_STARTED',
            ];
        }

        if ($qrCode->expires_at && $now > $qrCode->expires_at) {
            return [
                'eligible' => false,
                'error' => 'QR code has expired.',
                'error_code' => 'QR_CODE_EXPIRED',
            ];
        }

        // Check total limit
        if ($qrCode->total_limit !== null && $qrCode->total_used_count >= $qrCode->total_limit) {
            return [
                'eligible' => false,
                'error' => 'QR code has reached its total usage limit.',
                'error_code' => 'QR_CODE_TOTAL_LIMIT_EXCEEDED',
            ];
        }

        // Check per-customer limit
        if ($qrCode->per_customer_limit !== null) {
            $customerUsageCount = $this->qrCodeRepository->getUsageCountForCustomer($qrCode, $customer->id);
            if ($customerUsageCount >= $qrCode->per_customer_limit) {
                return [
                    'eligible' => false,
                    'error' => 'You have reached the maximum uses for this QR code.',
                    'error_code' => 'QR_CODE_PER_CUSTOMER_LIMIT_EXCEEDED',
                ];
            }
        }

        return [
            'eligible' => true,
            'error' => null,
            'error_code' => null,
        ];
    }

    /**
     * Redeem QR code for customer.
     *
     * @return array ['success' => bool, 'message' => string, 'data' => array|null, 'error' => string|null, 'error_code' => string|null]
     */
    public function redeem(Customer $customer, string $code): array
    {
        // Find QR code
        $qrCode = $this->validateCode($code);
        if (!$qrCode) {
            return [
                'success' => false,
                'message' => 'QR code not found.',
                'data' => null,
                'error' => 'QR code not found.',
                'error_code' => 'QR_CODE_NOT_FOUND',
            ];
        }

        // Check eligibility
        $eligibility = $this->checkEligibility($qrCode, $customer);
        if (!$eligibility['eligible']) {
            return [
                'success' => false,
                'message' => $eligibility['error'],
                'data' => null,
                'error' => $eligibility['error'],
                'error_code' => $eligibility['error_code'],
            ];
        }

        // Atomic redemption with transaction and row locking
        try {
            return DB::transaction(function () use ($qrCode, $customer) {
                // Lock the QR code row to prevent race conditions
                $lockedQrCode = QrCode::lockForUpdate()->find($qrCode->id);

                // Re-check eligibility after lock (double-check)
                $eligibility = $this->checkEligibility($lockedQrCode, $customer);
                if (!$eligibility['eligible']) {
                    return [
                        'success' => false,
                        'message' => $eligibility['error'],
                        'data' => null,
                        'error' => $eligibility['error'],
                        'error_code' => $eligibility['error_code'],
                    ];
                }

                // Create usage record
                $usage = QrCodeUsage::create([
                    'qr_code_id' => $lockedQrCode->id,
                    'customer_id' => $customer->id,
                    'used_at' => now(),
                    'metadata' => [
                        'redeemed_at' => now()->toIso8601String(),
                    ],
                ]);

                // Increment total usage count
                $this->qrCodeRepository->incrementUsageCount($lockedQrCode);

                // Refresh to get updated count
                $lockedQrCode->refresh();

                // Award points if applicable
                $pointsAwarded = 0;
                if ($lockedQrCode->points_awarded && $lockedQrCode->points_awarded > 0) {
                    $wallet = $this->loyaltyWalletRepository->getOrCreateForCustomer($customer->id);
                    $this->loyaltyWalletRepository->addPoints(
                        $wallet,
                        $lockedQrCode->points_awarded,
                        'earned',
                        "Points from QR code: {$lockedQrCode->title ?? $lockedQrCode->code}",
                        'qr_code',
                        $lockedQrCode->id
                    );
                    $pointsAwarded = $lockedQrCode->points_awarded;
                }

                // Calculate remaining limits
                $remainingTotal = $lockedQrCode->remainingTotalUses();
                $remainingForCustomer = $lockedQrCode->remainingForCustomer($customer->id);

                return [
                    'success' => true,
                    'message' => 'QR code redeemed successfully.',
                    'data' => [
                        'qr_code' => [
                            'id' => $lockedQrCode->id,
                            'code' => $lockedQrCode->code,
                            'title' => $lockedQrCode->title,
                            'points_awarded' => $pointsAwarded,
                        ],
                        'usage' => [
                            'id' => $usage->id,
                            'used_at' => $usage->used_at->toIso8601String(),
                        ],
                        'remaining_limits' => [
                            'total' => $remainingTotal,
                            'per_customer' => $remainingForCustomer,
                        ],
                    ],
                    'error' => null,
                    'error_code' => null,
                ];
            });
        } catch (\Exception $e) {
            \Log::error('QR code redemption failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'customer_id' => $customer->id,
                'code' => $code,
            ]);

            return [
                'success' => false,
                'message' => 'Redemption failed. Please try again.',
                'data' => null,
                'error' => 'Redemption failed.',
                'error_code' => 'REDEMPTION_FAILED',
            ];
        }
    }
}

