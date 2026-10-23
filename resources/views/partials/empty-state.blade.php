{{--
    Khối trống cho danh sách không có dữ liệu.

    @param string $icon   tên icon lucide
    @param string $title
    @param string|null $text
--}}
<div class="px-5 py-12 text-center">
    <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-3">
        <i data-lucide="{{ $icon }}" class="w-7 h-7 text-slate-400"></i>
    </div>
    <p class="text-sm font-bold text-slate-700">{{ $title }}</p>
    @if(!empty($text))
        <p class="text-xs text-slate-400 font-medium mt-1 break-words">{{ $text }}</p>
    @endif
</div>
