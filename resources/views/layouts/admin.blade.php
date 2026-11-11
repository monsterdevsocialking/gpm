<!doctype html>
<html lang="pt-AO">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="theme-color" content="#0d233f">
    <title>@yield('title', 'Painel interno') | GPM</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com"><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>body{font-family:Inter,sans-serif}a,button{-webkit-tap-highlight-color:transparent}</style>
    @stack('head')
</head>
<body class="bg-gpm-soft text-slate-700">
    @yield('content')
    @stack('scripts')
</body>
</html>
