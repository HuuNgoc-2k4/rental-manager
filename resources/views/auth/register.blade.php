<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký chủ hộ — Quản lý nhà cho thuê</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-900">

<div class="min-h-screen flex items-center justify-center p-6">
    <div class="max-w-md w-full bg-white rounded-3xl border border-slate-200 shadow-sm p-8">
        <div class="w-12 h-12 rounded-2xl bg-indigo-600 flex items-center justify-center mb-6">
            <i data-lucide="user-plus" class="w-6 h-6 text-white"></i>
        </div>
        <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Đăng ký tài khoản chủ hộ</h2>
        <p class="text-slate-500 text-sm mt-2">Tài khoản cần quản trị viên duyệt trước khi sử dụng.</p>

        @if(session('error'))
            <div class="mt-6 px-4 py-3 rounded-2xl bg-rose-50 text-rose-700 text-xs font-bold border border-rose-100 break-words">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('register.store') }}" method="POST" class="mt-8 space-y-5">
            @csrf
            <div>
                <label for="name" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Họ và tên</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                       class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-medium">
                @error('name') <p class="mt-2 text-xs text-rose-500 font-bold ml-1 break-words">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="email" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                       class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-medium"
                       placeholder="you@example.com">
                @error('email') <p class="mt-2 text-xs text-rose-500 font-bold ml-1 break-words">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="phone" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Số điện thoại</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                       class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-medium"
                       placeholder="09xxxxxxxx">
                @error('phone') <p class="mt-2 text-xs text-rose-500 font-bold ml-1 break-words">{{ $message }}</p> @enderror
                <p class="mt-2 text-[11px] text-slate-400 font-medium ml-1">Nhập ít nhất một trong hai: email hoặc số điện thoại.</p>
            </div>

            <div>
                <label for="password" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Mật khẩu</label>
                <input type="password" id="password" name="password" required
                       class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-medium"
                       placeholder="Tối thiểu 8 ký tự">
                @error('password') <p class="mt-2 text-xs text-rose-500 font-bold ml-1 break-words">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Nhập lại mật khẩu</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required
                       class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-medium">
            </div>

            <button type="submit" class="w-full py-4 bg-indigo-600 text-white rounded-2xl text-sm font-black shadow-xl shadow-indigo-200 hover:bg-indigo-700 active:scale-[0.98] transition-all tracking-wider uppercase">
                Đăng ký
            </button>
        </form>

        <p class="mt-8 text-center text-xs text-slate-400 font-medium">
            Đã có tài khoản? <a href="{{ route('login') }}" class="text-indigo-600 font-bold hover:underline">Đăng nhập</a>
        </p>
    </div>
</div>

<script>if (typeof lucide !== 'undefined') lucide.createIcons();</script>
</body>
</html>
