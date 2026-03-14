@extends('layout')

@section('title', $stock_item->item_name . ' - Stock Item Details')

@section('content')
<!-- Breadcrumb Navigation -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('items.index') }}">Inventory</a></li>
        <li class="breadcrumb-item active">{{ $stock_item->item_name }}</li>
    </ol>
</nav>

<!-- Page Header -->
<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="h2">{{ $stock_item->item_name }}</h1>
        <p class="text-muted">SKU: <code>{{ $stock_item->sku }}</code></p>
    </div>
    <div class="col-md-4 text-end">
        <div class="d-flex gap-2 justify-content-end flex-wrap">
            <a href="{{ route('items.edit', $stock_item) }}" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                <i class="bi bi-trash"></i> Delete
            </button>
        </div>
    </div>
</div>

<div class="row">
    <!-- Main Details Card -->
    <div class="col-md-8">
        <!-- Overview Card -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-info-circle"></i> Overview
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <h6 class="text-muted"><i class="bi bi-tag"></i> Item Name</h6>
                        <p class="h5 fw-bold">{{ $stock_item->item_name }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <h6 class="text-muted"><i class="bi bi-key"></i> SKU</h6>
                        <p class="h5 fw-bold"><code>{{ $stock_item->sku }}</code></p>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <h6 class="text-muted"><i class="bi bi-list"></i> Category</h6>
                        <p><span class="badge bg-info fs-6">{{ $stock_item->category }}</span></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <h6 class="text-muted"><i class="bi bi-circle-fill"></i> Status</h6>
                        @if ($stock_item->quantity > 0)
                            <p><span class="badge bg-success fs-6"><i class="bi bi-check-circle"></i> In Stock</span></p>
                        @else
                            <p><span class="badge bg-danger fs-6"><i class="bi bi-exclamation-circle"></i> Out of Stock</span></p>
                        @endif
                        @if ($stock_item->isLowStock())
                            <small class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle"></i> Low Stock Alert</small>
                        @endif
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <h6 class="text-muted"><i class="bi bi-box-seam"></i> Current Quantity</h6>
                        <p class="h4 text-primary fw-bold">{{ $stock_item->quantity }} <span class="h6 text-muted fw-normal">units</span></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <h6 class="text-muted"><i class="bi bi-currency-dollar"></i> Unit Price</h6>
                        <p class="h4 text-success fw-bold">${{ number_format($stock_item->unit_price, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory Value Card -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-calculator"></i> Inventory Valuation
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-6 mb-3">
                        <h6 class="text-muted mb-2">Total Inventory Value</h6>
                        <h3 class="text-primary fw-bold">${{ number_format($stock_item->getInventoryValue(), 2) }}</h3>
                        <small class="text-muted">
                            {{ $stock_item->quantity }} × ${{ number_format($stock_item->unit_price, 2) }}
                        </small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <h6 class="text-muted mb-2">Stock Status</h6>
                        <h3 class="text-{{ $stock_item->quantity > 0 ? 'success' : 'danger' }} fw-bold">
                            {{ $stock_item->quantity > 0 ? '✓ In Stock' : '✗ Out of Stock' }}
                        </h3>
                        <small class="text-muted">Last updated: {{ $stock_item->updated_at->diffForHumans() }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-md-4">
        <!-- Metadata Card -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-calendar-event"></i> Metadata
            </div>
            <div class="card-body small">
                <div class="mb-3">
                    <h6 class="text-muted"><i class="bi bi-clock"></i> Created Date</h6>
                    <p class="mb-0 fw-bold">{{ $stock_item->created_at->format('F j, Y') }}</p>
                    <small class="text-muted">{{ $stock_item->created_at->format('g:i A') }}</small>
                </div>

                <hr>

                <div class="mb-0">
                    <h6 class="text-muted"><i class="bi bi-arrow-repeat"></i> Last Updated</h6>
                    <p class="mb-0 fw-bold">{{ $stock_item->updated_at->format('F j, Y') }}</p>
                    <small class="text-muted">{{ $stock_item->updated_at->format('g:i A') }}</small>
                </div>
            </div>
        </div>

        <!-- Stock Info Card -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <i class="bi bi-graph-up"></i> Stock Information
            </div>
            <div class="card-body small">
                <div class="mb-3">
                    <h6 class="text-muted mb-2">Quantity Status</h6>
                    <div class="progress" role="progressbar" aria-valuenow="{{ min($stock_item->quantity, 100) }}" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar {{ $stock_item->quantity >= 50 ? 'bg-success' : ($stock_item->quantity >= 20 ? 'bg-warning' : 'bg-danger') }}" 
                             style="width: {{ min(($stock_item->quantity / 100) * 100, 100) }}%">
                        </div>
                    </div>
                </div>

                <div class="mb-0">
                    <p class="mb-1">
                        <strong>Category:</strong> {{ $stock_item->category }}
                    </p>
                    <p class="mb-1">
                        <strong>Availability:</strong> 
                        @if ($stock_item->quantity >= 50)
                            <span class="badge bg-success">Abundant</span>
                        @elseif ($stock_item->quantity >= 20)
                            <span class="badge bg-warning text-dark">Moderate</span>
                        @elseif ($stock_item->quantity > 0)
                            <span class="badge bg-danger">Low</span>
                        @else
                            <span class="badge bg-dark">Out of Stock</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Quick Actions Card -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-lightning"></i> Quick Actions
            </div>
            <div class="card-body d-grid gap-2">
                <a href="{{ route('items.edit', $stock_item) }}" class="btn btn-warning">
                    <i class="bi bi-pencil"></i> Edit Item
                </a>
                <a href="{{ route('items.index') }}" class="btn btn-secondary">
                    <i class="bi bi-list"></i> Back to Inventory
                </a>
                <a href="{{ route('items.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Add New Item
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="bi bi-exclamation-triangle"></i> Confirm Deletion
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">
                    Are you sure you want to permanently delete <strong>{{ $stock_item->item_name }}</strong>?
                </p>
                <p class="text-muted mb-0">
                    <small>
                        <i class="bi bi-info-circle"></i>
                        This action cannot be undone. The item will be removed from your inventory.
                    </small>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('items.destroy', $stock_item) }}" method="POST" style="display: inline;">
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
@endsection
