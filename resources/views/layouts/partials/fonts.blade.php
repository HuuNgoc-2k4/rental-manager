{{-- Font Inter giống cms-manager (300–700). CDN lỗi thì tự chuyển sang bản local public/vendor/fonts (có subset tiếng Việt). --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" onerror="this.onerror=null;this.href='{{ asset('vendor/fonts/inter.css') }}'">
