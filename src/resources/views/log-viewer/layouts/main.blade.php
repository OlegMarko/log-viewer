<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Log Viewer')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Roboto+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        .navbar {
            background-color: #343a40 !important;
        }
        .navbar-brand {
            color: #ffffff !important;
            font-weight: 600;
        }
        .container-fluid {
            padding-top: 20px;
            padding-bottom: 20px;
        }
        .card {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #ffffff;
            border-bottom: 1px solid #e9ecef;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
            padding: 1.25rem;
            font-weight: 600;
            color: #343a40;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 123, 255, 0.2);
        }
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(108, 117, 125, 0.2);
        }
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.2);
        }
        .table {
            border-radius: 12px;
            overflow: hidden;
        }
        .table th, .table td {
            vertical-align: middle;
            padding: 12px 15px;
        }
        .table thead th {
            background-color: #e9ecef;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
        }
        .table-hover tbody tr:hover {
            background-color: #e2f0ff;
        }
        .clickable-row {
            cursor: pointer;
        }
        .clickable-row.critical td {
            background-color: rgba(255, 0, 0, 0.08);
            border-left: 4px solid #dc3545;
        }
        .clickable-row.error td {
            background-color: rgba(255, 0, 0, 0.04);
            border-left: 4px solid #fd7e14;
        }
        .clickable-row.warning td {
            background-color: rgba(255, 193, 7, 0.08);
            border-left: 4px solid #ffc107;
        }
        .clickable-row.info td {
            background-color: rgba(0, 123, 255, 0.04);
            border-left: 4px solid #007bff;
        }
        .clickable-row.debug td, .clickable-row.notice td, .clickable-row.alert td, .clickable-row.emergency td, .clickable-row.other td {
            border-left: 4px solid #6c757d;
        }

        .modal-sub-title {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #495057;
            font-size: 0.9rem;
        }

        pre {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            overflow-x: auto;
            white-space: pre-wrap;
            word-break: break-word;
            font-family: 'Roboto Mono', monospace;
            font-size: 0.85rem;
            line-height: 1.5;
            border: 1px solid #e9ecef;
        }

        .d-flex.align-items-center {
            margin-bottom: 15px;
        }

        .btn-outline-secondary {
            border-radius: 8px;
            border: 1px solid #dee2e6;
            color: #6c757d;
        }
        .btn-outline-secondary:hover {
            background-color: #6c757d;
            color: #fff;
        }

        .download-form, .delete-form {
            display: inline-block;
        }
        .delete-form {
            margin-right: 1rem;
        }

        @media (max-width: 767.98px) {
            .table-responsive {
                border-radius: 12px;
                overflow-x: auto;
            }
            .download-form, .delete-form {
                display: block;
                width: 100%;
                margin-right: 0;
                margin-bottom: 10px !important;
            }
            .download-form button, .delete-form button {
                width: 100%;
            }
        }
    </style>
    @stack('head')
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('log-viewer.index') }}">
            <i class="bi bi-journal-text"></i> Log Viewer
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
            </ul>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <h1 class="mb-4">@yield('heading')</h1>
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>