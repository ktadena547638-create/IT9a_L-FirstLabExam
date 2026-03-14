<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Warehouse Inventory System')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #007bff;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --light-gray: #f8f9fa;
            --border-color: #dee2e6;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: var(--light-gray);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 14px;
            line-height: 1.5;
        }

        /* Navbar Styling */
        .navbar {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            background-color: #fff !important;
        }

        .navbar-brand {
            font-weight: 700;
            color: var(--primary-color) !important;
            font-size: 1.3rem;
        }

        .navbar-brand:hover {
            color: var(--info-color) !important;
        }

        /* Main Content */
        main {
            min-height: calc(100vh - 56px - 80px);
            padding: 30px 0;
        }

        .container-fluid {
            padding: 0 20px;
        }

        /* Cards */
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
            transition: box-shadow 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }

        .card-header {
            background-color: var(--light-gray);
            border-bottom: 1px solid var(--border-color);
            font-weight: 600;
            padding: 15px 20px;
            color: #333;
        }

        .card-body {
            padding: 20px;
        }

        /* Tables */
        .table {
            margin-bottom: 0;
            font-size: 0.95rem;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        .table thead {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        /* Badges */
        .badge {
            padding: 0.5rem 0.75rem;
            font-weight: 500;
            font-size: 0.85rem;
        }

        /* Buttons */
        .btn {
            border-radius: 5px;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .btn-sm {
            margin: 2px;
            padding: 0.35rem 0.6rem;
            font-size: 0.85rem;
        }

        /* Alerts */
        .alert {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
        }

        /* Forms */
        .form-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .form-control, .form-select {
            border-radius: 5px;
            border: 1px solid var(--border-color);
            padding: 0.6rem 0.9rem;
            font-size: 0.95rem;
            transition: border-color 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .form-control.is-invalid, .form-select.is-invalid {
            border-color: var(--danger-color);
        }

        .invalid-feedback {
            color: var(--danger-color);
            font-size: 0.85rem;
            margin-top: 0.25rem;
            display: block;
        }

        .text-danger {
            color: var(--danger-color) !important;
        }

        /* Breadcrumbs */
        .breadcrumb {
            background-color: transparent;
            padding: 0 0 15px 0;
            margin-bottom: 20px;
        }

        .breadcrumb-item {
            color: #666;
        }

        .breadcrumb-item.active {
            color: #333;
            font-weight: 600;
        }

        .breadcrumb-item a {
            color: var(--primary-color);
            text-decoration: none;
        }

        .breadcrumb-item a:hover {
            color: var(--info-color);
            text-decoration: underline;
        }

        /* Footer */
        footer {
            background-color: #fff;
            border-top: 1px solid var(--border-color);
            padding: 20px 0;
            color: #666;
            font-size: 0.9rem;
            margin-top: auto;
        }

        /* Loading Spinner (for future use) */
        .spinner-border {
            width: 1.5rem;
            height: 1.5rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            main {
                padding: 15px 0;
            }

            .container-fluid {
                padding: 0 10px;
            }

            .btn-sm {
                margin: 1px;
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }
        }
    </style>
</head>
<body class="d-flex flex-column">
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('items.index') }}">
                📦 Warehouse System
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('items.index') ? 'active' : '' }}" href="{{ route('items.index') }}">
                            📋 Inventory
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('items.create') ? 'active' : '' }}" href="{{ route('items.create') }}">
                            ➕ Add Item
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('items.export') }}">
                            📥 Export CSV
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="py-4 flex-grow-1">
        <div class="container-fluid">
            <!-- Flash Messages -->
            @if ($message = Session::get('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong><i class="bi bi-check-circle"></i> Success!</strong> {{ $message }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($message = Session::get('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong><i class="bi bi-exclamation-circle"></i> Error!</strong> {{ $message }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($message = Session::get('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <strong><i class="bi bi-exclamation-triangle"></i> Warning!</strong> {{ $message }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Page Content -->
            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="mt-auto">
        <div class="container-fluid text-center">
            <p class="mb-0">&copy; 2026 Warehouse Inventory Management System. Built with Laravel 11 & Bootstrap 5.</p>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Scripts -->
    <script>
        // Auto-dismiss alerts after 6 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 6000);
            });
        });

        // Confirm delete action
        function confirmDelete() {
            return confirm('Are you sure you want to delete this item? This action cannot be undone.');
        }
    </script>
</body>
</html>
