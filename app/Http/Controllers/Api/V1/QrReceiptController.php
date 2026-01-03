<?php

namespace App\Http\Controllers\Api\V1;

use App\Core\Repositories\LoyaltyWalletRepository;
use App\Core\Repositories\QrReceiptRepository;
use App\Helpers\FileHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ScanQrCodeRequest;
use App\Http\Resources\Api\V1\QrCodeRedemptionResource;
use App\Http\Resources\Api\V1\QrReceiptResource;
use App\Services\QrCodeRedemptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group QR Receipts APIs
 */
class QrReceiptController extends Controller
{
    public function __construct(
        private QrReceiptRepository $qrReceiptRepository,
        private LoyaltyWalletRepository $loyaltyWalletRepository,
        private FileHelper $fileHelper,
        private QrCodeRedemptionService $qrCodeRedemptionService
    ) {
    }

    /**
     * Scan QR code
     * 
     * Scan a QR code to redeem points. The QR code must be active, within validity dates, and not exceed usage limits.
     * 
     * @authenticated
     * 
     * @bodyParam code string required The QR code string to scan. Example: "QR-ABC123"
     * 
     * @response 200 {
     *   "success": true,
     *   "message": "QR code redeemed successfully.",
     *   "data": {
     *     "qr_code": {
     *       "id": 1,
     *       "code": "QR-ABC123",
     *       "title": "Welcome Bonus",
     *       "points_awarded": 50
     *     },
     *     "usage": {
     *       "id": 1,
     *       "used_at": "2026-01-03T18:30:00Z"
     *     },
     *     "remaining_limits": {
     *       "total": 99,
     *       "per_customer": 4
     *     }
     *   }
     * }
     * 
     * @response 400 {
     *   "success": false,
     *   "error": "QR_CODE_NOT_FOUND",
     *   "message": "QR code not found."
     * }
     * 
     * @response 400 {
     *   "success": false,
     *   "error": "QR_CODE_INACTIVE",
     *   "message": "QR code is not active."
     * }
     * 
     * @response 400 {
     *   "success": false,
     *   "error": "QR_CODE_NOT_STARTED",
     *   "message": "QR code has not started yet."
     * }
     * 
     * @response 400 {
     *   "success": false,
     *   "error": "QR_CODE_EXPIRED",
     *   "message": "QR code has expired."
     * }
     * 
     * @response 400 {
     *   "success": false,
     *   "error": "QR_CODE_PER_CUSTOMER_LIMIT_EXCEEDED",
     *   "message": "You have reached the maximum uses for this QR code."
     * }
     * 
     * @response 400 {
     *   "success": false,
     *   "error": "QR_CODE_TOTAL_LIMIT_EXCEEDED",
     *   "message": "QR code has reached its total usage limit."
     * }
     */
    public function scan(ScanQrCodeRequest $request): JsonResponse
    {
        $customer = auth('api')->user();

        if (!$customer) {
            return apiError('UNAUTHORIZED', 'unauthorized', 401);
        }

        $code = $request->validated()['code'];
        $result = $this->qrCodeRedemptionService->redeem($customer, $code);

        if (!$result['success']) {
            return apiError(
                $result['error_code'] ?? 'REDEMPTION_FAILED',
                $result['message'] ?? 'Redemption failed',
                400
            );
        }

        return apiSuccess(
            new QrCodeRedemptionResource($result['data']),
            $result['message']
        );
    }

    /**
     * Submit receipt manually (without image)
     * 
     * @deprecated This endpoint is deprecated. Use /scan with code parameter instead.
     * 
     * @authenticated
     * 
     * @bodyParam receipt_number string required Receipt number. Example: "RCP-123456"
     * @bodyParam amount number required Receipt amount (min 0). Example: 50.00
     * @bodyParam points_requested integer required Points requested (min 1). Example: 50
     * @bodyParam store_id integer optional Store ID. Example: 1
     * @bodyParam meta array optional Additional metadata. Example: {"notes": "Special order"}
     * 
     * @response 201 {
     *   "success": true,
     *   "message": "receipt_submitted",
     *   "data": {
     *     "id": 1,
     *     "receipt_number": "RCP-123456",
     *     "amount": 50.00,
     *     "points_requested": 50,
     *     "status": "pending"
     *   }
     * }
     */
    public function manual(Request $request): JsonResponse
    {
        $request->validate([
            'receipt_number' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'points_requested' => 'required|integer|min:1',
            'store_id' => 'nullable|exists:stores,id',
            'meta' => 'nullable|array',
        ]);

        $customer = auth('api')->user();

        $receipt = $this->qrReceiptRepository->create([
            'customer_id' => $customer->id,
            'store_id' => $request->input('store_id'),
            'receipt_number' => $request->input('receipt_number'),
            'amount' => $request->input('amount'),
            'points_requested' => $request->input('points_requested'),
            'meta' => $request->input('meta'),
            'status' => 'pending',
        ]);

        return apiSuccess(new QrReceiptResource($receipt), 'receipt_submitted', 201);
    }

    /**
     * Get receipt history
     * 
     * @authenticated
     * 
     * @queryParam per_page integer optional Items per page (max 100). Default: 15. Example: 20
     * 
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "receipt_number": "RCP-123456",
     *       "amount": 50.00,
     *       "points_requested": 50,
     *       "status": "approved"
     *     }
     *   ],
     *   "pagination": {
     *     "current_page": 1,
     *     "per_page": 15,
     *     "total": 10,
     *     "last_page": 1
     *   }
     * }
     */
    public function history(Request $request): JsonResponse
    {
        $customer = auth('api')->user();
        $perPage = min($request->query('per_page', 15), 100);

        $receipts = $this->qrReceiptRepository->getPaginatedForCustomer($customer->id, $perPage);

        return apiSuccess(
            QrReceiptResource::collection($receipts),
            null,
            200,
            [
                'current_page' => $receipts->currentPage(),
                'per_page' => $receipts->perPage(),
                'total' => $receipts->total(),
                'last_page' => $receipts->lastPage(),
            ]
        );
    }
}
