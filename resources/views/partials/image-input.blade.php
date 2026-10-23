{{--
    Ô chọn / chụp ảnh có xem trước (ảnh CCCD). Input file ẩn, bấm ô trống hoặc "Đổi ảnh" để chọn.
    Xem trước ảnh mới do listener chung ở layouts/partials/scripts xử lý; JS trang đổi ảnh hiện có bằng window.setImageField().

    @param string $inputId
    @param string $name
    @param string $label
    @param string|null $url  ảnh đã tải (route riêng tư), null = chưa có
--}}
<div data-image-field>
    <p class="form-label">{{ $label }}</p>
    <a data-image-preview href="{{ $url ?? '#' }}" target="_blank" rel="noopener"
       class="{{ $url ? '' : 'hidden' }} block rounded-xl overflow-hidden border border-slate-200 bg-slate-100 aspect-[3/2]">
        <img data-image-img @if($url) src="{{ $url }}" @endif loading="lazy" alt="{{ $label }}" class="w-full h-full object-cover">
    </a>
    <label for="{{ $inputId }}" data-image-empty
           class="{{ $url ? 'hidden' : '' }} flex flex-col items-center justify-center gap-1 rounded-xl border-2 border-dashed border-slate-200 bg-white aspect-[3/2] text-slate-400 cursor-pointer hover:border-indigo-300 hover:text-indigo-500 transition-colors">
        <i data-lucide="camera" class="w-6 h-6"></i>
        <span class="text-[11px] font-bold text-center px-2">Chụp / chọn ảnh</span>
    </label>
    <label for="{{ $inputId }}" data-image-change
           class="{{ $url ? '' : 'hidden' }} mt-1 flex items-center justify-center gap-1.5 min-h-[40px] rounded-xl text-xs font-bold text-indigo-600 hover:bg-indigo-50 cursor-pointer">
        <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i> Đổi ảnh
    </label>
    <input type="file" id="{{ $inputId }}" name="{{ $name }}" accept="image/*" capture="environment" class="sr-only">
</div>
