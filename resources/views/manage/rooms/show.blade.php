@extends('layouts.app')

@php
    $status = \App\Enums\Property\RoomStatus::tryFrom($room->status);
    $contract = $room->activeContract;
    $links = $contract?->liveLinks ?? collect();
    $residentCount = $links->where('approval_status', \App\Enums\Contract\MemberApproval::Approved->value)->count();
    $owner = $links->firstWhere('is_primary', true)?->tenant;
    $document = $contract?->document;
    $documentStatus = $document ? \App\Enums\Contract\ContractDocumentStatus::tryFrom($document->status) : null;
    $isVacant = $room->status === \App\Enums\Property\RoomStatus::Vacant->value;
    $isMaintenance = $room->status === \App\Enums\Property\RoomStatus::Maintenance->value;
    $meterTheme = ['electric' => ['zap', 'bg-amber-50 text-amber-600'], 'water' => ['droplet', 'bg-sky-50 text-sky-600']];
    $breadcrumbs = [
        ['name' => 'Phòng trọ', 'url' => route('manage.rooms.index')],
        ['name' => 'Phòng '.$room->name, 'url' => '#']
    ];
@endphp

@section('title', 'Phòng '.$room->name)
@section('subtitle', $room->property->name.' · '.$room->property->address)

@section('page_actions')
    @if($status)<span class="badge self-center {{ $status->color() }}">{{ $status->label() }}</span>@endif
    <button type="button" onclick="window.openOverlay('room-edit-modal')" class="btn btn-soft flex-1 sm:flex-none">
        <i data-lucide="pencil" class="w-4 h-4"></i> Sửa phòng
    </button>
@endsection

@section('content')
    {{-- Mobile: hộ thuê → hợp đồng → thông tin → đồng hồ → thao tác; desktop: hợp đồng ở cột phải --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-5 items-start">
        {{-- Hộ đang thuê --}}
        <div class="lg:col-span-2 card p-4 sm:p-5 space-y-4">
            <div class="flex items-center gap-2">
                <i data-lucide="home" class="w-4 h-4 text-indigo-500"></i>
                <p class="section-label">Hộ đang thuê</p>
            </div>

            @if($contract)
                <div>
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-base font-bold text-slate-900 break-words">{{ $owner?->full_name ?? '—' }}</p>
                            <p class="text-xs text-slate-500 font-semibold mt-0.5">Chủ hộ · vào ở từ {{ $contract->start_date->format('d/m/Y') }}</p>
                        </div>
                        <p class="text-sm font-bold text-slate-900 whitespace-nowrap tabular-nums">{{ $residentCount }} <span class="text-slate-400">người ở</span></p>
                    </div>
                </div>

                <div class="space-y-2">
                    @foreach($links as $link)
                        @php
                            $tenant = $link->tenant;
                            $approval = \App\Enums\Contract\MemberApproval::tryFrom($link->approval_status);
                            $isApproved = \App\Enums\Contract\MemberApproval::isApproved($link->approval_status);
                            $infoStatus = \App\Enums\Tenant\TenantInfoStatus::tryFrom($tenant->info_status);
                            $personal = array_filter([
                                $tenant->phone ? ['phone', $tenant->phone] : ['phone', 'Chưa có SĐT'],
                                $tenant->date_of_birth ? ['cake', $tenant->date_of_birth->format('d/m/Y')] : null,
                                $tenant->maskedIdNumber() ? ['id-card', $tenant->maskedIdNumber()] : null,
                                $tenant->permanent_address ? ['map-pin', $tenant->permanent_address] : null
                            ]);
                        @endphp
                        <div class="flex flex-col sm:flex-row sm:items-center gap-3 rounded-xl border border-slate-200 bg-white p-3">
                            <div class="flex items-start gap-3 min-w-0 flex-1">
                                @include('partials.avatar', ['name' => $tenant->full_name, 'size' => 'w-10 h-10 text-xs'])
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-slate-800 break-words">{{ $tenant->full_name }}</p>
                                    <div class="flex flex-wrap items-center gap-1.5 mt-1">
                                        @if($link->is_primary)
                                            <span class="badge bg-indigo-50 text-indigo-700 ring-indigo-600/20"><i data-lucide="crown" class="w-3 h-3"></i>Chủ hộ</span>
                                        @else
                                            <span class="badge bg-sky-50 text-sky-700 ring-sky-600/20"><i data-lucide="user" class="w-3 h-3"></i>Thành viên</span>
                                        @endif
                                        @if($approval && !$isApproved)<span class="badge {{ $approval->color() }}">{{ $approval->label() }}</span>@endif
                                        @if($infoStatus)<span class="badge {{ $infoStatus->color() }}">{{ $infoStatus->label() }}</span>@endif
                                        @if($tenant->user_id)
                                            <span class="badge bg-emerald-50 text-emerald-700 ring-emerald-600/20"><i data-lucide="shield-check" class="w-3 h-3"></i>Có tài khoản</span>
                                        @endif
                                    </div>
                                    <div class="flex flex-wrap gap-x-3 gap-y-1 mt-1.5 text-[11px] font-semibold text-slate-500">
                                        @foreach($personal as [$icon, $text])
                                            <span class="flex items-start gap-1 min-w-0 break-words"><i data-lucide="{{ $icon }}" class="w-3 h-3 mt-0.5 shrink-0 text-slate-300"></i>{{ $text }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="flex gap-2 shrink-0">
                                @if($isApproved && !$tenant->user_id && $tenant->phone)
                                    <button type="button" onclick="window.issueAccount({{ $tenant->id }}, @js($tenant->full_name))" class="btn btn-soft flex-1 px-4"><i data-lucide="key-round" class="w-4 h-4"></i>Cấp TK</button>
                                @endif
                                @if(!$link->is_primary)
                                    <button type="button" onclick="window.leaveRoom({{ $link->id }})" class="btn btn-secondary flex-1 px-4"><i data-lucide="log-out" class="w-4 h-4"></i>Rời đi</button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <button type="button" onclick="window.openOverlay('member-modal')" class="btn btn-soft"><i data-lucide="user-plus" class="w-4 h-4"></i> Thêm người ở cùng</button>
                    <button type="button" onclick="window.checkoutRoom()" class="btn btn-warning-soft"><i data-lucide="door-open" class="w-4 h-4"></i> Trả phòng</button>
                </div>
            @else
                <div class="flex items-center gap-3 rounded-xl bg-slate-50 p-4">
                    <i data-lucide="{{ $isMaintenance ? 'wrench' : 'door-open' }}" class="w-5 h-5 text-slate-400 shrink-0"></i>
                    <p class="text-sm font-bold text-slate-600">{{ $isMaintenance ? 'Phòng đang sửa, chưa cho thuê được' : 'Phòng đang trống' }}</p>
                </div>
                @if($isVacant)
                    <button type="button" onclick="window.openOverlay('rent-modal')" class="btn btn-primary w-full"><i data-lucide="key-square" class="w-4 h-4"></i> Cho thuê phòng</button>
                @endif
            @endif
        </div>

        {{-- Hợp đồng: cột phải trên desktop, ngay sau hộ thuê trên mobile --}}
        <div class="card p-4 sm:p-5 space-y-4 lg:col-start-3 lg:row-start-1 lg:row-span-4">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <i data-lucide="file-signature" class="w-4 h-4 text-indigo-500"></i>
                    <p class="section-label">Hợp đồng</p>
                </div>
                @if($documentStatus)<span class="badge {{ $documentStatus->color() }}">{{ $documentStatus->label() }}</span>@endif
            </div>

            @if(!$contract)
                <p class="text-xs text-slate-500 font-medium rounded-xl bg-slate-50 p-3 break-words">Chưa có hợp đồng. Cho thuê phòng để lập hợp đồng.</p>
            @else
                <dl class="space-y-2 text-xs font-semibold text-slate-500">
                    @if($document)
                        <div class="flex justify-between gap-3"><dt>Số hợp đồng</dt><dd class="font-bold text-slate-800 text-right break-words min-w-0">{{ $document->code }}</dd></div>
                    @endif
                    <div class="flex justify-between gap-3"><dt>Bắt đầu</dt><dd class="font-bold text-slate-800">{{ $contract->start_date->format('d/m/Y') }}</dd></div>
                    <div class="flex justify-between gap-3"><dt>Hết hạn</dt><dd class="font-bold text-slate-800">{{ $contract->end_date?->format('d/m/Y') ?? 'Không thời hạn' }}</dd></div>
                </dl>

                @if($documentStatus === \App\Enums\Contract\ContractDocumentStatus::Rejected && $document->reject_reason)
                    <div class="rounded-xl bg-rose-50 border border-rose-100 p-3">
                        <p class="text-[11px] font-bold text-rose-700 uppercase tracking-widest">Chủ hộ yêu cầu sửa</p>
                        <p class="text-sm font-medium text-rose-800 mt-1 break-words whitespace-pre-line">{{ $document->reject_reason }}</p>
                    </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-2">
                    @if(!$document)
                        <button type="button" onclick="window.generateDocument()" class="btn btn-primary sm:col-span-2 lg:col-span-1"><i data-lucide="file-plus-2" class="w-4 h-4"></i> Lập hợp đồng</button>
                    @else
                        <a href="{{ route('manage.contracts.documents.show', $document) }}" class="btn btn-primary"><i data-lucide="file-text" class="w-4 h-4"></i> Xem hợp đồng</a>
                        @if($document->isApproved())
                            <a href="{{ route('manage.contracts.documents.pdf', $document) }}" class="btn btn-soft"><i data-lucide="file-down" class="w-4 h-4"></i> Tải PDF</a>
                            <a href="{{ route('manage.contracts.documents.word', $document) }}" class="btn btn-outline"><i data-lucide="file-type" class="w-4 h-4"></i> Tải Word</a>
                        @else
                            <a href="{{ route('manage.contracts.documents.edit', $document) }}" class="btn btn-soft"><i data-lucide="pencil" class="w-4 h-4"></i> Sửa hợp đồng</a>
                        @endif
                    @endif
                </div>
            @endif
        </div>

        <div class="lg:col-span-2">
            @include('partials.room-info', ['room' => $room, 'document' => $document, 'residentCount' => $residentCount, 'canEditFurniture' => true])
        </div>

        {{-- Đồng hồ --}}
        <div class="lg:col-span-2 card p-4 sm:p-5 space-y-3">
            <div class="flex items-center gap-2">
                <i data-lucide="gauge" class="w-4 h-4 text-indigo-500"></i>
                <p class="section-label">Đồng hồ đang dùng</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach($room->currentMeters as $meter)
                    @php [$meterIcon, $meterTone] = $meterTheme[$meter->type] ?? ['gauge', 'bg-slate-100 text-slate-600']; @endphp
                    <div class="rounded-xl border border-slate-200 bg-white p-3 flex items-start gap-3">
                        <span class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 {{ $meterTone }}"><i data-lucide="{{ $meterIcon }}" class="w-5 h-5"></i></span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-bold text-slate-800">{{ \App\Enums\Meter\MeterType::tryFrom($meter->type)?->label() }}</p>
                            <p class="text-[11px] text-slate-500 font-semibold break-words mt-0.5">Đầu kỳ <b class="text-slate-700">{{ (float) $meter->initial_reading }}</b> · {{ $meter->integer_digits }}+{{ $meter->decimal_digits }} chữ số</p>
                            <p class="text-[11px] text-slate-400 font-semibold">Lắp từ {{ $meter->installed_at?->format('d/m/Y') }}</p>
                        </div>
                        <button type="button" onclick="window.openMeterModal(@js($meter->type), @js(\App\Enums\Meter\MeterType::tryFrom($meter->type)?->label()), {{ $meter->integer_digits }}, {{ $meter->decimal_digits }})"
                                class="btn-icon text-indigo-600 hover:bg-indigo-50" aria-label="Thay đồng hồ"><i data-lucide="refresh-cw" class="w-4 h-4"></i></button>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Công nợ (giai đoạn sau): chèn thẻ hóa đơn / công nợ của hợp đồng đang hiệu lực vào đây — xem docs/de-tai-nha-tro.md --}}

        {{-- Thao tác khác --}}
        <div class="lg:col-span-2 card p-4 sm:p-5 space-y-3">
            <div class="flex items-center gap-2">
                <i data-lucide="sliders-horizontal" class="w-4 h-4 text-indigo-500"></i>
                <p class="section-label">Thao tác khác</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                @if(!$contract)
                    @if($isMaintenance)
                        <button type="button" onclick="window.setRoomStatus('vacant')" class="btn btn-soft"><i data-lucide="door-open" class="w-4 h-4"></i> Đánh dấu phòng trống</button>
                    @else
                        <button type="button" onclick="window.setRoomStatus('maintenance')" class="btn btn-warning-soft"><i data-lucide="wrench" class="w-4 h-4"></i> Đánh dấu đang sửa</button>
                    @endif
                @endif
                <button type="button" onclick="window.deleteRoom()" class="btn btn-danger-soft"><i data-lucide="trash-2" class="w-4 h-4"></i> Xóa phòng</button>
            </div>
        </div>
    </div>

    {{-- Forms ẩn cho các thao tác xác nhận bằng Swal --}}
    <form id="room-status-form" method="POST" action="{{ route('manage.rooms.status', $room) }}" class="hidden">@csrf @method('PATCH')<input type="hidden" name="status"></form>
    <form id="room-delete-form" method="POST" action="{{ route('manage.rooms.destroy', $room) }}" class="hidden">@csrf @method('DELETE')</form>
    <form id="link-leave-form" method="POST" class="hidden">@csrf @method('DELETE')</form>
    <form id="account-form" method="POST" class="hidden">@csrf</form>
    @if($contract)
        <form id="checkout-form" method="POST" action="{{ route('manage.contracts.checkout', $contract) }}" class="hidden">@csrf<input type="hidden" name="ended_at"></form>
        <form id="document-generate-form" method="POST" action="{{ route('manage.contracts.documents.store', $contract) }}" class="hidden">@csrf</form>
    @endif

    {{-- Modal sửa phòng --}}
    <div id="room-edit-modal" data-close-fn="closeActiveModal" onclick="if (event.target === this) window.closeOverlay('room-edit-modal')"
         class="hidden fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm opacity-0 transition-opacity duration-300 flex items-end sm:items-center justify-center sm:p-4">
        <form method="POST" action="{{ route('manage.rooms.update', $room) }}" data-panel onsubmit="window.showGlobalLoading()"
              class="bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-lg max-h-[92dvh] overflow-y-auto scale-95 transition-transform duration-300 pb-safe">
            @csrf @method('PUT')
            @include('partials.modal-header', ['modalId' => 'room-edit-modal', 'icon' => 'settings-2', 'title' => 'Sửa phòng '.$room->name])
            <div class="p-5 sm:p-6 space-y-4">
                <div class="grid grid-cols-2 gap-3 sm:gap-4">
                    <div>
                        <label for="room-name" class="form-label">Tên / số phòng</label>
                        <input type="text" id="room-name" name="name" required maxlength="50" value="{{ old('name', $room->name) }}" class="form-input">
                    </div>
                    <div>
                        <label for="room-area" class="form-label">Diện tích (m²)</label>
                        <input type="number" step="0.01" id="room-area" name="area" value="{{ old('area', $room->area) }}" class="form-input">
                    </div>
                    <div class="col-span-2">
                        <label for="room-type" class="form-label">Loại phòng</label>
                        <input type="text" id="room-type" name="room_type" maxlength="50" list="room-type-options" value="{{ old('room_type', $room->room_type) }}" placeholder="VD: Phòng đơn, Có gác lửng, Studio" class="form-input">
                    </div>
                </div>
                <div>
                    <label for="room-rent" class="form-label">Giá thuê mặc định (đ/tháng)</label>
                    <input type="number" id="room-rent" name="base_rent" required min="0" value="{{ old('base_rent', $room->base_rent) }}" class="form-input">
                </div>
                @include('manage.rooms.partials.meter-fields', ['prefix' => 'room', 'meters' => $room->currentMeters->keyBy('type')])
                <p class="text-[11px] text-slate-400 font-medium -mt-2 ml-1 break-words">Sửa trực tiếp cấu hình đồng hồ đang dùng. Khi thay đồng hồ mới (chỉ số quay về 0), dùng nút "Thay đồng hồ" để giữ lịch sử.</p>
                <div>
                    <label for="room-note" class="form-label">Ghi chú nội bộ</label>
                    <textarea id="room-note" name="note" rows="2" maxlength="2000" placeholder="Người thuê không thấy ghi chú này" class="form-input">{{ old('note', $room->note) }}</textarea>
                </div>
            </div>
            <div class="px-5 sm:px-6 py-4 border-t border-slate-100 grid grid-cols-2 sm:flex sm:justify-end gap-3">
                <button type="button" onclick="window.closeOverlay('room-edit-modal')" class="btn btn-secondary">Đóng</button>
                <button type="submit" class="btn btn-primary"><i data-lucide="save" class="w-4 h-4"></i> Lưu phòng</button>
            </div>
        </form>
    </div>

    <datalist id="room-type-options">
        @foreach(\App\Models\Room::ROOM_TYPE_SUGGESTIONS as $suggestion)<option value="{{ $suggestion }}">@endforeach
    </datalist>
    <datalist id="furniture-name-options">
        @foreach(\App\Models\Room::FURNITURE_SUGGESTIONS as $suggestion)<option value="{{ $suggestion }}">@endforeach
    </datalist>

    {{-- Modal sửa nội thất & hiện trạng --}}
    @php $furnitureRows = old('furniture', $room->furniture ?? []); @endphp
    <div id="furniture-modal" data-close-fn="closeActiveModal" onclick="if (event.target === this) window.closeOverlay('furniture-modal')"
         class="hidden fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm opacity-0 transition-opacity duration-300 flex items-end sm:items-center justify-center sm:p-4">
        <form method="POST" action="{{ route('manage.rooms.furniture', $room) }}" data-panel onsubmit="window.showGlobalLoading()"
              class="bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-3xl max-h-[92dvh] overflow-y-auto scale-95 transition-transform duration-300 pb-safe">
            @csrf @method('PUT')
            @include('partials.modal-header', ['modalId' => 'furniture-modal', 'icon' => 'armchair', 'title' => 'Nội thất phòng '.$room->name])
            <div class="p-5 sm:p-6 space-y-4">
                <p class="text-xs text-slate-500 font-medium rounded-xl bg-slate-50 p-3 flex gap-2 break-words">
                    <i data-lucide="info" class="w-4 h-4 shrink-0 text-slate-400"></i>
                    Khai báo từng món và hiện trạng. Khi lập hợp đồng, danh sách này được chụp thành biên bản bàn giao kèm theo.
                </p>
                <div id="furniture-rows" class="space-y-3">
                    @foreach($furnitureRows as $index => $item)
                        @include('manage.rooms.partials.furniture-row', ['index' => $index, 'item' => $item])
                    @endforeach
                </div>
                <p id="furniture-empty" class="{{ empty($furnitureRows) ? '' : 'hidden' }} text-sm font-semibold text-slate-400 text-center py-4">Chưa có món nào.</p>
                <button type="button" onclick="window.addFurnitureRow()" class="btn btn-soft w-full"><i data-lucide="plus" class="w-4 h-4"></i> Thêm món</button>
            </div>
            <div class="px-5 sm:px-6 py-4 border-t border-slate-100 grid grid-cols-2 sm:flex sm:justify-end gap-3">
                <button type="button" onclick="window.closeOverlay('furniture-modal')" class="btn btn-secondary">Đóng</button>
                <button type="submit" class="btn btn-primary"><i data-lucide="save" class="w-4 h-4"></i> Lưu nội thất</button>
            </div>
        </form>
    </div>
    <template id="furniture-row-template">@include('manage.rooms.partials.furniture-row', ['index' => '__INDEX__', 'item' => []])</template>

    @if(!$contract && $isVacant)
        {{-- Modal cho thuê phòng --}}
        <div id="rent-modal" data-close-fn="closeActiveModal" onclick="if (event.target === this) window.closeOverlay('rent-modal')"
             class="hidden fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm opacity-0 transition-opacity duration-300 flex items-end sm:items-center justify-center sm:p-4">
            <form method="POST" action="{{ route('manage.rooms.rent', $room) }}" data-panel onsubmit="window.showGlobalLoading()"
                  class="bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-lg max-h-[92dvh] overflow-y-auto scale-95 transition-transform duration-300 pb-safe">
                @csrf
                @include('partials.modal-header', ['modalId' => 'rent-modal', 'icon' => 'key-square', 'title' => 'Cho thuê phòng '.$room->name])
                <div class="p-5 sm:p-6 space-y-4">
                    <div>
                        <label for="rent-tenant" class="form-label">Chủ hộ</label>
                        <select id="rent-tenant" name="tenant_id" onchange="document.getElementById('rent-new-tenant').classList.toggle('hidden', !!this.value)" class="form-input">
                            <option value="">— Tạo người mới —</option>
                            @foreach($availableTenants as $t)
                                <option value="{{ $t->id }}">{{ $t->full_name }}{{ $t->phone ? ' · '.$t->phone : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="rent-new-tenant" class="grid grid-cols-1 sm:grid-cols-2 gap-4 rounded-2xl bg-slate-50 p-4">
                        <div>
                            <label for="rent-name" class="form-label">Họ và tên người mới</label>
                            <input type="text" id="rent-name" name="full_name" maxlength="255" class="form-input bg-white">
                        </div>
                        <div>
                            <label for="rent-phone" class="form-label">Số điện thoại</label>
                            <input type="text" id="rent-phone" name="phone" inputmode="numeric" class="form-input bg-white">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="rent-start" class="form-label">Ngày vào ở</label>
                            <input type="date" id="rent-start" name="start_date" required value="{{ now()->toDateString() }}" class="form-input">
                        </div>
                        <div>
                            <label for="rent-end" class="form-label">Hết hạn dự kiến</label>
                            <input type="date" id="rent-end" name="end_date" class="form-input">
                        </div>
                    </div>
                    <label class="flex items-start gap-3 rounded-2xl border-2 border-indigo-100 bg-indigo-50/50 p-4 text-sm font-medium text-slate-600 cursor-pointer">
                        <input type="checkbox" name="issue_account" value="1" checked class="mt-0.5 w-5 h-5 rounded accent-indigo-600 shrink-0">
                        <span class="break-words min-w-0">
                            <span class="block font-bold text-slate-800">Cấp tài khoản cho chủ hộ</span>
                            Cần số điện thoại. Hệ thống hiện mật khẩu tạm đúng một lần.
                        </span>
                    </label>
                </div>
                <div class="px-5 sm:px-6 py-4 border-t border-slate-100 grid grid-cols-2 sm:flex sm:justify-end gap-3">
                    <button type="button" onclick="window.closeOverlay('rent-modal')" class="btn btn-secondary">Đóng</button>
                    <button type="submit" class="btn btn-primary"><i data-lucide="check" class="w-4 h-4"></i> Cho thuê</button>
                </div>
            </form>
        </div>
    @endif

    @if($contract)
        {{-- Modal thêm người ở cùng --}}
        <div id="member-modal" data-close-fn="closeActiveModal" onclick="if (event.target === this) window.closeOverlay('member-modal')"
             class="hidden fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm opacity-0 transition-opacity duration-300 flex items-end sm:items-center justify-center sm:p-4">
            <form method="POST" action="{{ route('manage.contracts.members.store', $contract) }}" data-panel onsubmit="window.showGlobalLoading()"
                  class="bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-lg max-h-[92dvh] overflow-y-auto scale-95 transition-transform duration-300 pb-safe">
                @csrf
                @include('partials.modal-header', ['modalId' => 'member-modal', 'icon' => 'user-plus', 'title' => 'Thêm người ở cùng'])
                <div class="p-5 sm:p-6 space-y-4">
                    <div>
                        <label for="member-tenant" class="form-label">Chọn người có sẵn</label>
                        <select id="member-tenant" name="tenant_id" class="form-input">
                            <option value="">— Tạo người mới —</option>
                            @foreach($availableTenants as $t)
                                <option value="{{ $t->id }}">{{ $t->full_name }}{{ $t->phone ? ' · '.$t->phone : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 rounded-2xl bg-slate-50 p-4">
                        <div>
                            <label for="member-name" class="form-label">Họ và tên người mới</label>
                            <input type="text" id="member-name" name="full_name" maxlength="255" class="form-input bg-white">
                        </div>
                        <div>
                            <label for="member-phone" class="form-label">Số điện thoại</label>
                            <input type="text" id="member-phone" name="phone" inputmode="numeric" class="form-input bg-white">
                        </div>
                    </div>
                </div>
                <div class="px-5 sm:px-6 py-4 border-t border-slate-100 grid grid-cols-2 sm:flex sm:justify-end gap-3">
                    <button type="button" onclick="window.closeOverlay('member-modal')" class="btn btn-secondary">Đóng</button>
                    <button type="submit" class="btn btn-primary"><i data-lucide="user-plus" class="w-4 h-4"></i> Thêm</button>
                </div>
            </form>
        </div>
    @endif

    {{-- Modal thay đồng hồ --}}
    <div id="meter-modal" data-close-fn="closeActiveModal" onclick="if (event.target === this) window.closeOverlay('meter-modal')"
         class="hidden fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm opacity-0 transition-opacity duration-300 flex items-end sm:items-center justify-center sm:p-4">
        <form method="POST" action="{{ route('manage.rooms.meters.replace', $room) }}" data-panel onsubmit="window.showGlobalLoading()"
              class="bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-md max-h-[92dvh] overflow-y-auto scale-95 transition-transform duration-300 pb-safe">
            @csrf
            <input type="hidden" name="type" id="meter-type">
            @include('partials.modal-header', ['modalId' => 'meter-modal', 'icon' => 'gauge', 'title' => 'Thay đồng hồ', 'titleId' => 'meter-modal-title', 'tone' => 'bg-amber-50 text-amber-600'])
            <div class="p-5 sm:p-6 space-y-4">
                <p class="text-xs text-slate-500 font-medium rounded-xl bg-slate-50 p-3 flex gap-2">
                    <i data-lucide="info" class="w-4 h-4 shrink-0 text-slate-400"></i>
                    Đồng hồ cũ được đóng lại theo ngày thay; đồng hồ mới bắt đầu từ chỉ số đầu bạn nhập.
                </p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="meter-int" class="form-label">Chữ số nguyên</label>
                        <input type="number" id="meter-int" name="integer_digits" required min="1" max="10" class="form-input">
                    </div>
                    <div>
                        <label for="meter-dec" class="form-label">Chữ số thập phân</label>
                        <input type="number" id="meter-dec" name="decimal_digits" required min="0" max="3" class="form-input">
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label for="meter-init" class="form-label">Chỉ số đầu</label>
                        <input type="number" step="any" id="meter-init" name="initial_reading" required min="0" value="0" class="form-input">
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label for="meter-date" class="form-label">Ngày thay</label>
                        <input type="date" id="meter-date" name="replaced_at" required max="{{ now()->toDateString() }}" value="{{ now()->toDateString() }}" class="form-input">
                    </div>
                </div>
            </div>
            <div class="px-5 sm:px-6 py-4 border-t border-slate-100 grid grid-cols-2 sm:flex sm:justify-end gap-3">
                <button type="button" onclick="window.closeOverlay('meter-modal')" class="btn btn-secondary">Đóng</button>
                <button type="submit" class="btn btn-primary"><i data-lucide="refresh-cw" class="w-4 h-4"></i> Thay đồng hồ</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    const roomName = window.escapeHtml(@js($room->name));
    const linkUrls = {
        leave: @js(route('manage.members.leave', ['link' => '__ID__'])),
        account: @js(route('manage.tenants.account', ['tenant' => '__ID__']))
    };

    // Chỉ số dòng tăng dần, không tái dùng chỉ số đã xóa để tên field không trùng nhau.
    let nextFurnitureIndex = {{ count($furnitureRows) }};

    window.addFurnitureRow = function () {
        const html = document.getElementById('furniture-row-template').innerHTML.replaceAll('__INDEX__', nextFurnitureIndex++);
        document.getElementById('furniture-rows').insertAdjacentHTML('beforeend', html);
        document.getElementById('furniture-empty').classList.add('hidden');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    };

    window.removeFurnitureRow = function (button) {
        button.closest('[data-row]').remove();
        document.getElementById('furniture-empty').classList.toggle('hidden', document.querySelectorAll('#furniture-rows [data-row]').length > 0);
    };

    window.setRoomStatus = function (status) {
        document.getElementById('room-status-form').querySelector('[name="status"]').value = status;
        window.confirmSubmit('room-status-form', { title: status === 'vacant' ? 'Đánh dấu phòng trống?' : 'Đánh dấu phòng đang sửa?', html: '<p class="text-sm text-slate-600">Phòng ' + roomName + '</p>' });
    };

    window.deleteRoom = function () {
        window.confirmSubmit('room-delete-form', { title: 'Xóa phòng?', html: '<p class="text-sm text-slate-600 break-words">Phòng ' + roomName + ' chỉ xóa được khi chưa từng có hợp đồng.</p>', confirmText: 'Xóa phòng', danger: true });
    };

    window.openMeterModal = function (type, label, integerDigits, decimalDigits) {
        document.getElementById('meter-type').value = type;
        document.getElementById('meter-modal-title').textContent = 'Thay đồng hồ ' + String(label).toLowerCase();
        document.getElementById('meter-int').value = integerDigits;
        document.getElementById('meter-dec').value = decimalDigits;
        document.getElementById('meter-init').value = 0;
        window.openOverlay('meter-modal');
    };

    window.leaveRoom = function (linkId) {
        document.getElementById('link-leave-form').action = linkUrls.leave.replace('__ID__', linkId);
        window.confirmSubmit('link-leave-form', { title: 'Người này rời phòng?', html: '<p class="text-sm text-slate-600 break-words">Tài khoản của họ (nếu có) sẽ bị khóa.</p>', confirmText: 'Xác nhận rời đi', danger: true });
    };

    window.issueAccount = function (tenantId, name) {
        document.getElementById('account-form').action = linkUrls.account.replace('__ID__', tenantId);
        window.confirmSubmit('account-form', { title: 'Cấp tài khoản?', html: '<p class="text-sm text-slate-600 break-words">Cấp tài khoản cho <b>' + window.escapeHtml(name) + '</b>. Mật khẩu tạm chỉ hiện một lần.</p>' });
    };

    window.generateDocument = function () {
        window.confirmSubmit('document-generate-form', { title: 'Lập hợp đồng phòng ' + roomName + '?', html: '<p class="text-sm text-slate-600 break-words">Hệ thống điền sẵn thông tin chủ hộ, người ở cùng, giá phòng và dịch vụ. Bạn sửa lại trước khi gửi chủ hộ duyệt.</p>', confirmText: 'Lập hợp đồng' });
    };

    window.checkoutRoom = function () {
        document.getElementById('checkout-form').querySelector('[name="ended_at"]').value = new Date().toISOString().slice(0, 10);
        window.confirmSubmit('checkout-form', { title: 'Trả phòng ' + roomName + '?', html: '<p class="text-sm text-slate-600 break-words">Hợp đồng kết thúc hôm nay, phòng chuyển về trống. Tài khoản của hộ vẫn mở để xem công nợ.</p>', confirmText: 'Trả phòng', danger: true });
    };
</script>
@endpush
