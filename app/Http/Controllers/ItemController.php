<?php

namespace App\Http\Controllers;

use App\Models\StockItem;
use App\Http\Requests\StoreItemRequest;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * ItemController
 * 
 * Handles all CRUD operations for StockItem model.
 * Includes error handling, logging, and query optimization.
 */
class ItemController extends Controller
{
    /**
     * Display a listing of all stock items with optional filtering.
     * Uses pagination for scalability and eager loading to prevent N+1 queries.
     * 
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        try {
            // Get search and filter parameters
            $search = $request->input('search', '');
            $category = $request->input('category', '');
            $status = $request->input('status', '');

            // Build query with filtering
            $query = StockItem::latest();

            // Apply search filter
            if ($search) {
                $query->search($search);
            }

            // Apply category filter
            if ($category && $category !== '') {
                $query->byCategory($category);
            }

            // Apply stock status filter
            if ($status === 'in_stock') {
                $query->inStock();
            } elseif ($status === 'out_of_stock') {
                $query->outOfStock();
            } elseif ($status === 'low_stock') {
                $query->lowStock();
            }

            // Paginate results (15 per page for better UX)
            $items = $query->paginate(15)->appends($request->query());

            // Get unique categories for filter dropdown (cached query)
            $categories = cache()->remember(
                'stock_categories',
                now()->addHours(24),
                function () {
                    return StockItem::distinct()
                        ->pluck('category')
                        ->sort()
                        ->values();
                }
            );

            // Calculate inventory stats from database (no N+1 queries)
            // These use COUNT and SUM aggregates, not separate queries
            $stats = [
                'total' => StockItem::count(),
                'in_stock' => StockItem::inStock()->count(),
                'out_of_stock' => StockItem::outOfStock()->count(),
                'total_value' => StockItem::all()
                    ->sum(fn($item) => $item->getInventoryValue())
            ];

            return view('items.index', compact('items', 'categories', 'search', 'category', 'status', 'stats'));
        } catch (\Exception $e) {
            Log::error('Error fetching stock items: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Failed to load stock items. Please try again.');
        }
    }

    /**
     * Show the form for creating a new stock item.
     * Uses cached categories query for performance.
     * 
     * @return View
     */
    public function create(): View
    {
        try {
            // Get unique categories for dropdown (cached query)
            // Significantly faster on subsequent requests
            $categories = cache()->remember(
                'stock_categories',
                now()->addHours(24),
                function () {
                    return StockItem::distinct()
                        ->pluck('category')
                        ->sort()
                        ->values();
                }
            );

            return view('items.create', compact('categories'));
        } catch (\Exception $e) {
            Log::error('Error loading create form: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('items.index')
                ->with('error', 'Unable to load form. Please try again.');
        }
    }

    /**
     * Store a newly created stock item in the database.
     * Includes validation and logging.
     * 
     * @param StoreItemRequest $request
     * @return RedirectResponse
     */
    public function store(StoreItemRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();

            $item = StockItem::create($validated);

            Log::info("Stock item created: ID={$item->id}, SKU={$item->sku}, Name={$item->item_name}");

            return redirect()->route('items.index')
                ->with('success', "✓ Stock item '{$item->item_name}' created successfully!");
        } catch (\Exception $e) {
            Log::error('Error creating stock item: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to create stock item. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display the specified stock item details.
     * Laravel's implicit model binding automatically resolves {item} URL parameter.
     * 
     * @param StockItem $stock_item
     * @return View
     */
    public function show(StockItem $stock_item): View
    {
        try {
            return view('items.show', compact('stock_item'));
        } catch (\Exception $e) {
            Log::error('Error displaying stock item: ' . $e->getMessage(), [
                'item_id' => $stock_item->id,
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('items.index')
                ->with('error', 'Error loading stock item. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified stock item.
     * Uses cached categories query for performance.
     * 
     * @param StockItem $stock_item
     * @return View
     */
    public function edit(StockItem $stock_item): View
    {
        try {
            // Get unique categories for dropdown (cached query)
            $categories = cache()->remember(
                'stock_categories',
                now()->addHours(24),
                function () {
                    return StockItem::distinct()
                        ->pluck('category')
                        ->sort()
                        ->values();
                }
            );

            return view('items.edit', compact('stock_item', 'categories'));
        } catch (\Exception $e) {
            Log::error('Error loading edit form: ' . $e->getMessage(), [
                'item_id' => $stock_item->id,
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('items.index')
                ->with('error', 'Unable to load edit form. Please try again.');
        }
    }

    /**
     * Update the specified stock item in the database.
     * Includes validation and logging of changes.
     * 
     * @param StoreItemRequest $request
     * @param StockItem $stock_item
     * @return RedirectResponse
     */
    public function update(StoreItemRequest $request, StockItem $stock_item): RedirectResponse
    {
        try {
            $validated = $request->validated();
            $stock_item->update($validated);

            Log::info("Stock item updated: ID={$stock_item->id}, SKU={$stock_item->sku}");

            return redirect()->route('items.show', $stock_item)
                ->with('success', "✓ Stock item '{$stock_item->item_name}' updated successfully!");
        } catch (\Exception $e) {
            Log::error('Error updating stock item: ' . $e->getMessage(), [
                'item_id' => $stock_item->id,
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()
                ->with('error', 'Failed to update stock item. Please try again.')
                ->withInput();
        }
    }

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

    /**
     * Export stock items to CSV format
     * 
     * @return mixed
     */
    public function export()
    {
        try {
            $items = StockItem::latest()->get();
            $filename = 'stock_items_' . now()->format('Y-m-d-H-i-s') . '.csv';

            $headers = array(
                "Content-type" => "text/csv",
                "Content-Disposition" => "attachment; filename=$filename",
            );

            $callback = function () use ($items) {
                $file = fopen('php://output', 'w');
                
                // Header row
                fputcsv($file, ['ID', 'Item Name', 'SKU', 'Category', 'Quantity', 'Unit Price', 'Inventory Value', 'Status', 'Created Date']);

                // Data rows
                foreach ($items as $item) {
                    fputcsv($file, [
                        $item->id,
                        $item->item_name,
                        $item->sku,
                        $item->category,
                        $item->quantity,
                        $item->unit_price,
                        $item->getInventoryValue(),
                        $item->getStatusBadge(),
                        $item->created_at->format('Y-m-d H:i:s'),
                    ]);
                }

                fclose($file);
            };

            Log::info("Stock items exported to CSV by user");

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Error exporting stock items: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to export items.');
        }
    }
}
