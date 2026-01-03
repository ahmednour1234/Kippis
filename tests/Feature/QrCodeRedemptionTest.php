<?php

namespace Tests\Feature;

use App\Core\Models\Customer;
use App\Core\Models\LoyaltyWallet;
use App\Core\Models\QrCode;
use App\Core\Models\QrCodeUsage;
use App\Services\QrCodeRedemptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QrCodeRedemptionTest extends TestCase
{
    use RefreshDatabase;

    private QrCodeRedemptionService $service;
    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(QrCodeRedemptionService::class);
        $this->customer = Customer::factory()->create();
        LoyaltyWallet::factory()->create(['customer_id' => $this->customer->id, 'points' => 0]);
    }

    public function test_admin_can_create_qr_code(): void
    {
        $qrCode = QrCode::create([
            'code' => 'TEST-CODE-123',
            'title' => 'Test QR Code',
            'points_awarded' => 50,
            'is_active' => true,
            'total_used_count' => 0,
        ]);

        $this->assertDatabaseHas('qr_codes', [
            'code' => 'TEST-CODE-123',
            'title' => 'Test QR Code',
            'points_awarded' => 50,
        ]);
    }

    public function test_customer_can_redeem_valid_qr_code(): void
    {
        $qrCode = QrCode::create([
            'code' => 'VALID-CODE',
            'title' => 'Valid Code',
            'points_awarded' => 100,
            'is_active' => true,
            'total_used_count' => 0,
        ]);

        $result = $this->service->redeem($this->customer, 'VALID-CODE');

        $this->assertTrue($result['success']);
        $this->assertEquals('QR code redeemed successfully.', $result['message']);
        $this->assertEquals(100, $result['data']['qr_code']['points_awarded']);

        // Check usage was recorded
        $this->assertDatabaseHas('qr_code_usages', [
            'qr_code_id' => $qrCode->id,
            'customer_id' => $this->customer->id,
        ]);

        // Check total_used_count incremented
        $qrCode->refresh();
        $this->assertEquals(1, $qrCode->total_used_count);

        // Check points were awarded
        $wallet = $this->customer->loyaltyWallet;
        $this->assertEquals(100, $wallet->points);
    }

    public function test_redeem_fails_when_code_not_found(): void
    {
        $result = $this->service->redeem($this->customer, 'NONEXISTENT-CODE');

        $this->assertFalse($result['success']);
        $this->assertEquals('QR_CODE_NOT_FOUND', $result['error_code']);
    }

    public function test_redeem_fails_when_code_inactive(): void
    {
        $qrCode = QrCode::create([
            'code' => 'INACTIVE-CODE',
            'points_awarded' => 50,
            'is_active' => false,
            'total_used_count' => 0,
        ]);

        $result = $this->service->redeem($this->customer, 'INACTIVE-CODE');

        $this->assertFalse($result['success']);
        $this->assertEquals('QR_CODE_INACTIVE', $result['error_code']);
    }

    public function test_redeem_fails_when_code_not_started(): void
    {
        $qrCode = QrCode::create([
            'code' => 'FUTURE-CODE',
            'points_awarded' => 50,
            'is_active' => true,
            'start_at' => now()->addDays(1),
            'total_used_count' => 0,
        ]);

        $result = $this->service->redeem($this->customer, 'FUTURE-CODE');

        $this->assertFalse($result['success']);
        $this->assertEquals('QR_CODE_NOT_STARTED', $result['error_code']);
    }

    public function test_redeem_fails_when_code_expired(): void
    {
        $qrCode = QrCode::create([
            'code' => 'EXPIRED-CODE',
            'points_awarded' => 50,
            'is_active' => true,
            'expires_at' => now()->subDay(),
            'total_used_count' => 0,
        ]);

        $result = $this->service->redeem($this->customer, 'EXPIRED-CODE');

        $this->assertFalse($result['success']);
        $this->assertEquals('QR_CODE_EXPIRED', $result['error_code']);
    }

    public function test_redeem_fails_when_per_customer_limit_exceeded(): void
    {
        $qrCode = QrCode::create([
            'code' => 'LIMITED-CODE',
            'points_awarded' => 50,
            'is_active' => true,
            'per_customer_limit' => 1,
            'total_used_count' => 0,
        ]);

        // First redemption should succeed
        $result1 = $this->service->redeem($this->customer, 'LIMITED-CODE');
        $this->assertTrue($result1['success']);

        // Second redemption should fail
        $result2 = $this->service->redeem($this->customer, 'LIMITED-CODE');
        $this->assertFalse($result2['success']);
        $this->assertEquals('QR_CODE_PER_CUSTOMER_LIMIT_EXCEEDED', $result2['error_code']);
    }

    public function test_redeem_fails_when_total_limit_exceeded(): void
    {
        $qrCode = QrCode::create([
            'code' => 'TOTAL-LIMIT-CODE',
            'points_awarded' => 50,
            'is_active' => true,
            'total_limit' => 1,
            'total_used_count' => 0,
        ]);

        $customer2 = Customer::factory()->create();
        LoyaltyWallet::factory()->create(['customer_id' => $customer2->id, 'points' => 0]);

        // First customer redeems
        $result1 = $this->service->redeem($this->customer, 'TOTAL-LIMIT-CODE');
        $this->assertTrue($result1['success']);

        // Second customer tries to redeem - should fail
        $result2 = $this->service->redeem($customer2, 'TOTAL-LIMIT-CODE');
        $this->assertFalse($result2['success']);
        $this->assertEquals('QR_CODE_TOTAL_LIMIT_EXCEEDED', $result2['error_code']);
    }

    public function test_unlimited_limits_work_correctly(): void
    {
        $qrCode = QrCode::create([
            'code' => 'UNLIMITED-CODE',
            'points_awarded' => 50,
            'is_active' => true,
            'per_customer_limit' => null,
            'total_limit' => null,
            'total_used_count' => 0,
        ]);

        // Customer can redeem multiple times
        $result1 = $this->service->redeem($this->customer, 'UNLIMITED-CODE');
        $this->assertTrue($result1['success']);

        $result2 = $this->service->redeem($this->customer, 'UNLIMITED-CODE');
        $this->assertTrue($result2['success']);

        $result3 = $this->service->redeem($this->customer, 'UNLIMITED-CODE');
        $this->assertTrue($result3['success']);

        $qrCode->refresh();
        $this->assertEquals(3, $qrCode->total_used_count);
    }

    public function test_total_used_count_increments_correctly(): void
    {
        $qrCode = QrCode::create([
            'code' => 'COUNT-TEST',
            'points_awarded' => 50,
            'is_active' => true,
            'total_used_count' => 0,
        ]);

        $customer2 = Customer::factory()->create();
        LoyaltyWallet::factory()->create(['customer_id' => $customer2->id, 'points' => 0]);

        $this->service->redeem($this->customer, 'COUNT-TEST');
        $qrCode->refresh();
        $this->assertEquals(1, $qrCode->total_used_count);

        $this->service->redeem($customer2, 'COUNT-TEST');
        $qrCode->refresh();
        $this->assertEquals(2, $qrCode->total_used_count);
    }

    public function test_customer_usage_count_increments(): void
    {
        $qrCode = QrCode::create([
            'code' => 'CUSTOMER-COUNT',
            'points_awarded' => 50,
            'is_active' => true,
            'per_customer_limit' => 5,
            'total_used_count' => 0,
        ]);

        // First redemption
        $this->service->redeem($this->customer, 'CUSTOMER-COUNT');
        $usageCount = QrCodeUsage::where('qr_code_id', $qrCode->id)
            ->where('customer_id', $this->customer->id)
            ->count();
        $this->assertEquals(1, $usageCount);

        // Second redemption
        $this->service->redeem($this->customer, 'CUSTOMER-COUNT');
        $usageCount = QrCodeUsage::where('qr_code_id', $qrCode->id)
            ->where('customer_id', $this->customer->id)
            ->count();
        $this->assertEquals(2, $usageCount);
    }
}

