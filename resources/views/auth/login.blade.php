<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#4f46e5">
    <title>Đăng nhập — Quản lý nhà cho thuê</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @include('layouts.partials.fonts')
    <script src="https://unpkg.com/lucide@latest"></script>
    @include('layouts.partials.mobile-viewport')
    @include('layouts.partials.ui-styles')
</head>
<body class="bg-white text-slate-900">

<div class="flex min-h-dvh">
    {{-- Panel giới thiệu (desktop) --}}
    <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden bg-gradient-to-br from-indigo-600 via-indigo-600 to-violet-700 items-center justify-center p-12 text-white">
        <div class="absolute -left-24 -top-24 w-96 h-96 rounded-full bg-white/10"></div>
        <div class="absolute right-0 bottom-0 w-[28rem] h-[28rem] rounded-full bg-white/5 translate-x-1/3 translate-y-1/3"></div>

        <div class="relative max-w-md w-full">
            <div class="w-14 h-14 rounded-2xl bg-white/15 ring-1 ring-inset ring-white/25 flex items-center justify-center mb-8">
                <i data-lucide="home" class="w-7 h-7"></i>
            </div>
            <h1 class="text-4xl font-bold leading-tight tracking-tight">Quản lý nhà cho thuê</h1>
            <p class="mt-4 text-indigo-100 text-lg leading-relaxed">
                Phòng, khách thuê, hợp đồng, chốt chỉ số điện nước, hóa đơn và công nợ — gọn trong một chỗ.
            </p>

            <div class="mt-10 space-y-3">
                @foreach([
                    ['building-2', 'Sơ đồ phòng theo tầng, trạng thái trống / đang thuê'],
                    ['id-card', 'Khách tự khai báo CCCD, chủ nhà duyệt một chạm'],
                    ['camera', 'Chụp ảnh đồng hồ, hệ thống đọc sẵn chỉ số'],
                    ['receipt-text', 'Hóa đơn kỳ sinh tự động từ hợp đồng']
                ] as [$icon, $text])
                    <div class="flex items-center gap-3 rounded-2xl bg-white/10 ring-1 ring-inset ring-white/15 px-4 py-3 text-sm font-semibold">
                        <span class="w-9 h-9 rounded-xl bg-white text-indigo-600 flex items-center justify-center shrink-0">
                            <i data-lucide="{{ $icon }}" class="w-4 h-4"></i>
                        </span>
                        <span class="break-words min-w-0">{{ $text }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="w-full lg:w-1/2 flex flex-col">
        {{-- Mobile: dải thương hiệu phía trên --}}
        <div class="lg:hidden relative overflow-hidden bg-gradient-to-br from-indigo-600 to-violet-600 text-white px-6 pt-10 pb-16">
            <div class="absolute -right-16 -top-16 w-56 h-56 rounded-full bg-white/10"></div>
            <div class="relative flex items-center gap-3">
                <span class="w-12 h-12 rounded-2xl bg-white/15 ring-1 ring-inset ring-white/25 flex items-center justify-center shrink-0">
                    <i data-lucide="home" class="w-6 h-6"></i>
                </span>
                <div class="min-w-0">
                    <p class="text-lg font-bold tracking-tight">Nhà cho thuê</p>
                    <p class="text-xs text-indigo-100 font-medium">Quản lý phòng, khách thuê, hóa đơn</p>
                </div>
            </div>
        </div>

        <div class="flex-1 flex items-start lg:items-center justify-center px-4 sm:px-8 -mt-8 lg:mt-0 pb-10">
            <div class="relative z-10 max-w-sm w-full bg-white rounded-3xl lg:rounded-none shadow-xl shadow-slate-200/60 lg:shadow-none border border-slate-100 lg:border-0 p-6 sm:p-8 lg:p-0">
                <div class="mb-8">
                    <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">Đăng nhập</h2>
                    <p class="text-slate-500 text-sm mt-2">Dùng email hoặc số điện thoại đã đăng ký.</p>
                </div>

                @if(session('success'))
                    <div class="mb-6 px-4 py-3 rounded-2xl bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-100 break-words flex items-center gap-2">
                        <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0"></i>{{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST" class="space-y-5">
                    @csrf
                    <div>
                        <label for="login" class="form-label">Email hoặc số điện thoại</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 pointer-events-none"><i data-lucide="user" class="w-5 h-5"></i></span>
                            <input type="text" id="login" name="login" value="{{ old('login') }}" required autofocus autocomplete="username"
                                   class="form-input min-h-[52px] pl-12" placeholder="Email hoặc 09xxxxxxxx">
                        </div>
                        @error('login') <p class="mt-2 text-xs text-rose-500 font-bold ml-1 break-words">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="password" class="form-label">Mật khẩu</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 pointer-events-none"><i data-lucide="lock" class="w-5 h-5"></i></span>
                            <input type="password" id="password" name="password" required autocomplete="current-password"
                                   class="form-input min-h-[52px] pl-12 pr-12" placeholder="••••••••">
                            <button type="button" onclick="window.togglePassword('password', this)" aria-label="Hiện mật khẩu"
                                    class="absolute inset-y-0 right-0 w-12 flex items-center justify-center text-slate-400 hover:text-slate-600">
                                <i data-lucide="eye" class="w-5 h-5"></i>
                            </button>
                        </div>
                        @error('password') <p class="mt-2 text-xs text-rose-500 font-bold ml-1 break-words">{{ $message }}</p> @enderror
                    </div>

                    <label for="remember" class="flex items-center gap-3 min-h-[44px] ml-1 cursor-pointer select-none">
                        <input type="checkbox" id="remember" name="remember" class="w-5 h-5 rounded accent-indigo-600 cursor-pointer">
                        <span class="text-sm text-slate-500 font-semibold">Ghi nhớ đăng nhập</span>
                    </label>

                    <button type="submit" class="btn btn-primary w-full min-h-[52px] uppercase tracking-wider font-bold shadow-xl">
                        Đăng nhập <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Helper dùng chung (togglePassword, lucide...) — các hàm khác tự guard khi thiếu Swal / phần tử --}}
@include('layouts.partials.scripts')
</body>
</html>
