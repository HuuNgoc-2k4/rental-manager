<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#4f46e5">
    <title>Chờ duyệt tài khoản</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @include('layouts.partials.fonts')
    <script src="https://unpkg.com/lucide@latest"></script>
    @include('layouts.partials.mobile-viewport')
    @include('layouts.partials.ui-styles')
</head>
<body class="bg-slate-50 text-slate-900">

<div class="min-h-dvh flex items-center justify-center p-4 sm:p-6">
    <div class="max-w-md w-full card rounded-3xl overflow-hidden">
        <div class="relative overflow-hidden bg-gradient-to-br from-amber-400 to-orange-500 px-6 py-10 text-center">
            <div class="absolute -right-10 -top-10 w-40 h-40 rounded-full bg-white/15"></div>
            <div class="relative w-16 h-16 rounded-2xl bg-white/20 ring-1 ring-inset ring-white/30 flex items-center justify-center mx-auto">
                <i data-lucide="hourglass" class="w-8 h-8 text-white"></i>
            </div>
        </div>
        <div class="p-6 sm:p-8 text-center">
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Tài khoản đang chờ duyệt</h2>
            <p class="text-slate-500 text-sm mt-3 leading-relaxed break-words">
                Xin chào <span class="font-bold text-slate-700">{{ Auth::user()->name }}</span>, quản trị viên sẽ kích hoạt
                tài khoản của bạn trong thời gian ngắn. Vui lòng đăng nhập lại sau.
            </p>

            <form action="{{ route('logout') }}" method="POST" class="mt-8">
                @csrf
                <button type="submit" class="btn btn-secondary w-full min-h-[52px]">
                    <i data-lucide="log-out" class="w-4 h-4"></i> Đăng xuất
                </button>
            </form>
        </div>
    </div>
</div>

<script>if (typeof lucide !== 'undefined') lucide.createIcons();</script>
</body>
</html>
