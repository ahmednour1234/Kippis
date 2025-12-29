<?php

namespace App\Http\Controllers\Api\V1;

use App\Core\Repositories\LoyaltyWalletRepository;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\LoyaltyWalletResource;
use Illuminate\Http\JsonResponse;

/**
 * @group Loyalty APIs
 */
class LoyaltyController extends Controller
{
    public function __construct(
        private LoyaltyWalletRepository $loyaltyWalletRepository
    ) {
    }

    /**
     * Get customer loyalty wallet
     * 
     * @authenticated
     * 
     * Returns the customer's loyalty points balance and recent transactions.
     * 
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "customer_id": 123,
     *     "balance": 500,
     *     "transactions": [
     *       {
     *         "id": 1,
     *         "type": "earned",
     *         "points": 100,
     *         "created_at": "2025-12-21T10:00:00Z"
     *       }
     *     ]
     *   }
     * }
     */
    public function index(): JsonResponse
    {
        $customer = auth('api')->user();
        $wallet = $this->loyaltyWalletRepository->getOrCreateForCustomer($customer->id);

        $wallet->load(['transactions' => function ($query) {
            $query->latest()->limit(10);
        }]);

        return apiSuccess(new LoyaltyWalletResource($wallet));
    }
}
