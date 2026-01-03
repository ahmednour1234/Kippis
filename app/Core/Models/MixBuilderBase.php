<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MixBuilderBase extends Model
{
    use HasFactory;

    protected $fillable = [
        'mix_builder_id',
        'product_id',
    ];

    /**
     * Get the product that is used as a base.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

