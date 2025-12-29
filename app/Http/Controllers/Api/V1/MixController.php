<?php

namespace App\Http\Controllers\Api\V1;

use App\Core\Repositories\ModifierRepository;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ModifierResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Mix Builder APIs
 */
class MixController extends Controller
{
    public function __construct(
        private ModifierRepository $modifierRepository
    ) {
    }

    /**
     * Get mix builder options
     * 
     * Returns all available modifiers grouped by type (sweetness, fizz, caffeine, extra).
     * 
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "sweetness": [
     *       {
     *         "id": 1,
     *         "name": "Low Sugar",
     *         "price": 0.00
     *       }
     *     ],
     *     "fizz": [],
     *     "caffeine": [],
     *     "extra": []
     *   }
     * }
     */
    public function options(): JsonResponse
    {
        $modifiers = $this->modifierRepository->getGroupedByType();

        $data = [];
        foreach (['sweetness', 'fizz', 'caffeine', 'extra'] as $type) {
            $data[$type] = ModifierResource::collection($modifiers[$type]);
        }

        return apiSuccess($data);
    }

    /**
     * Preview mix price calculation
     * 
     * Calculate the total price for a custom mix based on base price and selected modifiers.
     * 
     * @bodyParam base_price number required Base product price (min 0). Example: 15.00
     * @bodyParam modifiers array optional Array of modifier objects. Example: [{"id": 1, "level": 2}]
     * @bodyParam modifiers.*.id integer required Modifier ID. Example: 1
     * @bodyParam modifiers.*.level integer optional Modifier level (min 1). Default: 1. Example: 2
     * 
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "base_price": 15.00,
     *     "modifiers_price": 5.00,
     *     "total": 20.00
     *   }
     * }
     */
    public function preview(Request $request): JsonResponse
    {
        $request->validate([
            'base_price' => 'required|numeric|min:0',
            'modifiers' => 'array',
            'modifiers.*.id' => 'required|exists:modifiers,id',
            'modifiers.*.level' => 'nullable|integer|min:1',
        ]);

        $basePrice = (float) $request->input('base_price');
        $modifiers = $request->input('modifiers', []);
        $totalModifierPrice = 0;

        foreach ($modifiers as $modifierData) {
            $modifier = $this->modifierRepository->findById($modifierData['id']);
            if (!$modifier || !$modifier->is_active) {
                continue;
            }

            $level = $modifierData['level'] ?? 1;
            if ($modifier->max_level && $level > $modifier->max_level) {
                $level = $modifier->max_level;
            }

            $totalModifierPrice += $modifier->price * $level;
        }

        $total = $basePrice + $totalModifierPrice;

        return apiSuccess([
            'base_price' => $basePrice,
            'modifiers_price' => $totalModifierPrice,
            'total' => $total,
        ]);
    }
}
