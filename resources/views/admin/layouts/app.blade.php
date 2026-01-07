<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Al Sidra Admin')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>

<body>
<div class="d-flex" id="wrapper">

    @include('admin.partials.sidebar')

    <div id="page-content-wrapper" class="d-flex flex-column min-vh-100">

        @include('admin.partials.header')

        <main class="container-fluid p-4">
            @yield('content')
        </main>

        @include('admin.partials.footer')

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.getElementById('menu-toggle')?.addEventListener('click', function (e) {
        e.preventDefault();
        document.body.classList.toggle('toggled');
    });
</script>
</body>
</html>
