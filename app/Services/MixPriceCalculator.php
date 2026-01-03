<?php

namespace App\Services;

use App\Core\Models\Modifier;
use App\Core\Models\Product;

class MixPriceCalculator
{
    /**
     * Calculate total price for a mix configuration.
     *
     * @param array $configuration Configuration array with base_id/base_price, modifiers, and extras
     * @return array Array with 'total' and 'breakdown' keys
     * @throws \InvalidArgumentException
     */
    public function calculate(array $configuration): array
    {
        $breakdown = [];
        $total = 0;

        // Calculate base price
        $basePrice = $this->calculateBasePrice($configuration);
        if ($basePrice > 0) {
            $breakdown[] = [
                'label' => 'Base',
                'amount' => $basePrice,
            ];
            $total += $basePrice;
        }

        // Calculate modifier prices
        if (isset($configuration['modifiers']) && is_array($configuration['modifiers'])) {
            foreach ($configuration['modifiers'] as $modifierConfig) {
                $modifierPrice = $this->calculateModifierPrice($modifierConfig);
                if ($modifierPrice > 0) {
                    $modifier = Modifier::find($modifierConfig['id']);
                    $label = $modifier ? $modifier->getName(app()->getLocale()) : 'Modifier';
                    
                    $breakdown[] = [
                        'label' => $label,
                        'amount' => $modifierPrice,
                    ];
                    $total += $modifierPrice;
                }
            }
        }

        // Calculate extra product prices
        if (isset($configuration['extras']) && is_array($configuration['extras'])) {
            foreach ($configuration['extras'] as $extraId) {
                $extraPrice = $this->calculateExtraPrice($extraId);
                if ($extraPrice > 0) {
                    $product = Product::find($extraId);
                    $label = $product ? $product->getName(app()->getLocale()) : 'Extra';
                    
                    $breakdown[] = [
                        'label' => $label,
                        'amount' => $extraPrice,
                    ];
                    $total += $extraPrice;
                }
            }
        }

        return [
            'total' => round($total, 2),
            'breakdown' => $breakdown,
        ];
    }

    /**
     * Calculate base price from configuration.
     *
     * @param array $configuration
     * @return float
     * @throws \InvalidArgumentException
     */
    protected function calculateBasePrice(array $configuration): float
    {
        // Try to get base_id first
        if (isset($configuration['base_id']) && $configuration['base_id']) {
            $product = Product::active()->find($configuration['base_id']);
            
            if (!$product) {
                throw new \InvalidArgumentException('Base product not found or inactive.');
            }

            return (float) $product->base_price;
        }

        // Fallback to base_price if provided
        if (isset($configuration['base_price']) && $configuration['base_price'] !== null) {
            $basePrice = (float) $configuration['base_price'];
            
            if ($basePrice < 0) {
                throw new \InvalidArgumentException('Base price cannot be negative.');
            }

            return $basePrice;
        }

        // If neither is provided, throw exception
        throw new \InvalidArgumentException('Either base_id or base_price must be provided.');
    }

    /**
     * Calculate modifier price.
     *
     * @param array $modifierConfig Modifier configuration with 'id' and optional 'level'
     * @return float
     * @throws \InvalidArgumentException
     */
    protected function calculateModifierPrice(array $modifierConfig): float
    {
        if (!isset($modifierConfig['id'])) {
            throw new \InvalidArgumentException('Modifier ID is required.');
        }

        $modifier = Modifier::active()->find($modifierConfig['id']);

        if (!$modifier) {
            throw new \InvalidArgumentException("Modifier with ID {$modifierConfig['id']} not found or inactive.");
        }

        $level = isset($modifierConfig['level']) ? (int) $modifierConfig['level'] : 1;

        // Validate level doesn't exceed max_level
        if ($modifier->max_level !== null && $level > $modifier->max_level) {
            throw new \InvalidArgumentException(
                "Modifier level {$level} exceeds maximum level {$modifier->max_level}."
            );
        }

        if ($level < 0) {
            throw new \InvalidArgumentException('Modifier level cannot be negative.');
        }

        return (float) ($modifier->price * $level);
    }

    /**
     * Calculate extra product price.
     *
     * @param int $productId
     * @return float
     * @throws \InvalidArgumentException
     */
    protected function calculateExtraPrice(int $productId): float
    {
        $product = Product::active()->find($productId);

        if (!$product) {
            throw new \InvalidArgumentException("Extra product with ID {$productId} not found or inactive.");
        }

        return (float) $product->base_price;
    }
}

