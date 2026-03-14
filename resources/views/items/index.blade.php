@extends('layout')

@section('title', 'Stock Items Inventory - Warehouse System')

@section('content')
<!-- Page Header -->
<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="h2 mb-2"><i class="bi bi-list"></i> Stock Items</h1>
        <p class="text-muted">Manage your warehouse inventory with real-time stock tracking</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('items.create') }}" class="btn btn-primary btn-lg">
            <i class="bi bi-plus-circle"></i> Add New Item
        </a>
    </div>
</div>

<!-- Search and Filter Section -->
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-funnel"></i> Search & Filter
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('items.index') }}" class="row g-3">
            <!-- Search Input -->
            <div class="col-md-4">
                <label for="search" class="form-label">Search by Name or SKU</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="search" name="search" 
                           placeholder="e.g., Drill, PDR-001" value="{{ $search ?? '' }}">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="bi bi-search"></i> Search
                    </button>
                </div>
            </div>

            <!-- Category Filter -->
            <div class="col-md-3">
                <label for="category" class="form-label">Category</label>
                <select class="form-select" id="category" name="category" onchange="this.form.submit()">
                    <option value="">-- All Categories --</option>
                    @foreach ($categories ?? [] as $cat)
                        <option value="{{ $cat }}" {{ ($category ?? '') === $cat ? 'selected' : '' }}>
                            {{ $cat }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div class="col-md-3">
                <label for="status" class="form-label">Stock Status</label>
                <select class="form-select" id="status" name="status" onchange="this.form.submit()">
                    <option value="">-- All Items --</option>
                    <option value="in_stock" {{ ($status ?? '') === 'in_stock' ? 'selected' : '' }}>In Stock</option>
                    <option value="out_of_stock" {{ ($status ?? '') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                    <option value="low_stock" {{ ($status ?? '') === 'low_stock' ? 'selected' : '' }}>Low Stock (≤10)</option>
                </select>
            </div>

            <!-- Reset Button -->
            <div class="col-md-2 d-flex align-items-end">
                <a href="{{ route('items.index') }}" class="btn btn-secondary w-100">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Items Table -->
@if ($items->count() > 0)
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th><i class="bi bi-hash"></i> ID</th>
                        <th>Item Name</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th class="text-center">Quantity</th>
                        <th class="text-end">Unit Price</th>
                        <th class="text-center">Inventory Value</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        <tr>
                            <td class="fw-bold text-primary">#{{ $item->id }}</td>
                            <td class="fw-600">{{ $item->item_name }}</td>
                            <td>
                                <code class="bg-light px-2 py-1 rounded">{{ $item->sku }}</code>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $item->category }}</span>
                            </td>
                            <td class="text-center">
                                <strong class="text-primary">{{ $item->quantity }}</strong>
                            </td>
                            <td class="text-end">
                                <span class="text-success fw-bold">${{ number_format($item->unit_price, 2) }}</span>
                            </td>
                            <td class="text-center">
                                <span class="text-dark fw-bold">${{ number_format($item->getInventoryValue(), 2) }}</span>
                            </td>
                            <td class="text-center">
                                @if ($item->quantity > 0)
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> In Stock
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="bi bi-exclamation-circle"></i> Out
                                    </span>
                                @endif
                                @if ($item->isLowStock())
                                    <br><small class="badge bg-warning text-dark mt-1">Low Stock</small>
                                @endif
                            </td>
                            <td class="text-center">
                                <!-- View Button -->
                                <a href="{{ route('items.show', $item) }}" 
                                   class="btn btn-sm btn-info text-white" 
                                   title="View Details"
                                   data-bs-toggle="tooltip">
                                    <i class="bi bi-eye"></i>
                                </a>

                                <!-- Edit Button -->
                                <a href="{{ route('items.edit', $item) }}" 
                                   class="btn btn-sm btn-warning text-dark" 
                                   title="Edit Item"
                                   data-bs-toggle="tooltip">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <!-- Delete Button with Modal -->
                                <button type="button" 
                                        class="btn btn-sm btn-danger" 
                                        title="Delete Item"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deleteModal{{ $item->id }}"
                                        data-bs-tooltip="tooltip">
                                    <i class="bi bi-trash"></i>
                                </button>

                                <!-- Delete Confirmation Modal -->
                                <div class="modal fade" id="deleteModal{{ $item->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Confirm Deletion</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p class="mb-0">
                                                    Are you sure you want to delete <strong>{{ $item->item_name }}</strong> (SKU: <code>{{ $item->sku }}</code>)?
                                                </p>
                                                <p class="text-muted mt-2 small">This action cannot be undone.</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <form action="{{ route('items.destroy', $item) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">
                                                        <i class="bi bi-trash"></i> Delete Permanently
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-4">
        {{ $items->links('pagination::bootstrap-5') }}
    </div>

    <!-- Summary Stats (Optional: Shows at bottom) -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Total Items</h6>
                    <h3 class="text-primary">{{ $stats['total'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">In Stock</h6>
                    <h3 class="text-success">{{ $stats['in_stock'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Out of Stock</h6>
                    <h3 class="text-danger">{{ $stats['out_of_stock'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Total Inventory Value</h6>
                    <h3 class="text-info">${{ number_format($stats['total_value'] ?? 0, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>
@else
    <!-- Empty State -->
    <div class="card text-center py-5">
        <div class="card-body">
            <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
            <h5 class="card-title mt-3">No Items Found</h5>
            <p class="card-text text-muted">
                @if ($search || $category || $status)
                    Try adjusting your search or filter criteria.
                @else
                    Start by adding your first stock item to the warehouse.
                @endif
            </p>
            <a href="{{ route('items.create') }}" class="btn btn-primary mt-3">
                <i class="bi bi-plus-circle"></i> Add First Item
            </a>
        </div>
    </div>
@endif

<!-- Initialize Bootstrap Tooltips -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endsection
