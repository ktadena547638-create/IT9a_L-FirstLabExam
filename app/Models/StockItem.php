<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * StockItem Model
 * 
 * Represents a warehouse inventory item with complete CRUD functionality.
 * 
 * Features:
 * - 7 query scopes for filtering and searching
 * - Status helper methods (badge, color, stock level checks)
 * - Inventory valuation calculations
 * - Route key name configuration for implicit binding
 * - Type casting for financial accuracy
 * - Predefined categories constant for validation consistency
 */
class StockItem extends Model
{
    protected $table = 'stock_items';

    protected $fillable = [
        'item_name',
        'sku',
        'category',
        'quantity',
        'unit_price',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'quantity' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Predefined inventory categories
     * 
     * Ensures consistency across the application:
     * - Used in validation rules
     * - Prevents category typos
     * - Makes filtering reliable
     * - Easy to extend in future
     */
    public const CATEGORIES = [
        'Tools',
        'Hardware',
        'Supplies',
        'Equipment',
        'Safety',
    ];

    /**
     * Get the route key for model binding
     * 
     * Explicitly declares that route {item} parameter uses 'id' field
     * This is already the default, but making it explicit:
     * - Clarifies implicit model binding for developers
     * - Makes refactoring easier (change one place)
     * - Improves IDE autocomplete support
     * - Example: route('items.show', $item) resolves using this key
     * 
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }

    /**
     * Get the status badge for the item
     * 
     * @return string
     */
    public function getStatusBadge(): string
    {
        return $this->quantity > 0 ? 'In Stock' : 'Out of Stock';
    }

    /**
     * Get the status badge color for Bootstrap
     * 
     * @return string
     */
    public function getStatusColor(): string
    {
        return $this->quantity > 0 ? 'success' : 'danger';
    }

    /**
     * Calculate total inventory value (quantity × unit_price)
     * 
     * @return float
     */
    public function getInventoryValue(): float
    {
        return (float) ($this->quantity * $this->unit_price);
    }

    /**
     * Check if item is in stock
     * 
     * @return bool
     */
    public function isInStock(): bool
    {
        return $this->quantity > 0;
    }

    /**
     * Check if item is low stock (less than 10 units)
     * 
     * @return bool
     */
    public function isLowStock(): bool
    {
        return $this->quantity > 0 && $this->quantity <= 10;
    }

    /**
     * Scope: Filter items by category
     * 
     * @param Builder $query
     * @param string $category
     * @return Builder
     */
    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * Scope: Filter items that are in stock
     * 
     * @param Builder $query
     * @return Builder
     */
    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('quantity', '>', 0);
    }

    /**
     * Scope: Filter items that are out of stock
     * 
     * @param Builder $query
     * @return Builder
     */
    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->where('quantity', 0);
    }

    /**
     * Scope: Filter items with low stock (≤ 10 units)
     * 
     * @param Builder $query
     * @return Builder
     */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereBetween('quantity', [1, 10]);
    }

    /**
     * Scope: Search items by name or SKU
     * 
     * @param Builder $query
     * @param string $searchTerm
     * @return Builder
     */
    public function scopeSearch(Builder $query, string $searchTerm): Builder
    {
        return $query->where('item_name', 'like', "%{$searchTerm}%")
                     ->orWhere('sku', 'like', "%{$searchTerm}%");
    }

    /**
     * Scope: Order by most recent first
     * 
     * @param Builder $query
     * @return Builder
     */
    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope: Order by most recent updates first
     * 
     * @param Builder $query
     * @return Builder
     */
    public function scopeMostRecent(Builder $query): Builder
    {
        return $query->orderBy('updated_at', 'desc');
    }

    /**
     * Boot the model and set up events
     * Useful for logging and audit trails
     */
    protected static function boot(): void
    {
        parent::boot();

        // Log creation timestamp
        static::creating(function (self $model) {
            // Could add logging here if needed
            // Log::info("Creating stock item: {$model->item_name}");
        });

        // Log updates
        static::updating(function (self $model) {
            // Log::info("Updating stock item: {$model->item_name}");
        });

        // Log deletions
        static::deleting(function (self $model) {
            // Log::info("Deleting stock item: {$model->item_name}");
        });
    }
}
