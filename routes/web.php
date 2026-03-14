<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Models\StockItem;

/**
 * Route Configuration for Warehouse Inventory System
 * 
 * Implements RESTful routing with security-hardened error handling.
 * All CRUD operations follow Laravel resource routing conventions.
 * 
 * Architecture decisions:
 * - Explicit findOrFail for 404 handling (security best practice)
 * - Rate limiting on export endpoint (10 requests/minute)
 * - Query caching for performance
 */

// Redirect root to items index
Route::get('/', function () {
    return redirect()->route('items.index');
});

// ============================================================================
// EXPLICIT ROUTE MODEL BINDING
// ============================================================================
// Optional: Declare model binding for reference
// With explicit findOrFail in controller, this is supplementary
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
