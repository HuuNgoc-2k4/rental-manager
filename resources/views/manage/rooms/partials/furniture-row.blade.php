{{--
    Một dòng nội thất trong modal sửa checklist phòng (cũng dùng làm <template> với index '__INDEX__').

    @param int|string $index
    @param array $item
--}}
<div data-row class="rounded-2xl border-2 border-slate-100 p-3 grid grid-cols-[4.5rem_minmax(0,1fr)] sm:grid-cols-[minmax(0,2fr)_4.5rem_minmax(9rem,1fr)_minmax(0,2fr)_auto] gap-3 items-end">
    <div class="col-span-2 sm:col-span-1">
        <label for="furniture-{{ $index }}-name" class="form-label">Tên đồ</label>
        <input type="text" id="furniture-{{ $index }}-name" name="furniture[{{ $index }}][name]" required maxlength="100" list="furniture-name-options" value="{{ $item['name'] ?? '' }}" placeholder="VD: Điều hòa" class="form-input">
    </div>
    <div>
        <label for="furniture-{{ $index }}-qty" class="form-label">SL</label>
        <input type="number" id="furniture-{{ $index }}-qty" name="furniture[{{ $index }}][quantity]" required min="1" max="99" value="{{ $item['quantity'] ?? 1 }}" class="form-input">
    </div>
    <div>
        <label for="furniture-{{ $index }}-condition" class="form-label">Hiện trạng</label>
        <select id="furniture-{{ $index }}-condition" name="furniture[{{ $index }}][condition]" class="form-input">
            @foreach(\App\Enums\Property\FurnitureCondition::cases() as $condition)
                <option value="{{ $condition->value }}" @selected(($item['condition'] ?? 'good') === $condition->value)>{{ $condition->label() }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-span-2 sm:col-span-1">
        <label for="furniture-{{ $index }}-note" class="form-label">Ghi chú</label>
        <input type="text" id="furniture-{{ $index }}-note" name="furniture[{{ $index }}][note]" maxlength="255" value="{{ $item['note'] ?? '' }}" placeholder="VD: Vết xước góc trái" class="form-input">
    </div>
    <button type="button" onclick="window.removeFurnitureRow(this)" class="btn btn-danger-soft col-span-2 sm:col-span-1 sm:w-11 sm:px-0" aria-label="Xóa món này">
        <i data-lucide="trash-2" class="w-4 h-4"></i><span class="sm:hidden">Xóa món này</span>
    </button>
</div>
