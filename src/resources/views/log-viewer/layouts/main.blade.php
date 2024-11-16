<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Laravel Log Viewer</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500&family=Roboto+Mono:wght@300;400&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Roboto Mono', monospace;
            background-color: #f8f9fa;
            padding: 20px;
        }
        h1, h5 {
            font-family: 'Fira Code', monospace;
        }
    </style>

    @stack('head')
</head>
<body>
<div class="container">
    <h3 class="text-secondary">@yield('heading')</h3>
    @yield('content')
</div>

@stack('scripts')
</body>
</html>
