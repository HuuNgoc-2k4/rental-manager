{{--
    Một dòng phí dịch vụ cố định trong form sửa hợp đồng (cũng dùng làm <template> với index '__INDEX__').

    @param int|string $index
    @param array $service
--}}
<div data-row class="rounded-2xl border-2 border-slate-100 p-3 grid grid-cols-[1fr_1fr_auto] gap-3 items-end">
    <div class="col-span-3">
        <label for="service-{{ $index }}-name" class="form-label">Khoản phí</label>
        <input type="text" id="service-{{ $index }}-name" name="services[{{ $index }}][name]" required maxlength="100" value="{{ $service['name'] ?? '' }}" placeholder="VD: Internet" class="form-input">
    </div>
    <div>
        <label for="service-{{ $index }}-amount" class="form-label">Đơn giá (đ)</label>
        <input type="number" min="0" step="1" id="service-{{ $index }}-amount" name="services[{{ $index }}][amount]" required value="{{ $service['amount'] ?? '' }}" class="form-input">
    </div>
    <div>
        <label for="service-{{ $index }}-unit" class="form-label">Đơn vị</label>
        <input type="text" id="service-{{ $index }}-unit" name="services[{{ $index }}][unit]" maxlength="50" value="{{ $service['unit'] ?? '' }}" placeholder="VD: người/tháng" class="form-input">
    </div>
    <button type="button" onclick="window.removeDocumentRow(this, 'service')" class="btn-icon bg-rose-50 text-rose-600 hover:bg-rose-100" aria-label="Xóa khoản phí">
        <i data-lucide="trash-2" class="w-4 h-4"></i>
    </button>
</div>
