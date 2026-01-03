<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FrameRenderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $fileHelper = new \App\Helpers\FileHelper();

        return [
            'frame_id' => $this->frame_id,
            'rendered_url' => $fileHelper->getUrl($this->rendered_image_path, 'public'),
            'original_url' => $fileHelper->getUrl($this->original_image_path, 'public'),
            'width' => $this->width,
            'height' => $this->height,
            'format' => $this->format,
        ];
    }
}
