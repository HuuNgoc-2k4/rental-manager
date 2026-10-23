@extends('layouts.app')

@section('title', 'Đổi mật khẩu')
@section('subtitle', $forced ? 'Bạn đang dùng mật khẩu tạm, vui lòng đặt mật khẩu mới để tiếp tục.' : 'Đặt mật khẩu mới cho tài khoản của bạn.')

@section('content')
    <div class="w-full max-w-md space-y-4">
        @if($forced)
            <div class="rounded-2xl bg-amber-50 border border-amber-200 p-4 flex gap-3">
                <i data-lucide="shield-alert" class="w-5 h-5 text-amber-600 shrink-0"></i>
                <p class="text-sm font-semibold text-amber-800 break-words">Mật khẩu tạm chỉ dùng cho lần đăng nhập đầu. Đổi xong bạn mới vào được các trang khác.</p>
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" onsubmit="window.showGlobalLoading()" class="card p-5 sm:p-6 space-y-4">
            @csrf
            @method('PUT')

            <div class="flex items-center gap-3 pb-2">
                <span class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0"><i data-lucide="key-round" class="w-5 h-5"></i></span>
                <p class="text-sm font-semibold text-slate-500">Mật khẩu mới tối thiểu 8 ký tự và phải khác mật khẩu hiện tại.</p>
            </div>

            @foreach([['current_password', 'Mật khẩu hiện tại', 'current-password'], ['password', 'Mật khẩu mới', 'new-password'], ['password_confirmation', 'Nhập lại mật khẩu mới', 'new-password']] as [$name, $label, $autocomplete])
                <div>
                    <label for="{{ $name }}" class="form-label">{{ $label }}</label>
                    <div class="relative">
                        <input type="password" id="{{ $name }}" name="{{ $name }}" required autocomplete="{{ $autocomplete }}" class="form-input pr-12">
                        <button type="button" onclick="window.togglePassword('{{ $name }}', this)" aria-label="Hiện mật khẩu"
                                class="absolute inset-y-0 right-0 w-12 flex items-center justify-center text-slate-400 hover:text-slate-600">
                            <i data-lucide="eye" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>
            @endforeach

            <button type="submit" class="btn btn-primary w-full min-h-[52px]"><i data-lucide="check" class="w-4 h-4"></i> Đổi mật khẩu</button>
        </form>
    </div>
@endsection
