<?php

namespace App\Http\Controllers\Api\V1;

use App\Core\Repositories\FrameRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\FramesRenderRequest;
use App\Http\Resources\Api\V1\FrameResource;
use App\Http\Resources\Api\V1\FrameRenderResource;
use App\Services\FrameRenderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Frames APIs
 */
class FrameController extends Controller
{
    public function __construct(
        private FrameRepository $frameRepository,
        private FrameRenderService $frameRenderService
    ) {
    }

    /**
     * Get list of active frames
     *
     * Returns only active and valid frames (within date range).
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Summer Frame",
     *       "name_ar": "إطار صيفي",
     *       "name_en": "Summer Frame",
     *       "thumbnail_url": "https://example.com/storage/frames/thumbnails/..."
     *     }
     *   ]
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $frames = $this->frameRepository->getActiveFrames();

        return apiSuccess(FrameResource::collection($frames));
    }

    /**
     * Render frame on uploaded image
     *
     * Upload a photo and apply a frame overlay. Returns the rendered image URL.
     *
     * @bodyParam frame_id integer required The frame ID. Example: 1
     * @bodyParam image file required The image file (JPEG/PNG, max 10MB). Example: (binary)
     * @bodyParam output_size string optional Output size in format WIDTHxHEIGHT. Example: 1080x1080
     * @bodyParam format string optional Output format (jpg, png). Default: jpg. Example: jpg
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "frame_id": 1,
     *     "rendered_url": "https://example.com/storage/frames/renders/...",
     *     "original_url": "https://example.com/storage/frames/originals/...",
     *     "width": 1080,
     *     "height": 1080
     *   }
     * }
     *
     * @response 422 {
     *   "success": false,
     *   "error": {
     *     "code": "validation_failed",
     *     "message": "The frame is not currently available."
     *   }
     * }
     */
    public function render(FramesRenderRequest $request): JsonResponse
    {
        try {
            $frame = $this->frameRepository->findById($request->validated('frame_id'));
            
            if (!$frame) {
                return apiError('frame_not_found', 'Frame not found', 404);
            }

            $customer = auth('api')->user();
            
            $result = $this->frameRenderService->render(
                $customer,
                $frame,
                $request->file('image'),
                [
                    'output_size' => $request->validated('output_size'),
                    'format' => $request->validated('format', 'jpg'),
                ]
            );

            return apiSuccess([
                'frame_id' => $frame->id,
                'rendered_url' => $result['rendered_url'],
                'original_url' => $result['original_url'],
                'width' => $result['width'],
                'height' => $result['height'],
            ]);
        } catch (\Exception $e) {
            return apiError('render_failed', $e->getMessage(), 422);
        }
    }
}
