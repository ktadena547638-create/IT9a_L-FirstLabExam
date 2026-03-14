@extends('layout')

@section('title', 'Edit Stock Item - ' . $stock_item->item_name)

@section('content')
<!-- Breadcrumb Navigation -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('items.index') }}">Inventory</a></li>
        <li class="breadcrumb-item"><a href="{{ route('items.show', $stock_item) }}">{{ $stock_item->item_name }}</a></li>
        <li class="breadcrumb-item active">Edit</li>
    </ol>
</nav>

<!-- Page Header -->
<div class="row mb-4">
    <div class="col-md-10">
        <h1 class="h2"><i class="bi bi-pencil-square"></i> Edit Stock Item</h1>
        <p class="text-muted">Update the details of <strong>{{ $stock_item->item_name }}</strong> (SKU: <code>{{ $stock_item->sku }}</code>)</p>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-file-earmark-text"></i> Item Details
            </div>
            <div class="card-body">
                <form action="{{ route('items.update', $stock_item) }}" method="POST" novalidate id="editForm">
                    @csrf
                    @method('PUT')

                    <!-- Item Name Field -->
                    <div class="mb-3">
                        <label for="item_name" class="form-label">
                            Item Name
                            <span class="text-danger" title="Required field">*</span>
                        </label>
                        <input type="text" 
                               class="form-control @error('item_name') is-invalid @enderror"
                               id="item_name" 
                               name="item_name" 
                               value="{{ old('item_name', $stock_item->item_name) }}" 
                               placeholder="e.g., Pneumatic Drill"
                               minlength="3"
                               maxlength="255"
                               required>
                        @error('item_name')
                            <div class="invalid-feedback d-block">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                        <small class="form-text text-muted">Minimum 3 characters</small>
                    </div>

                    <!-- SKU Field -->
                    <div class="mb-3">
                        <label for="sku" class="form-label">
                            SKU (Stock Keeping Unit)
                            <span class="text-danger" title="Required field">*</span>
                        </label>
                        <input type="text" 
                               class="form-control @error('sku') is-invalid @enderror"
                               id="sku" 
                               name="sku" 
                               value="{{ old('sku', $stock_item->sku) }}" 
                               placeholder="e.g., PDR-001"
                               pattern="[A-Z0-9\-]*"
                               maxlength="50"
                               required
                               title="Use uppercase letters, numbers, and hyphens only">
                        @error('sku')
                            <div class="invalid-feedback d-block">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                        <small class="form-text text-muted">
                            <strong>Format:</strong> ABC-001 (Uppercase, numbers, hyphens only)
                        </small>
                    </div>

                    <!-- Category Field -->
                    <div class="mb-3">
                        <label for="category" class="form-label">
                            Category
                            <span class="text-danger" title="Required field">*</span>
                        </label>
                        <select class="form-select @error('category') is-invalid @enderror" 
                                id="category" 
                                name="category"
                                required>
                            <option value="">-- Select Category --</option>
                            @foreach ($categories ?? ['Tools', 'Hardware', 'Supplies', 'Equipment', 'Safety'] as $cat)
                                <option value="{{ $cat }}" {{ old('category', $stock_item->category) === $cat ? 'selected' : '' }}>
                                    {{ $cat }}
                                </option>
                            @endforeach
                        </select>
                        @error('category')
                            <div class="invalid-feedback d-block">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="row">
                        <!-- Quantity Field -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="quantity" class="form-label">
                                    Quantity
                                    <span class="text-danger" title="Required field">*</span>
                                </label>
                                <input type="number" 
                                       class="form-control @error('quantity') is-invalid @enderror"
                                       id="quantity" 
                                       name="quantity" 
                                       value="{{ old('quantity', $stock_item->quantity) }}" 
                                       min="0" 
                                       max="999999"
                                       step="1"
                                       required>
                                @error('quantity')
                                    <div class="invalid-feedback d-block">
                                        <i class="bi bi-exclamation-circle"></i> {{ $message }}
                                    </div>
                                @enderror
                                <small class="form-text text-muted">Whole units only</small>
                            </div>
                        </div>

                        <!-- Unit Price Field -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="unit_price" class="form-label">
                                    Unit Price (USD)
                                    <span class="text-danger" title="Required field">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" 
                                           class="form-control @error('unit_price') is-invalid @enderror"
                                           id="unit_price" 
                                           name="unit_price" 
                                           value="{{ old('unit_price', number_format($stock_item->unit_price, 2)) }}" 
                                           min="0.01" 
                                           max="99999.99"
                                           step="0.01"
                                           required>
                                </div>
                                @error('unit_price')
                                    <div class="invalid-feedback d-block">
                                        <i class="bi bi-exclamation-circle"></i> {{ $message }}
                                    </div>
                                @enderror
                                <small class="form-text text-muted">e.g., 19.99</small>
                            </div>
                        </div>
                    </div>

                    <!-- Metadata Info -->
                    <div class="alert alert-info" role="alert">
                        <i class="bi bi-info-circle"></i> 
                        <strong>Last updated:</strong> {{ $stock_item->updated_at->format('F j, Y \a\t g:i A') }}
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle"></i> Save Changes
                        </button>
                        <a href="{{ route('items.show', $stock_item) }}" class="btn btn-secondary btn-lg">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Info Sidebar -->
    <div class="col-md-4">
        <div class="card bg-light border-warning">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Field Guidelines</h6>
            </div>
            <div class="card-body small">
                <div class="mb-3">
                    <h6 class="fw-bold"><i class="bi bi-tag"></i> Item Name</h6>
                    <p class="mb-0">A clear, descriptive product name.</p>
                </div>

                <hr>

                <div class="mb-3">
                    <h6 class="fw-bold"><i class="bi bi-key"></i> SKU</h6>
                    <p class="mb-0">Unique identifier for tracking. Must be uppercase with hyphens.</p>
                </div>

                <hr>

                <div class="mb-3">
                    <h6 class="fw-bold"><i class="bi bi-list"></i> Category</h6>
                    <p class="mb-0">Change the product classification if needed.</p>
                </div>

                <hr>

                <div class="mb-3">
                    <h6 class="fw-bold"><i class="bi bi-box-seam"></i> Quantity</h6>
                    <p class="mb-0">Update the current stock level.</p>
                </div>

                <hr>

                <div class="mb-0">
                    <h6 class="fw-bold"><i class="bi bi-currency-dollar"></i> Unit Price</h6>
                    <p class="mb-0">Adjust the per-unit cost if needed.</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions Card -->
        <div class="card mt-3">
            <div class="card-header">
                <i class="bi bi-lightning"></i> Quick Actions
            </div>
            <div class="card-body d-grid gap-2">
                <a href="{{ route('items.show', $stock_item) }}" class="btn btn-info">
                    <i class="bi bi-eye"></i> View Details
                </a>
                <a href="{{ route('items.index') }}" class="btn btn-secondary">
                    <i class="bi bi-list"></i> Back to List
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto-convert SKU to uppercase
    document.getElementById('sku').addEventListener('input', function(e) {
        e.target.value = e.target.value.toUpperCase();
    });

    // Form validation feedback
    document.getElementById('editForm').addEventListener('submit', function(e) {
        if (!this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        this.classList.add('was-validated');
    });
</script>

    <!-- Info Sidebar -->
    <div class="col-md-4">
        <div class="card bg-light">
            <div class="card-header bg-light">
                <h6 class="mb-0">📋 Item Info</h6>
            </div>
            <div class="card-body small">
                <p><strong>Created:</strong> {{ $stock_item->created_at->format('M d, Y H:i') }}</p>
                <p><strong>Updated:</strong> {{ $stock_item->updated_at->format('M d, Y H:i') }}</p>
                <p><strong>Current Stock:</strong> {{ $stock_item->quantity }} units</p>
                <p><strong>Inventory Value:</strong> ${{ number_format($stock_item->quantity * $stock_item->unit_price, 2) }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
