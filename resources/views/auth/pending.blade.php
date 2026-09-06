<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chờ duyệt tài khoản</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-900">

<div class="min-h-screen flex items-center justify-center p-6">
    <div class="max-w-md w-full bg-white rounded-3xl border border-slate-200 shadow-sm p-8 text-center">
        <div class="w-14 h-14 rounded-2xl bg-amber-100 flex items-center justify-center mx-auto mb-6">
            <i data-lucide="hourglass" class="w-7 h-7 text-amber-600"></i>
        </div>
        <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Tài khoản đang chờ duyệt</h2>
        <p class="text-slate-500 text-sm mt-3 leading-relaxed break-words">
            Xin chào <span class="font-bold text-slate-700">{{ Auth::user()->name }}</span>, quản trị viên sẽ kích hoạt
            tài khoản của bạn trong thời gian ngắn. Vui lòng đăng nhập lại sau.
        </p>

        <form action="{{ route('logout') }}" method="POST" class="mt-8">
            @csrf
            <button type="submit" class="w-full py-3.5 bg-slate-100 text-slate-600 rounded-2xl text-sm font-bold hover:bg-slate-200 transition-all">
                Đăng xuất
            </button>
        </form>
    </div>
</div>

<script>if (typeof lucide !== 'undefined') lucide.createIcons();</script>
</body>
</html>
