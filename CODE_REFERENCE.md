# 📖 COMPLETE CODE REFERENCE GUIDE
**All optimized code snippets for quick review**

---

## TABLE OF CONTENTS
1. routes/web.php (COMPLETE)
2. app/Models/StockItem.php (KEY SNIPPETS)
3. app/Http/Requests/StoreItemRequest.php (KEY SNIPPETS)
4. app/Http/Controllers/ItemController.php (KEY SNIPPETS)
5. Architecture Explanation

---

## 1. ROUTES/WEB.PHP - COMPLETE FILE

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Models\StockItem;

/**
 * Route Configuration for Warehouse Inventory System
 * 
 * Implements RESTful routing with implicit model binding for stock items.
 * All CRUD operations follow Laravel resource routing conventions.
 * 
 * Architecture decisions:
 * - Explicit model binding for clarity
 * - Rate limiting on export endpoint (10 requests/minute)
 * - Single responsibility: items only (no legacy routes)
 */

// Redirect root to items index
Route::get('/', function () {
    return redirect()->route('items.index');
});

// ============================================================================
// EXPLICIT ROUTE MODEL BINDING
// ============================================================================
// Declares that {item} route parameter = StockItem model instance
// Laravel automatically resolves the ID to the full model object
// Example: /items/5/edit → route('items.edit', $item) where $item->id === 5
Route::model('item', StockItem::class);

// ============================================================================
// RESTful Resource Routes (7 routes auto-generated)
// ============================================================================
// GET    /items              → index()    Show all items with pagination
// GET    /items/create       → create()   Show form for new item
// POST   /items              → store()    Save new item to database
// GET    /items/{item}       → show()     Show single item details
// GET    /items/{item}/edit  → edit()     Show edit form with pre-filled data
// PUT    /items/{item}       → update()   Save changes to database
// DELETE /items/{item}       → destroy()  Delete item from database
Route::resource('items', ItemController::class);

// ============================================================================
// ADDITIONAL ROUTES
// ============================================================================
// CSV export with rate limiting: 10 exports per minute maximum
// Uses streaming response for memory efficiency with large datasets
Route::get('/items-export/csv', [ItemController::class, 'export'])
    ->name('items.export')
    ->middleware('throttle:10,1');
```

**Key Points:**
- ✅ Explicit model binding makes {item} parameter explicit
- ✅ Rate limiting prevents CSV export abuse
- ✅ All 7 RESTful routes documented
- ✅ Clear separation of concerns

---

## 2. APP/MODELS/STOCKITEM.PHP - KEY SECTIONS

### The Model Class Header
```php
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
```

### Property Definitions
```php
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
```

### ⭐ NEW: CATEGORIES CONSTANT
```php
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
```

### ⭐ NEW: ROUTE KEY METHOD
```php
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
```

### Existing Methods (Reference)
```php
    // 7 Scopes for query optimization
    public function scopeByCategory(Builder $query, string $category): Builder { ... }
    public function scopeInStock(Builder $query): Builder { ... }
    public function scopeOutOfStock(Builder $query): Builder { ... }
    public function scopeLowStock(Builder $query): Builder { ... }
    public function scopeSearch(Builder $query, string $searchTerm): Builder { ... }
    public function scopeLatest(Builder $query): Builder { ... }

    // 3 Helper methods
    public function getStatusBadge(): string { ... }
    public function getStatusColor(): string { ... }
    public function getInventoryValue(): float { ... }
    public function isInStock(): bool { ... }
    public function isLowStock(): bool { ... }
```

---

## 3. APP/HTTP/REQUESTS/STOREITEMREQUEST.php - KEY SECTIONS

### ⭐ UPDATED: rules() method
```php
    public function rules(): array
    {
        // Get the current item for unique SKU validation during updates
        // Using safe null coalescing operator (?->) for cleaner syntax
        $stockItem = $this->route('stock_item');
        $itemId = $stockItem?->id;

        return [
            // Item name: Required, text-only, 3-255 characters
            // Prevents empty/too-short/too-long product names
            'item_name' => [
                'required',
                'string',
                'min:3',
                'max:255',
            ],

            // SKU: Unique stock keeping unit identifier
            // Format: Uppercase letters [A-Z], numbers [0-9], hyphens [-]
            // Examples: TOOL-001, PDR-002, WRN-BLUE-10
            // Ignores current item ID during updates (allows same item to be edited)
            'sku' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Z0-9\-]+$/', // Pattern: Only [A-Z0-9-] characters
                Rule::unique('stock_items', 'sku')->ignore($itemId), // Allow re-submission without unique violation
            ],

            // Category: One of predefined warehouse categories
            // Uses constant from StockItem model to avoid hardcoding
            // Ensures data consistency across application
            'category' => [
                'required',
                'string',
                'max:100',
                Rule::in(\App\Models\StockItem::CATEGORIES), // ⭐ USES MODEL CONSTANT
            ],

            // Quantity: Non-negative integer count
            // Min 0 (out of stock is valid state)
            // Max 999,999 (prevents decimal storage)
            'quantity' => [
                'required',
                'integer',
                'min:0',
                'max:999999',
            ],

            // Unit Price: Positive decimal currency amount
            // Range: $0.01 to $99,999.99
            // Regex ensures proper decimal format (e.g., 19.99 not 19.999)
            'unit_price' => [
                'required',
                'numeric',
                'min:0.01',
                'max:99999.99',
                'regex:/^\d+(\.\d{1,2})?$/', // Validates: 10 or 10.99 format only
            ],
        ];
    }
```

### Existing Methods (Reference)
```php
    public function authorize(): bool { return true; }
    public function messages(): array { /* 22 custom messages */ }
    protected function prepareForValidation(): void { /* Auto-uppercase SKU */ }
    public function attributes(): array { /* Human-readable field names */ }
```

---

## 4. APP/HTTP/CONTROLLERS/ITEMCONTROLLER.php - KEY SECTIONS

### ⭐ NEW: Imports
```php
use App\Models\StockItem;
use App\Http\Requests\StoreItemRequest;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;  // ← NEW: For transactions
```

### ⭐ UPDATED: destroy() method
```php
    /**
     * Delete the specified stock item from the database.
     * 
     * Uses database transaction for data consistency:
     * - If delete fails, transaction automatically rolls back
     * - Prevents partial deletes if foreign key constraints fail
     * - Captures full deletion context in audit log
     * 
     * @param StockItem $stock_item
     * @return RedirectResponse
     */
    public function destroy(StockItem $stock_item): RedirectResponse
    {
        try {
            $itemName = $stock_item->item_name;
            $sku = $stock_item->sku;
            $itemId = $stock_item->id;

            // Database transaction ensures atomic operation
            // All-or-nothing: either item is deleted or database stays unchanged
            DB::transaction(function () use ($stock_item) {
                $stock_item->delete();
            });

            // Log deletion with complete context for audit trail
            Log::warning(
                'Stock item deleted',
                [
                    'id' => $itemId,
                    'sku' => $sku,
                    'name' => $itemName,
                    'deleted_at' => now(),
                ]
            );

            return redirect()->route('items.index')
                ->with('success', "✓ Stock item '{$itemName}' has been permanently deleted.");
        } catch (\Exception $e) {
            Log::error(
                'Error deleting stock item',
                [
                    'item_id' => $stock_item->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]
            );
            return redirect()->back()
                ->with('error', 'Failed to delete stock item. Please try again.');
        }
    }
```

### Existing Methods (Reference)
```php
    public function index(Request $request): View { /* List with search/filter */ }
    public function create(): View { /* Show create form */ }
    public function store(StoreItemRequest $request): RedirectResponse { /* Save new */ }
    public function show(StockItem $stock_item): View { /* Show detail */ }
    public function edit(StockItem $stock_item): View { /* Show edit form */ }
    public function update(StoreItemRequest $request, StockItem $stock_item): RedirectResponse { /* Save changes */ }
    public function export() { /* Stream CSV */ }
```

---

## 5. ARCHITECTURAL PATTERNS USED

### Pattern 1: Resource Routing
```
RESTful Routes for StockItem:
GET    /items              ← index()   (list all)
GET    /items/create       ← create()  (show form)
POST   /items              ← store()   (save)
GET    /items/{item}       ← show()    (view one)
GET    /items/{item}/edit  ← edit()    (edit form)
PUT    /items/{item}       ← update()  (update)
DELETE /items/{item}       ← destroy() (delete)
```

### Pattern 2: Implicit Model Binding
```php
// In routes/web.php
Route::model('item', StockItem::class);

// In controller method
public function show(StockItem $stock_item): View // Laravel auto-resolves!
{
    // $stock_item is already the model instance
    // No need to find it manually
}

// In blade template
route('items.show', $item)  // Laravel extracts $item->id automatically
```

### Pattern 3: Form Requests
```php
// Validation logic separated from controller
public function store(StoreItemRequest $request)  // Validation happens first
{
    $validated = $request->validated();  // Data is already validated
    StockItem::create($validated);       // Just save it
}
```

### Pattern 4: Query Scopes
```php
// Chainable query builders
$items = StockItem::search($term)
    ->byCategory($category)
    ->inStock()
    ->latest()
    ->paginate(15);

// Instead of:
$items = StockItem::where('item_name', 'like', "%$term%")
    ->orWhere('sku', 'like', "%$term%")
    ->where('category', $category)
    ->where('quantity', '>', 0)
    ->orderBy('created_at', 'desc')
    ->paginate(15);
```

### Pattern 5: Database Transactions
```php
DB::transaction(function () use ($stock_item) {
    $stock_item->delete();
    // Multiple operations here are atomic
    // Either all succeed or all rollback
});
```

### Pattern 6: Structured Logging
```php
// Professional logging with context
Log::warning(
    'Stock item deleted',  // Message
    [                      // Context array
        'id' => $itemId,
        'sku' => $sku,
        'name' => $itemName,
        'deleted_at' => now(),
    ]
);

// Enables searching: Log::where('context.sku', 'TOOL-001')
// Shows in dashboards and aggregators
```

---

## 6. KEY ARCHITECTURAL DECISIONS

### Decision 1: Why Model Constant for Categories?
```php
// ❌ Before: Hardcoded in 3 places
// Request validation
Rule::in(['Tools', 'Hardware', ...])

// View dropdown
@foreach (['Tools', 'Hardware', ...] as $cat)

// Controller filtering
if (in_array($category, ['Tools', 'Hardware', ...]))

// ✅ After: Single source of truth
public const CATEGORIES = ['Tools', 'Hardware', ...];

// Used everywhere as
Rule::in(StockItem::CATEGORIES)
```

**Why?** DRY principle - change once, updates everywhere

---

### Decision 2: Why Database Transactions?
```php
// ❌ Without transaction
$stock_item->delete();  // Success
createAuditLog();       // Fails - but item already deleted!

// ✅ With transaction
DB::transaction(function() {
    $stock_item->delete();
    createAuditLog();
    // Both must succeed together
    // If one fails, both rollback
});
```

**Why?** ACID guarantees - Atomicity for data integrity

---

### Decision 3: Why Explicit Route Binding?
```php
// ❌ Implicit (works but unclear)
// Route::resource('items', ItemController::class);

// ✅ Explicit (self-documenting)
Route::model('item', StockItem::class);
Route::resource('items', ItemController::class);
```

**Why?** Clarifies that {item} = StockItem for future developers

---

## 7. COMMON PATTERNS IN THIS CODE

### Pattern: Try-Catch on DB Operations
```php
try {
    // Database operation
    $stock_item->delete();
    // Success handling
    return redirect()->with('success', '✓ Deleted');
} catch (\Exception $e) {
    // Error handling
    Log::error('Error', ['error' => $e->getMessage()]);
    return redirect()->back()->with('error', 'Failed');
}
```

### Pattern: Type Hinting
```php
public function show(StockItem $stock_item): View
              ↑↑↑↑↑           ↓↓↓↓↓↓↓↓↓↓ ↑↑↑↑
           Return type        Parameter type     Type
```

### Pattern: Fluent Interface
```php
$items = StockItem::latest()    // Scope
    ->search($term)              // Scope
    ->byCategory($cat)           // Scope
    ->paginate(15);              // Terminal method
```

### Pattern: Chain Methods
```php
return redirect()->route('items.index')
    ->with('success', 'Item deleted');
    ↑            ↑
    Method 1     Method 2
```

---

## 8. QUICK REFERENCE: WHAT EACH FILE DOES

| File | Purpose | Key Code |
|------|---------|----------|
| **routes/web.php** | URL → Controller mapping | `Route::resource()` |
| **StockItem.php** | Database model & scopes | 7 scopes + 5 methods |
| **StoreItemRequest.php** | Input validation | 13 rules |
| **ItemController.php** | Business logic | 7 methods (CRUD + export) |
| **Blade Views** | HTML templates | 5 views + modals |
| **Migration** | Database schema | 7 indexes |

---

## 9. TEST COMMAND EXAMPLES

**Verify model constant:**
```bash
php artisan tinker
>>> \App\Models\StockItem::CATEGORIES
```

**Check syntax:**
```bash
php -l app/Models/StockItem.php
php -l routes/web.php
```

**Cache config:**
```bash
php artisan config:cache
```

**Clear cache (if needed):**
```bash
php artisan config:clear
```

---

## 10. EXAM MEMORY AID

**Remember these 5 optimizations:**

1. **Route Model Binding** - `Route::model('item', StockItem::class);`
2. **Categories Constant** - `public const CATEGORIES = [...];`
3. **Transaction Safety** - `DB::transaction(function() { ... });`
4. **Structured Logging** - `Log::warning('message', $contextArray);`
5. **Type Hints** - `function(StockItem $item): View`

**Remember these 3 patterns:**

1. **Form Requests** - Validation → Controller stays skinny
2. **Query Scopes** - Chainable → Readable → Efficient
3. **Implicit Binding** - Route parameter → Auto-resolved model

---

**This reference document contains all optimized code snippets for quick lookup during exam preparation!** ✨

