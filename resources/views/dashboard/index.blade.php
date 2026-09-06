@extends('layouts.app')

@section('title', 'Tổng quan')
@section('subtitle', 'Xin chào ' . $user->name . ', đây là bảng điều khiển hệ thống quản lý nhà cho thuê.')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        @foreach([
            ['building-2', 'Khu trọ & phòng', 'Sơ đồ phòng, giá thuê, trạng thái trống / đã thuê'],
            ['file-text', 'Hợp đồng thuê', 'Đơn giá điện nước, phí cố định, cọc, gia hạn'],
            ['gauge', 'Chốt chỉ số', 'Ảnh đồng hồ, chỉ số đầu / cuối kỳ, tiêu thụ'],
            ['wallet', 'Hóa đơn & công nợ', 'Hóa đơn kỳ, phiếu thu từng phần, quá hạn'],
        ] as [$icon, $name, $desc])
            <div class="bg-white rounded-2xl border border-slate-200 p-5">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center mb-4">
                    <i data-lucide="{{ $icon }}" class="w-5 h-5 text-indigo-600"></i>
                </div>
                <p class="text-sm font-bold text-slate-800 break-words">{{ $name }}</p>
                <p class="text-xs text-slate-500 font-medium mt-1 break-words">{{ $desc }}</p>
                <p class="text-[10px] font-black text-amber-600 uppercase tracking-widest mt-4">Chưa triển khai</p>
            </div>
        @endforeach
    </div>
@endsection
