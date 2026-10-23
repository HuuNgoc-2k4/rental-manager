{{--
    Avatar chữ cái đầu (không gọi dịch vụ ảnh ngoài). Màu chọn theo tổng mã ký tự của tên — cùng thuật toán
    với window.avatarHtml() ở layouts/partials/scripts để avatar vẽ bằng JS trong drawer trùng màu.

    @param string|null $name
    @param string $size  class kích thước + cỡ chữ, mặc định 'w-10 h-10 text-sm'
--}}
@php
    $palette = ['bg-indigo-100 text-indigo-700', 'bg-emerald-100 text-emerald-700', 'bg-amber-100 text-amber-700', 'bg-rose-100 text-rose-700', 'bg-sky-100 text-sky-700', 'bg-violet-100 text-violet-700'];
    $words = preg_split('/\s+/u', trim((string) ($name ?? '')), -1, PREG_SPLIT_NO_EMPTY);
    $initials = $words ? mb_strtoupper(mb_substr($words[0], 0, 1).(count($words) > 1 ? mb_substr(end($words), 0, 1) : '')) : '?';
    $colorIndex = array_sum(array_map('mb_ord', mb_str_split((string) ($name ?? '')) ?: [0])) % count($palette);
@endphp
<span class="inline-flex items-center justify-center shrink-0 rounded-full font-bold {{ $size ?? 'w-10 h-10 text-sm' }} {{ $palette[$colorIndex] }}" aria-hidden="true">{{ $initials }}</span>
