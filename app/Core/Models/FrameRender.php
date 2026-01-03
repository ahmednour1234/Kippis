<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FrameRender extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'frame_id',
        'original_image_path',
        'rendered_image_path',
        'width',
        'height',
        'format',
    ];

    protected function casts(): array
    {
        return [
            'width' => 'integer',
            'height' => 'integer',
        ];
    }

    /**
     * Relationship: FrameRender belongs to Frame.
     *
     * @return BelongsTo
     */
    public function frame(): BelongsTo
    {
        return $this->belongsTo(Frame::class);
    }

    /**
     * Relationship: FrameRender belongs to Customer.
     *
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(\App\Core\Models\Customer::class);
    }
}

