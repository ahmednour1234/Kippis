<?php

namespace Tests\Unit\Models;

use App\Core\Models\Customer;
use App\Core\Models\LoyaltyTransaction;
use App\Core\Models\LoyaltyWallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoyaltyWalletTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_add_points(): void
    {
        $wallet = LoyaltyWallet::factory()->create(['points' => 100]);

        $transaction = $wallet->addPoints(50, 'earned', 'Test points');

        $this->assertEquals(150, $wallet->fresh()->points);
        $this->assertNotNull($transaction);
        $this->assertEquals(50, $transaction->points);
    }

    public function test_can_deduct_points(): void
    {
        $wallet = LoyaltyWallet::factory()->create(['points' => 100]);

        $transaction = $wallet->deductPoints(30, 'redeemed', 'Test redemption');

        $this->assertEquals(70, $wallet->fresh()->points);
        $this->assertEquals(-30, $transaction->points);
    }

    public function test_has_transactions_relationship(): void
    {
        $wallet = LoyaltyWallet::factory()->create();
        LoyaltyTransaction::factory()->count(3)->create(['wallet_id' => $wallet->id]);

        $this->assertCount(3, $wallet->transactions);
    }
}

