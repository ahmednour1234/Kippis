<?php

namespace App\Core\Services;

use App\Core\Models\Customer;
use App\Core\Models\CustomerOtp;
use App\Core\Repositories\CustomerOtpRepository;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    public function __construct(
        private CustomerOtpRepository $otpRepository
    ) {
    }

    /**
     * Generate a 6-digit OTP.
     * In dev environment, returns '111111' for testing.
     *
     * @return string
     */
    public function generateOtp(): string
    {
        // In dev environment, return fixed OTP for testing
        if (config('app.env') === 'dev' || config('app.env') === 'local') {
            return '111111';
        }

        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Create and save OTP for customer.
     *
     * @param Customer $customer
     * @param string $type
     * @param int $expiryMinutes
     * @return CustomerOtp
     */
    public function createOtp(Customer $customer, string $type, int $expiryMinutes = 5): CustomerOtp
    {
        // Delete any existing OTPs of the same type for this email
        $this->otpRepository->deleteByEmail($customer->email, $type);

        $otp = $this->generateOtp();

        return $this->otpRepository->create([
            'customer_id' => $customer->id,
            'email' => $customer->email,
            'otp' => $otp,
            'type' => $type,
            'expires_at' => now()->addMinutes($expiryMinutes),
        ]);
    }

    /**
     * Validate OTP.
     * In dev environment, accepts '111111' as valid OTP.
     *
     * @param string $email
     * @param string $otp
     * @param string $type
     * @return CustomerOtp
     * @throws \App\Http\Exceptions\InvalidOtpException
     */
    public function validateOtp(string $email, string $otp, string $type): CustomerOtp
    {
        // In dev environment, accept '111111' as valid OTP
        $isDev = config('app.env') === 'dev' || config('app.env') === 'local';
        
        if ($isDev && $otp === '111111') {
            // First try to find existing OTP record
            $otpRecord = $this->otpRepository->findByEmailAndType($email, $type);
            
            if ($otpRecord) {
                // If record exists, check if it's not expired
                if (!$otpRecord->isExpired()) {
                    return $otpRecord;
                }
            }
            
            // If no valid record exists, verify customer exists and create/update OTP record
            $customer = Customer::where('email', $email)->first();
            if (!$customer) {
                throw new \App\Http\Exceptions\InvalidOtpException();
            }
            
            // Delete any expired OTPs and create a new one
            $this->otpRepository->deleteByEmail($email, $type);
            $otpRecord = $this->otpRepository->create([
                'customer_id' => $customer->id,
                'email' => $email,
                'otp' => '111111',
                'type' => $type,
                'expires_at' => now()->addMinutes(5),
            ]);
            
            return $otpRecord;
        }

        // Normal OTP validation
        $otpRecord = $this->otpRepository->findByEmailAndOtp($email, $otp, $type);

        if (!$otpRecord) {
            throw new \App\Http\Exceptions\InvalidOtpException();
        }

        if ($otpRecord->isExpired()) {
            throw new \App\Http\Exceptions\InvalidOtpException();
        }

        return $otpRecord;
    }

    /**
     * Send OTP to customer via email.
     * In dev environment, skips email sending and just logs the static OTP.
     *
     * @param string $email
     * @param string $otp
     * @param string|null $type OTP type (verification, password_reset)
     * @return void
     */
    public function sendOtp(string $email, string $otp, ?string $type = null): void
    {
        $isDev = config('app.env') === 'dev' || config('app.env') === 'local';

        // In dev environment, don't send email, just log the static OTP
        if ($isDev) {
            Log::info('OTP (dev mode - not sent via email)', [
                'email' => $email,
                'otp' => $otp,
                'type' => $type,
                'timestamp' => now(),
                'note' => 'In dev environment, OTP is static (111111) and not sent via email',
            ]);
            return;
        }

        // In production, send OTP via email
        try {
            Mail::to($email)->send(new OtpMail($otp, $type));

            Log::info('OTP sent via email', [
                'email' => $email,
                'otp' => $otp,
                'type' => $type,
                'timestamp' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send OTP email', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
