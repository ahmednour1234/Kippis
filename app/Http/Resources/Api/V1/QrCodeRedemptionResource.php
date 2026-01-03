<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QrCodeRedemptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'qr_code' => [
                'id' => $this->resource['qr_code']['id'] ?? null,
                'code' => $this->resource['qr_code']['code'] ?? null,
                'title' => $this->resource['qr_code']['title'] ?? null,
                'points_awarded' => $this->resource['qr_code']['points_awarded'] ?? 0,
            ],
            'usage' => [
                'id' => $this->resource['usage']['id'] ?? null,
                'used_at' => $this->resource['usage']['used_at'] ?? null,
            ],
            'remaining_limits' => [
                'total' => $this->resource['remaining_limits']['total'] ?? null,
                'per_customer' => $this->resource['remaining_limits']['per_customer'] ?? null,
            ],
        ];
    }
}

