{{--
    Một dòng người ở cùng trong form sửa hợp đồng (cũng dùng làm <template> với index '__INDEX__').

    @param int|string $index
    @param array $member
--}}
<div data-row class="rounded-2xl border-2 border-slate-100 p-3 sm:p-4 grid grid-cols-1 sm:grid-cols-[2fr_1fr_1fr_1fr_auto] gap-3 items-end">
    <div>
        <label for="member-{{ $index }}-name" class="form-label">Họ và tên</label>
        <input type="text" id="member-{{ $index }}-name" name="members[{{ $index }}][full_name]" required maxlength="255" value="{{ $member['full_name'] ?? '' }}" class="form-input">
    </div>
    <div>
        <label for="member-{{ $index }}-dob" class="form-label">Ngày sinh</label>
        <input type="date" id="member-{{ $index }}-dob" name="members[{{ $index }}][date_of_birth]" value="{{ $member['date_of_birth'] ?? '' }}" class="form-input">
    </div>
    <div>
        <label for="member-{{ $index }}-id" class="form-label">CCCD</label>
        <input type="text" id="member-{{ $index }}-id" name="members[{{ $index }}][id_number]" inputmode="numeric" maxlength="20" value="{{ $member['id_number'] ?? '' }}" class="form-input">
    </div>
    <div>
        <label for="member-{{ $index }}-phone" class="form-label">Điện thoại</label>
        <input type="text" id="member-{{ $index }}-phone" name="members[{{ $index }}][phone]" inputmode="numeric" value="{{ $member['phone'] ?? '' }}" class="form-input">
    </div>
    <button type="button" onclick="window.removeDocumentRow(this, 'member')" class="btn btn-danger-soft sm:w-11 sm:px-0" aria-label="Xóa người này">
        <i data-lucide="trash-2" class="w-4 h-4"></i><span class="sm:hidden">Xóa người này</span>
    </button>
</div>
