<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'KampusLMS' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <nav>
        <a href="{{ ('dashboard') }}">Dashboard</a> |
        <a href="{{ route('courses.index') }}">Mata Kuliah</a> |
        <a href="/tentang">Tentang</a>
    </nav>
    <hr>
    <main>{{ $slot }}</main>
</body>
</html>