{{--
    Đầu modal dạng bottom-sheet (dính trên cùng khi cuộn).

    @param string $modalId   id overlay để nút đóng gọi closeOverlay
    @param string $icon      tên icon lucide
    @param string $title
    @param string|null $titleId  id cho tiêu đề khi JS cần đổi chữ
    @param string|null $tone     class màu ô icon, mặc định indigo
--}}
<div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-center gap-3 sticky top-0 bg-white z-10">
    <span class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 {{ $tone ?? 'bg-indigo-50 text-indigo-600' }}"><i data-lucide="{{ $icon }}" class="w-5 h-5"></i></span>
    <h3 @if(!empty($titleId)) id="{{ $titleId }}" @endif class="text-lg font-bold text-slate-800 flex-1 min-w-0 break-words">{{ $title }}</h3>
    <button type="button" onclick="window.closeOverlay('{{ $modalId }}')" aria-label="Đóng" class="btn-icon -mr-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100"><i data-lucide="x" class="w-5 h-5"></i></button>
</div>
