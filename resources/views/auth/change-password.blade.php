@extends('layouts.app')

@section('title', 'Đổi mật khẩu')
@section('subtitle', $forced ? 'Bạn đang dùng mật khẩu tạm, vui lòng đặt mật khẩu mới để tiếp tục.' : 'Đặt mật khẩu mới cho tài khoản của bạn.')

@section('content')
    <form method="POST" action="{{ route('password.update') }}" onsubmit="window.showGlobalLoading()"
          class="bg-white rounded-2xl border border-slate-200 p-5 md:p-6 w-full max-w-md space-y-4">
        @csrf
        @method('PUT')

        @foreach([['current_password', 'Mật khẩu hiện tại'], ['password', 'Mật khẩu mới'], ['password_confirmation', 'Nhập lại mật khẩu mới']] as [$name, $label])
            <div>
                <label for="{{ $name }}" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">{{ $label }}</label>
                <input type="password" id="{{ $name }}" name="{{ $name }}" required autocomplete="{{ $name === 'current_password' ? 'current-password' : 'new-password' }}"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all">
            </div>
        @endforeach

        <button type="submit" class="w-full min-h-[44px] bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-md hover:bg-indigo-700 transition-all">
            Đổi mật khẩu
        </button>
    </form>
@endsection
