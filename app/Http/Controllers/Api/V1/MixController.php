<?php

namespace App\Http\Controllers\Api\V1;

use App\Core\Repositories\ModifierRepository;
use App\Services\MixPriceCalculator;
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
        private ModifierRepository $modifierRepository,
        private MixPriceCalculator $mixPriceCalculator
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
     * Calculate the total price for a custom mix based on a configuration snapshot.
     * Provide either `configuration.base_id` (preferred) or `configuration.base_price` for backwards compatibility.
     *
     * @bodyParam configuration object required Configuration snapshot for the mix. Example: {"base_id":1,"modifiers":[{"id":2,"level":1}],"extras":[3]}
     * @bodyParam configuration.base_id integer The base product id. Example: 1
     * @bodyParam configuration.base_price number Deprecated. Raw base price. Example: 15.00
     * @bodyParam configuration.modifiers array Array of modifier objects. Example: [{"id": 1, "level": 2}]
     * @bodyParam configuration.modifiers.*.id integer required Modifier ID. Example: 1
     * @bodyParam configuration.modifiers.*.level integer optional Modifier level (min 0). Example: 2
     * @bodyParam configuration.extras array optional Array of extra product ids. Example: [3,4]
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "total": 20.00,
     *     "breakdown": [{"label":"Base","amount":15.00},{"label":"Extra","amount":5.00}]
     *   }
     * }
     */
    public function preview(Request $request): JsonResponse
    {
        $request->validate([
            'configuration' => 'required|array',
            'configuration.base_id' => 'nullable|exists:products,id',
            'configuration.base_price' => 'nullable|numeric|min:0',
            'configuration.modifiers' => 'array',
            'configuration.modifiers.*.id' => 'required_with:configuration.modifiers|exists:modifiers,id',
            'configuration.modifiers.*.level' => 'nullable|integer|min:0',
            'configuration.extras' => 'array',
            'configuration.extras.*' => 'exists:products,id',
        ]);

        $configuration = $request->input('configuration', []);

        try {
            $result = $this->mixPriceCalculator->calculate($configuration);
        } catch (\Exception $e) {
            return apiError('INVALID_CONFIGURATION', $e->getMessage(), 400);
        }

        return apiSuccess(['total' => $result['total'], 'breakdown' => $result['breakdown']]);
    }
}
