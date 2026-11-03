@extends('layouts.app')

@php
    $breadcrumbs = [
        ['name' => 'Phòng trọ', 'url' => route('manage.rooms.index')],
        ['name' => 'Phòng '.$document->room_name, 'url' => route('manage.rooms.show', $document->contract->room_id)],
        ['name' => 'Hợp đồng', 'url' => route('manage.contracts.documents.show', $document)],
        ['name' => 'Sửa', 'url' => '#']
    ];
    $partyFields = [
        ['full_name', 'Họ và tên', 'text', 'sm:col-span-2'],
        ['date_of_birth', 'Ngày sinh', 'date', ''],
        ['phone', 'Điện thoại', 'text', ''],
        ['id_number', 'Số CCCD / CMND', 'text', ''],
        ['id_issued_date', 'Ngày cấp', 'date', ''],
        ['id_issued_place', 'Nơi cấp', 'text', 'sm:col-span-2'],
        ['permanent_address', 'Địa chỉ thường trú', 'text', 'sm:col-span-2']
    ];
    $parties = [
        ['landlord', 'Bên A — bên cho thuê', 'building-2', 'Lần sau lập hợp đồng mới sẽ tự điền lại thông tin Bên A này.'],
        ['tenant', 'Bên B — bên thuê (chủ hộ)', 'user-round', null]
    ];
    $members = array_values(old('members', $document->members ?? []));
    $services = array_values(old('services', $document->services ?? []));
    $value = fn (string $key, $default = null) => old($key, $default);
@endphp

@section('title', 'Sửa hợp đồng phòng '.$document->room_name)
@section('subtitle', 'Thông tin đã điền sẵn từ hồ sơ khách và phòng. Lưu xong, hợp đồng về bản nháp và cần gửi chủ hộ duyệt lại.')

@section('page_actions')
    <a href="{{ route('manage.contracts.documents.show', $document) }}" class="btn btn-secondary flex-1 sm:flex-none">Hủy</a>
    <button type="submit" form="document-form" class="btn btn-primary flex-1 sm:flex-none"><i data-lucide="save" class="w-4 h-4"></i> Lưu</button>
@endsection

@section('content')
    <form id="document-form" method="POST" action="{{ route('manage.contracts.documents.update', $document) }}" onsubmit="window.showGlobalLoading()"
          class="grid grid-cols-1 xl:grid-cols-2 gap-4 sm:gap-5 items-start">
        @csrf @method('PUT')

        {{-- Thông tin chung --}}
        <div class="card p-4 sm:p-5 space-y-4">
            <div class="flex items-center gap-2"><i data-lucide="file-signature" class="w-4 h-4 text-indigo-500"></i><p class="section-label">Thông tin chung</p></div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                <div>
                    <label for="doc-code" class="form-label">Số hợp đồng</label>
                    <input type="text" id="doc-code" name="code" required maxlength="50" value="{{ $value('code', $document->code) }}" class="form-input">
                </div>
                <div>
                    <label for="doc-signed" class="form-label">Ngày lập</label>
                    <input type="date" id="doc-signed" name="signed_date" required value="{{ $value('signed_date', $document->signed_date?->toDateString()) }}" class="form-input">
                </div>
                <div class="sm:col-span-2">
                    <label for="doc-place" class="form-label">Nơi lập</label>
                    <input type="text" id="doc-place" name="signed_place" maxlength="500" value="{{ $value('signed_place', $document->signed_place) }}" class="form-input">
                </div>
                <div>
                    <label for="doc-start" class="form-label">Thuê từ ngày</label>
                    <input type="date" id="doc-start" name="start_date" required value="{{ $value('start_date', $document->start_date?->toDateString()) }}" class="form-input">
                </div>
                <div>
                    <label for="doc-end" class="form-label">Đến ngày</label>
                    <input type="date" id="doc-end" name="end_date" value="{{ $value('end_date', $document->end_date?->toDateString()) }}" class="form-input">
                    <p class="text-[11px] text-slate-400 font-medium mt-1 ml-1">Để trống = không xác định thời hạn.</p>
                </div>
            </div>
        </div>

        {{-- Phòng thuê --}}
        <div class="card p-4 sm:p-5 space-y-4">
            <div class="flex items-center gap-2"><i data-lucide="door-closed" class="w-4 h-4 text-indigo-500"></i><p class="section-label">Phòng thuê</p></div>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-4">
                <div>
                    <label for="doc-room" class="form-label">Số phòng</label>
                    <input type="text" id="doc-room" name="room_name" required maxlength="50" value="{{ $value('room_name', $document->room_name) }}" class="form-input">
                </div>
                <div>
                    <label for="doc-area" class="form-label">Diện tích m²</label>
                    <input type="number" step="0.01" min="0" id="doc-area" name="room_area" value="{{ $value('room_area', $document->room_area) }}" class="form-input">
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <label for="doc-max" class="form-label">Số người tối đa</label>
                    <input type="number" min="1" id="doc-max" name="max_occupants" required value="{{ $value('max_occupants', $document->max_occupants) }}" class="form-input">
                </div>
                <div class="col-span-2 sm:col-span-3">
                    <label for="doc-address" class="form-label">Địa chỉ</label>
                    <input type="text" id="doc-address" name="property_address" required maxlength="500" value="{{ $value('property_address', $document->property_address) }}" class="form-input">
                </div>
                <div class="col-span-2 sm:col-span-3">
                    @if(!empty($document->equipment_items))
                        <p class="form-label">Trang thiết bị bàn giao (chụp từ checklist phòng lúc lập hợp đồng)</p>
                        <ul class="rounded-xl border border-slate-100 divide-y divide-slate-100 mb-3">
                            @foreach($document->equipment_items as $item)
                                <li class="flex items-start justify-between gap-3 px-3 py-2 text-sm font-semibold text-slate-700">
                                    <span class="min-w-0 break-words">{{ $item['name'] ?? '' }}@if(($item['quantity'] ?? 1) > 1) × {{ $item['quantity'] }}@endif @if(filled($item['note'] ?? null))<span class="text-slate-400 font-medium">· {{ $item['note'] }}</span>@endif</span>
                                    <span class="text-xs shrink-0 text-slate-500">{{ \App\Enums\Property\FurnitureCondition::tryFrom($item['condition'] ?? '')?->label() }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    <label for="doc-equipment" class="form-label">{{ empty($document->equipment_items) ? 'Trang thiết bị bàn giao' : 'Ghi chú thiết bị bàn giao thêm' }}</label>
                    <textarea id="doc-equipment" name="equipment" rows="2" maxlength="2000" placeholder="VD: 01 điều hòa, 01 bình nóng lạnh, 01 giường, 01 tủ quần áo" class="form-input">{{ $value('equipment', $document->equipment) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Bên A, Bên B --}}
        @foreach($parties as [$key, $heading, $icon, $hint])
            <div class="card p-4 sm:p-5 space-y-4">
                <div class="flex items-center gap-2"><i data-lucide="{{ $icon }}" class="w-4 h-4 text-indigo-500"></i><p class="section-label">{{ $heading }}</p></div>
                @if($hint)
                    <p class="text-xs text-slate-500 font-medium rounded-xl bg-slate-50 p-3 flex gap-2 break-words"><i data-lucide="info" class="w-4 h-4 shrink-0 text-slate-400"></i>{{ $hint }}</p>
                @endif
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    @foreach($partyFields as [$field, $label, $type, $span])
                        <div class="{{ $span }}">
                            <label for="{{ $key }}-{{ $field }}" class="form-label">{{ $label }}</label>
                            <input type="{{ $type }}" id="{{ $key }}-{{ $field }}" name="{{ $key }}[{{ $field }}]" @if($field === 'full_name') required @endif
                                   @if($field === 'phone' || $field === 'id_number') inputmode="numeric" @endif
                                   value="{{ $value($key.'.'.$field, $document->{$key}[$field] ?? null) }}" class="form-input">
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        {{-- Người ở cùng --}}
        <div class="card p-4 sm:p-5 space-y-4 xl:col-span-2">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2"><i data-lucide="users-round" class="w-4 h-4 text-indigo-500"></i><p class="section-label">Người ở cùng (thuộc Bên B)</p></div>
                <button type="button" onclick="window.addDocumentRow('member')" class="btn btn-soft px-4"><i data-lucide="plus" class="w-4 h-4"></i> Thêm</button>
            </div>
            <div id="member-rows" class="space-y-3">
                @foreach($members as $index => $member)
                    @include('manage.contracts.partials.member-row', ['index' => $index, 'member' => $member])
                @endforeach
            </div>
            <p id="member-empty" class="{{ empty($members) ? '' : 'hidden' }} text-sm font-semibold text-slate-400 text-center py-4">Không có người ở cùng.</p>
        </div>

        {{-- Giá thuê và thanh toán --}}
        <div class="card p-4 sm:p-5 space-y-4">
            <div class="flex items-center gap-2"><i data-lucide="wallet" class="w-4 h-4 text-indigo-500"></i><p class="section-label">Giá thuê và thanh toán</p></div>
            <div class="grid grid-cols-2 gap-3 sm:gap-4">
                @foreach([['rent', 'Giá thuê (đ/tháng)'], ['deposit', 'Tiền cọc (đ)'], ['electric_price', 'Giá điện (đ/kWh)'], ['water_price', 'Giá nước (đ/m³)']] as [$field, $label])
                    <div>
                        <label for="doc-{{ $field }}" class="form-label">{{ $label }}</label>
                        <input type="number" min="0" step="1" id="doc-{{ $field }}" name="{{ $field }}" required value="{{ $value($field, $document->{$field}) }}"
                               oninput="window.previewVnd(this)" class="form-input">
                        <p class="text-[11px] text-slate-400 font-semibold mt-1 ml-1 tabular-nums" data-vnd-preview></p>
                    </div>
                @endforeach
                <div>
                    <label for="doc-payment-day" class="form-label">Hạn thanh toán (ngày)</label>
                    <input type="number" min="1" max="28" id="doc-payment-day" name="payment_day" required value="{{ $value('payment_day', $document->payment_day) }}" class="form-input">
                </div>
                <div>
                    <label for="doc-payment-method" class="form-label">Hình thức</label>
                    <input type="text" id="doc-payment-method" name="payment_method" maxlength="255" value="{{ $value('payment_method', $document->payment_method) }}" class="form-input">
                </div>
            </div>
        </div>

        {{-- Phí dịch vụ cố định --}}
        <div class="card p-4 sm:p-5 space-y-4">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2"><i data-lucide="receipt-text" class="w-4 h-4 text-indigo-500"></i><p class="section-label">Phí dịch vụ cố định</p></div>
                <button type="button" onclick="window.addDocumentRow('service')" class="btn btn-soft px-4"><i data-lucide="plus" class="w-4 h-4"></i> Thêm</button>
            </div>
            <div id="service-rows" class="space-y-3">
                @foreach($services as $index => $service)
                    @include('manage.contracts.partials.service-row', ['index' => $index, 'service' => $service])
                @endforeach
            </div>
            <p id="service-empty" class="{{ empty($services) ? '' : 'hidden' }} text-sm font-semibold text-slate-400 text-center py-4">Không có phí cố định.</p>
        </div>

        {{-- Thỏa thuận khác --}}
        <div class="card p-4 sm:p-5 space-y-3 xl:col-span-2">
            <div class="flex items-center gap-2"><i data-lucide="scroll-text" class="w-4 h-4 text-indigo-500"></i><p class="section-label">Thỏa thuận khác</p></div>
            <textarea name="extra_terms" rows="4" maxlength="10000" aria-label="Thỏa thuận khác" placeholder="Mỗi dòng một ý. VD: Không nuôi thú cưng. Giờ đóng cổng 23h." class="form-input">{{ $value('extra_terms', $document->extra_terms) }}</textarea>
            <p class="text-[11px] text-slate-400 font-medium ml-1">Chèn thành một điều riêng trước điều "Giải quyết tranh chấp". Các điều khoản chuẩn (quyền, nghĩa vụ, chấm dứt, hoàn cọc) đã có sẵn trong mẫu.</p>
        </div>

        <div class="xl:col-span-2 grid grid-cols-2 sm:flex sm:justify-end gap-3">
            <a href="{{ route('manage.contracts.documents.show', $document) }}" class="btn btn-secondary">Hủy</a>
            <button type="submit" class="btn btn-primary"><i data-lucide="save" class="w-4 h-4"></i> Lưu hợp đồng</button>
        </div>
    </form>

    <template id="member-row-template">@include('manage.contracts.partials.member-row', ['index' => '__INDEX__', 'member' => []])</template>
    <template id="service-row-template">@include('manage.contracts.partials.service-row', ['index' => '__INDEX__', 'service' => []])</template>
@endsection

@push('scripts')
<script>
    // Chỉ số dòng tăng dần, không tái dùng chỉ số đã xóa để tên field không trùng nhau.
    const nextRowIndex = { member: {{ count($members) }}, service: {{ count($services) }} };

    window.addDocumentRow = function (type) {
        const html = document.getElementById(type + '-row-template').innerHTML.replaceAll('__INDEX__', nextRowIndex[type]++);
        document.getElementById(type + '-rows').insertAdjacentHTML('beforeend', html);
        document.getElementById(type + '-empty').classList.add('hidden');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    };

    window.removeDocumentRow = function (button, type) {
        button.closest('[data-row]').remove();
        document.getElementById(type + '-empty').classList.toggle('hidden', document.querySelectorAll('#' + type + '-rows [data-row]').length > 0);
    };

    /** Hiện số tiền dạng 2.500.000đ dưới ô nhập để dễ đọc số 0. */
    window.previewVnd = function (input) {
        const preview = input.parentElement.querySelector('[data-vnd-preview]');
        if (preview) preview.textContent = input.value === '' ? '' : window.formatVnd(input.value);
    };
    document.querySelectorAll('[oninput="window.previewVnd(this)"]').forEach(window.previewVnd);
</script>
@endpush
