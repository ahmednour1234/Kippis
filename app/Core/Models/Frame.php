<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Database\Factories\FrameFactory;

class Frame extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return FrameFactory::new();
    }

    protected $fillable = [
        'name_json',
        'thumbnail_path',
        'overlay_path',
        'is_active',
        'sort_order',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'name_json' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /**
     * Get localized name for a specific locale.
     *
     * @param string $locale
     * @param string|null $fallback
     * @return string
     */
    public function getName(string $locale = 'en', ?string $fallback = null): string
    {
        $name = $this->name_json;
        
        if (is_array($name) && isset($name[$locale])) {
            return $name[$locale];
        }

        return $fallback ?? ($name['en'] ?? '');
    }

    /**
     * Check if frame is currently valid (active and within date range).
     *
     * @param \DateTime|null $now
     * @return bool
     */
    public function isCurrentlyValid(?\DateTime $now = null): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = $now ?? new \DateTime();

        if ($this->starts_at && $now < new \DateTime($this->starts_at)) {
            return false;
        }

        if ($this->ends_at && $now > new \DateTime($this->ends_at)) {
            return false;
        }

        return true;
    }

    /**
     * Relationship: Frame has many renders.
     *
     * @return HasMany
     */
    public function renders(): HasMany
    {
        return $this->hasMany(FrameRender::class);
    }

    /**
     * Scope: Only active frames.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Valid frames (active and within date range).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeValid($query)
    {
        $now = now();
        
        return $query->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')
                  ->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')
                  ->orWhere('ends_at', '>=', $now);
            });
    }

    /**
     * Scope: Expired frames.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpired($query)
    {
        return $query->where('ends_at', '<', now());
    }
}

