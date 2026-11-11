<!doctype html>
<html lang="pt-AO">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0d233f">
    <title>@yield('title', 'GPM Conecta') | GPM Conecta</title>
    <meta name="description" content="@yield('description', 'Soluções em suprimentos, procurement e fornecimento profissional em Angola.')">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        html{scroll-behavior:smooth}body{font-family:Inter,sans-serif;color:#263548}a,button{-webkit-tap-highlight-color:transparent}
        a:focus-visible,button:focus-visible,input:focus-visible,textarea:focus-visible{outline:3px solid rgba(249,107,33,.45);outline-offset:3px}
        .reveal{opacity:0;transform:translateY(24px);transition:opacity .65s ease,transform .65s ease}.reveal.active{opacity:1;transform:none}
        .product-card{border:1px solid #edf0f3;transition:transform .25s ease,box-shadow .25s ease,border-color .25s ease}
        .product-card:hover{transform:translateY(-5px);box-shadow:0 16px 35px rgba(13,35,63,.09);border-color:#f96b21}
        .watermark{position:absolute;top:-12px;left:0;font-size:clamp(5rem,13vw,12rem);font-weight:900;letter-spacing:-.075em;color:#f1f3f6;line-height:1;pointer-events:none;user-select:none}
        @media(prefers-reduced-motion:reduce){html{scroll-behavior:auto}.reveal{opacity:1;transform:none}*,*::before,*::after{animation-duration:.01ms!important;transition-duration:.01ms!important}}
    </style>
    @stack('head')
</head>
<body class="bg-white">
    @yield('content')
    <script>
        (() => {
            const button = document.getElementById('gpm-menu-button');
            const menu = document.getElementById('gpm-mobile-menu');
            if (button && menu) {
                button.addEventListener('click', () => {
                    const expanded = button.getAttribute('aria-expanded') === 'true';
                    button.setAttribute('aria-expanded', String(!expanded));
                    menu.classList.toggle('hidden', expanded);
                    menu.classList.toggle('flex', !expanded);
                });
            }
            const items = document.querySelectorAll('.reveal');
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver(entries => entries.forEach(entry => {
                    if (entry.isIntersecting) { entry.target.classList.add('active'); observer.unobserve(entry.target); }
                }), { threshold: 0.08 });
                items.forEach(item => observer.observe(item));
            } else items.forEach(item => item.classList.add('active'));
        })();
    </script>
    @stack('scripts')
</body>
</html>
