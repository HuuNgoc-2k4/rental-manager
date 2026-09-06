<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập — Quản lý nhà cho thuê</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .bg-pattern {
            background-color: #ffffff;
            background-image: radial-gradient(#e2e8f0 0.8px, transparent 0.8px);
            background-size: 30px 30px;
        }
    </style>
</head>
<body class="bg-white text-slate-900">

<div class="flex min-h-screen">
    <div class="hidden lg:flex lg:w-1/2 bg-pattern items-center justify-center p-12 border-r border-slate-100">
        <div class="max-w-md w-full">
            <div class="w-14 h-14 rounded-2xl bg-indigo-600 flex items-center justify-center mb-8">
                <i data-lucide="home" class="w-7 h-7 text-white"></i>
            </div>
            <h1 class="text-4xl font-extrabold text-slate-900 leading-tight tracking-tight">
                Quản lý <span class="text-indigo-600">nhà cho thuê</span>
            </h1>
            <p class="mt-4 text-slate-500 text-lg leading-relaxed">
                Phòng, khách thuê, hợp đồng, chốt chỉ số điện nước, hóa đơn và công nợ — gọn trong một chỗ.
            </p>

            <div class="mt-12 space-y-3">
                @foreach([
                    ['camera', 'Chụp ảnh đồng hồ, hệ thống đọc sẵn chỉ số'],
                    ['receipt-text', 'Hóa đơn kỳ sinh tự động từ hợp đồng'],
                    ['bell-ring', 'Nhắc thu tiền và công nợ quá hạn'],
                ] as [$icon, $text])
                    <div class="flex items-center gap-3 text-sm text-slate-600 font-medium">
                        <span class="w-8 h-8 rounded-xl bg-white ring-1 ring-slate-200 flex items-center justify-center shrink-0">
                            <i data-lucide="{{ $icon }}" class="w-4 h-4 text-indigo-600"></i>
                        </span>
                        <span class="break-words min-w-0">{{ $text }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-white">
        <div class="max-w-sm w-full">
            <div class="mb-10">
                <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Đăng nhập</h2>
                <p class="text-slate-500 text-sm mt-2">Dùng email hoặc số điện thoại đã đăng ký.</p>
            </div>

            @if(session('success'))
                <div class="mb-6 px-4 py-3 rounded-2xl bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-100 break-words">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label for="login" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Email hoặc số điện thoại</label>
                    <input type="text" id="login" name="login" value="{{ old('login') }}" required autofocus
                           class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all placeholder:text-slate-300 font-medium"
                           placeholder="you@example.com hoặc 09xxxxxxxx">
                    @error('login') <p class="mt-2 text-xs text-rose-500 font-bold ml-1 break-words">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Mật khẩu</label>
                    <input type="password" id="password" name="password" required
                           class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all placeholder:text-slate-300 font-medium"
                           placeholder="••••••••">
                    @error('password') <p class="mt-2 text-xs text-rose-500 font-bold ml-1 break-words">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-3 ml-1">
                    <input type="checkbox" id="remember" name="remember" class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                    <label for="remember" class="text-xs text-slate-500 font-bold cursor-pointer select-none">Ghi nhớ đăng nhập</label>
                </div>

                <button type="submit" class="w-full py-4 bg-indigo-600 text-white rounded-2xl text-sm font-black shadow-xl shadow-indigo-200 hover:bg-indigo-700 active:scale-[0.98] transition-all tracking-wider uppercase">
                    Đăng nhập
                </button>
            </form>

            <p class="mt-8 text-center text-xs text-slate-400 font-medium">
                Là chủ hộ chưa có tài khoản?
                <a href="{{ route('register') }}" class="text-indigo-600 font-bold hover:underline">Đăng ký</a>
            </p>
        </div>
    </div>
</div>

<script>if (typeof lucide !== 'undefined') lucide.createIcons();</script>
</body>
</html>
