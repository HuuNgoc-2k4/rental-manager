@extends('layouts.app')

@section('title', 'Phòng của tôi')
@section('subtitle', 'Xin chào ' . $user->name . ', đây là thông tin thuê phòng của bạn.')

@section('content')
    <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center">
        <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-4">
            <i data-lucide="receipt-text" class="w-6 h-6 text-slate-400"></i>
        </div>
        <p class="text-sm font-bold text-slate-700">Chưa có hóa đơn nào</p>
        <p class="text-xs text-slate-500 font-medium mt-1 break-words">
            Hợp đồng và hóa đơn của bạn sẽ hiện ở đây sau khi chủ hộ chốt kỳ.
        </p>
    </div>
@endsection
