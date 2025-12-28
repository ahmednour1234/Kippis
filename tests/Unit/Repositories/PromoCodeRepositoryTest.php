<?php

namespace Tests\Unit\Repositories;

use App\Core\Models\Customer;
use App\Core\Models\PromoCode;
use App\Core\Repositories\PromoCodeRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromoCodeRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private PromoCodeRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new PromoCodeRepository();
    }

    public function test_can_find_valid_promo_code(): void
    {
        $promoCode = PromoCode::factory()->create([
            'code' => 'TEST10',
            'valid_from' => now()->subDay(),
            'valid_to' => now()->addDay(),
            'active' => true,
        ]);

        $found = $this->repository->findValidByCode('test10');

        $this->assertNotNull($found);
        $this->assertEquals($promoCode->id, $found->id);
    }

    public function test_returns_null_for_invalid_code(): void
    {
        $found = $this->repository->findValidByCode('INVALID');

        $this->assertNull($found);
    }

    public function test_validates_for_customer(): void
    {
        $customer = Customer::factory()->create();
        $promoCode = PromoCode::factory()->create([
            'minimum_order_amount' => 50.00,
            'usage_per_user_limit' => 2,
        ]);

        $isValid = $this->repository->isValidForCustomer($promoCode, $customer->id, 100.00);

        $this->assertTrue($isValid);
    }

    public function test_rejects_below_minimum_order(): void
    {
        $customer = Customer::factory()->create();
        $promoCode = PromoCode::factory()->create([
            'minimum_order_amount' => 100.00,
        ]);

        $isValid = $this->repository->isValidForCustomer($promoCode, $customer->id, 50.00);

        $this->assertFalse($isValid);
    }
}

