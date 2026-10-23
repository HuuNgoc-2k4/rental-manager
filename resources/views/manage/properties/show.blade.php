@extends('layouts.app')

@section('title', $property->name)
@section('subtitle', $property->address)

@php
    $breadcrumbs = [
        ['name' => 'Khu trọ', 'url' => route('manage.properties.index')],
        ['name' => $property->name, 'url' => '#']
    ];
    $inp = 'w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all';
    $lbl = 'block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2';
    $tileColors = [
        'vacant' => 'border-green-200 bg-green-50 hover:border-green-400',
        'occupied' => 'border-indigo-200 bg-indigo-50 hover:border-indigo-400',
        'maintenance' => 'border-amber-200 bg-amber-50 hover:border-amber-400'
    ];
    $roomData = $rooms->mapWithKeys(function ($room) {
        $contract = $room->activeContract;
        $residents = $contract?->residents ?? collect();
        $owner = $residents->firstWhere('is_primary', true);

        return [$room->id => [
            'id' => $room->id,
            'name' => $room->name,
            'floor' => $room->floor,
            'area' => $room->area,
            'max_occupants' => $room->max_occupants,
            'base_rent' => $room->base_rent,
            'note' => $room->note,
            'status' => $room->status,
            'status_label' => \App\Enums\Property\RoomStatus::tryFrom($room->status)?->label(),
            'has_contract' => (bool) $contract,
            'contract_id' => $contract?->id,
            'links' => ($contract?->liveLinks ?? collect())->map(fn ($l) => [
                'id' => $l->id,
                'tenant_id' => $l->tenant_id,
                'name' => $l->tenant?->full_name,
                'phone' => $l->tenant?->phone,
                'is_primary' => $l->is_primary,
                'approval' => $l->approval_status,
                'approval_label' => \App\Enums\Contract\MemberApproval::tryFrom($l->approval_status)?->label(),
                'has_account' => (bool) $l->tenant?->user_id
            ])->values(),
            'owner_name' => $owner?->tenant?->full_name,
            'residents' => $residents->count(),
            'meters' => $room->currentMeters->map(fn ($m) => [
                'type' => $m->type,
                'label' => \App\Enums\Meter\MeterType::tryFrom($m->type)?->label(),
                'integer_digits' => $m->integer_digits,
                'decimal_digits' => $m->decimal_digits,
                'initial_reading' => $m->initial_reading,
                'installed_at' => $m->installed_at?->format('d/m/Y')
            ])->values()
        ]];
    });
@endphp

@section('page_actions')
    <button type="button" onclick="window.openOverlay('room-create-modal')"
            class="min-h-[44px] px-5 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-md hover:bg-indigo-700 transition-all flex items-center gap-2">
        <i data-lucide="plus" class="w-4 h-4"></i> Thêm phòng
    </button>
    <form id="property-delete-form" method="POST" action="{{ route('manage.properties.destroy', $property) }}" class="hidden">@csrf @method('DELETE')</form>
    <button type="button" aria-label="Xóa khu"
            onclick="window.confirmSubmit('property-delete-form', { title: 'Xóa khu trọ?', html: '<p class=&quot;text-sm text-slate-600 break-words&quot;>Chỉ xóa được khi khu chưa có phòng nào.</p>', confirmText: 'Xóa khu', danger: true })"
            class="min-h-[44px] w-11 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-rose-500 transition-colors">
        <i data-lucide="trash-2" class="w-4 h-4"></i>
    </button>
@endsection

@section('content')
    <div class="flex flex-wrap gap-3 mb-4 text-[11px] font-bold">
        @foreach($statuses as $st)
            <span class="px-2.5 py-1 rounded-full ring-1 ring-inset {{ $st->color() }}">{{ $st->label() }}: {{ $rooms->where('status', $st->value)->count() }}</span>
        @endforeach
    </div>

    @forelse($floors as $floor => $floorRooms)
        <div class="mb-6">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">
                {{ $floor === 'none' ? 'Chưa phân tầng' : 'Tầng '.$floor }}
            </p>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-6 gap-3">
                @foreach($floorRooms as $room)
                    @php
                        $data = $roomData[$room->id];
                    @endphp
                    <button type="button" onclick="window.openRoomDrawer({{ $room->id }})"
                            class="text-left min-h-[96px] rounded-2xl border-2 p-3 transition-colors {{ $tileColors[$room->status] ?? 'border-slate-200 bg-white' }}">
                        <p class="text-lg font-black text-slate-800 break-words min-w-0">{{ $room->name }}</p>
                        <p class="text-[11px] font-bold text-slate-600 break-words min-w-0 mt-1">
                            {{ $data['owner_name'] ?? $data['status_label'] }}
                        </p>
                        <p class="text-[11px] font-medium text-slate-500 mt-1 whitespace-nowrap">
                            {{ $data['residents'] }}/{{ $room->max_occupants }} người
                        </p>
                    </button>
                @endforeach
            </div>
        </div>
    @empty
        <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center">
            <p class="text-sm font-bold text-slate-700">Khu này chưa có phòng</p>
            <p class="text-xs text-slate-500 font-medium mt-1">Bấm "Thêm phòng" để khai báo phòng và đồng hồ điện nước.</p>
        </div>
    @endforelse

    {{-- Forms ẩn: đặt NGOÀI drawer để không lồng form trong form --}}
    <form id="room-status-form" method="POST" class="hidden">@csrf @method('PATCH')<input type="hidden" name="status"></form>
    <form id="room-delete-form" method="POST" class="hidden">@csrf @method('DELETE')</form>
    <form id="link-leave-form" method="POST" class="hidden">@csrf @method('DELETE')</form>
    <form id="account-form" method="POST" class="hidden">@csrf</form>
    <form id="checkout-form" method="POST" class="hidden">@csrf<input type="hidden" name="ended_at"></form>

    {{-- Drawer phòng --}}
    <div id="room-drawer" data-drawer-overlay data-close-fn="closeActiveDrawer" onclick="if (event.target === this) window.closeOverlay('room-drawer')"
         class="hidden fixed inset-0 z-[100] bg-slate-900/40 opacity-0 transition-opacity duration-300">
        <div data-panel class="absolute right-0 top-0 h-full w-full lg:w-[52vw] xl:w-[44vw] bg-white shadow-2xl z-[101] translate-x-full transition-transform duration-300 ease-in-out overflow-y-auto">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white z-10">
                <div class="min-w-0">
                    <p id="room-drawer-status" class="text-[10px] font-black text-slate-400 uppercase tracking-widest"></p>
                    <h3 id="room-drawer-title" class="text-lg font-black text-slate-800 break-words min-w-0"></h3>
                </div>
                <button type="button" onclick="window.closeOverlay('room-drawer')" aria-label="Đóng" class="w-11 h-11 flex items-center justify-center text-slate-400 hover:text-slate-600 shrink-0">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="p-6 space-y-6">
                <div id="room-household" class="rounded-xl bg-slate-50 p-4 text-sm"></div>
                <div id="room-links" class="space-y-2"></div>
                <div id="room-rent-actions" class="flex flex-col sm:flex-row gap-3"></div>

                <form id="room-edit-form" method="POST" onsubmit="window.showGlobalLoading()" class="space-y-4">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="room-name" class="{{ $lbl }}">Tên / số phòng</label>
                            <input type="text" id="room-name" name="name" required maxlength="50" class="{{ $inp }}">
                        </div>
                        <div>
                            <label for="room-floor" class="{{ $lbl }}">Tầng</label>
                            <input type="number" id="room-floor" name="floor" class="{{ $inp }}">
                        </div>
                        <div>
                            <label for="room-area" class="{{ $lbl }}">Diện tích (m²)</label>
                            <input type="number" step="0.01" id="room-area" name="area" class="{{ $inp }}">
                        </div>
                        <div>
                            <label for="room-max" class="{{ $lbl }}">Số người tối đa</label>
                            <input type="number" id="room-max" name="max_occupants" required min="1" class="{{ $inp }}">
                        </div>
                    </div>
                    <div>
                        <label for="room-rent" class="{{ $lbl }}">Giá thuê mặc định (đ/tháng)</label>
                        <input type="number" id="room-rent" name="base_rent" required min="0" class="{{ $inp }}">
                    </div>
                    <div>
                        <label for="room-note" class="{{ $lbl }}">Ghi chú</label>
                        <textarea id="room-note" name="note" rows="2" maxlength="2000" class="{{ $inp }}"></textarea>
                    </div>
                    <button type="submit" class="w-full min-h-[44px] bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-md hover:bg-indigo-700 transition-all">Lưu phòng</button>
                </form>

                <div>
                    <p class="{{ $lbl }}">Đồng hồ đang dùng</p>
                    <div id="room-meters" class="space-y-2"></div>
                </div>

                <div id="room-manual-actions" class="flex flex-col sm:flex-row gap-3 pt-2 border-t border-slate-100"></div>
            </div>
        </div>
    </div>

    {{-- Modal thêm phòng --}}
    <div id="room-create-modal" data-close-fn="closeActiveModal" onclick="if (event.target === this) window.closeOverlay('room-create-modal')"
         class="hidden fixed inset-0 z-50 bg-slate-900/40 opacity-0 transition-opacity duration-300 flex items-center justify-center p-4">
        <form method="POST" action="{{ route('manage.properties.rooms.store', $property) }}" data-panel onsubmit="window.showGlobalLoading()"
              class="bg-white rounded-2xl w-full max-w-xl max-h-[90dvh] overflow-y-auto p-5 md:p-6 space-y-4 scale-95 transition-transform duration-300">
            @csrf
            <h3 class="text-lg font-black text-slate-800">Thêm phòng</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="new-name" class="{{ $lbl }}">Tên / số phòng</label>
                    <input type="text" id="new-name" name="name" required maxlength="50" value="{{ old('name') }}" class="{{ $inp }}">
                </div>
                <div>
                    <label for="new-floor" class="{{ $lbl }}">Tầng</label>
                    <input type="number" id="new-floor" name="floor" value="{{ old('floor') }}" class="{{ $inp }}">
                </div>
                <div>
                    <label for="new-area" class="{{ $lbl }}">Diện tích (m²)</label>
                    <input type="number" step="0.01" id="new-area" name="area" value="{{ old('area') }}" class="{{ $inp }}">
                </div>
                <div>
                    <label for="new-max" class="{{ $lbl }}">Số người tối đa</label>
                    <input type="number" id="new-max" name="max_occupants" required min="1" value="{{ old('max_occupants', 2) }}" class="{{ $inp }}">
                </div>
            </div>
            <div>
                <label for="new-rent" class="{{ $lbl }}">Giá thuê mặc định (đ/tháng)</label>
                <input type="number" id="new-rent" name="base_rent" required min="0" value="{{ old('base_rent') }}" class="{{ $inp }}">
            </div>

            @foreach(\App\Enums\Meter\MeterType::cases() as $type)
                <fieldset class="rounded-xl border border-slate-200 p-4">
                    <legend class="px-2 text-[10px] font-black text-slate-500 uppercase tracking-widest">Đồng hồ {{ $type->label() }}</legend>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label for="new-{{ $type->value }}-int" class="{{ $lbl }}">Chữ số nguyên</label>
                            <input type="number" id="new-{{ $type->value }}-int" name="{{ $type->value }}_integer_digits" required min="1" max="10" value="{{ old($type->value.'_integer_digits', 5) }}" class="{{ $inp }}">
                        </div>
                        <div>
                            <label for="new-{{ $type->value }}-dec" class="{{ $lbl }}">Chữ số thập phân</label>
                            <input type="number" id="new-{{ $type->value }}-dec" name="{{ $type->value }}_decimal_digits" required min="0" max="3" value="{{ old($type->value.'_decimal_digits', 0) }}" class="{{ $inp }}">
                        </div>
                        <div>
                            <label for="new-{{ $type->value }}-init" class="{{ $lbl }}">Chỉ số đầu</label>
                            <input type="number" step="any" id="new-{{ $type->value }}-init" name="{{ $type->value }}_initial_reading" required min="0" value="{{ old($type->value.'_initial_reading', 0) }}" class="{{ $inp }}">
                        </div>
                    </div>
                </fieldset>
            @endforeach

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="window.closeOverlay('room-create-modal')" class="min-h-[44px] px-6 bg-slate-100 text-slate-600 rounded-xl text-sm font-bold hover:bg-slate-200 transition-all">Đóng</button>
                <button type="submit" class="min-h-[44px] px-6 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-md hover:bg-indigo-700 transition-all">Thêm phòng</button>
            </div>
        </form>
    </div>

    {{-- Modal cho thuê phòng --}}
    <div id="rent-modal" data-close-fn="closeActiveModal" onclick="if (event.target === this) window.closeOverlay('rent-modal')"
         class="hidden fixed inset-0 z-50 bg-slate-900/40 opacity-0 transition-opacity duration-300 flex items-center justify-center p-4">
        <form id="rent-form" method="POST" data-panel onsubmit="window.showGlobalLoading()"
              class="bg-white rounded-2xl w-full max-w-lg max-h-[90dvh] overflow-y-auto p-5 md:p-6 space-y-4 scale-95 transition-transform duration-300">
            @csrf
            <h3 id="rent-title" class="text-lg font-black text-slate-800">Cho thuê phòng</h3>
            <div>
                <label for="rent-tenant" class="{{ $lbl }}">Chọn khách có sẵn</label>
                <select id="rent-tenant" name="tenant_id" onchange="window.toggleNewTenant()" class="{{ $inp }}">
                    <option value="">— Tạo khách mới —</option>
                    @foreach($availableTenants as $t)
                        <option value="{{ $t->id }}">{{ $t->full_name }}{{ $t->phone ? ' · '.$t->phone : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div id="rent-new-tenant" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="rent-name" class="{{ $lbl }}">Họ và tên khách mới</label>
                    <input type="text" id="rent-name" name="full_name" maxlength="255" class="{{ $inp }}">
                </div>
                <div>
                    <label for="rent-phone" class="{{ $lbl }}">Số điện thoại</label>
                    <input type="text" id="rent-phone" name="phone" inputmode="numeric" class="{{ $inp }}">
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="rent-start" class="{{ $lbl }}">Ngày vào ở</label>
                    <input type="date" id="rent-start" name="start_date" required value="{{ now()->toDateString() }}" class="{{ $inp }}">
                </div>
                <div>
                    <label for="rent-end" class="{{ $lbl }}">Hết hạn dự kiến</label>
                    <input type="date" id="rent-end" name="end_date" class="{{ $inp }}">
                </div>
            </div>
            <label class="flex items-start gap-3 text-sm font-medium text-slate-600 min-h-[44px]">
                <input type="checkbox" name="issue_account" value="1" checked class="mt-1 w-5 h-5 rounded">
                <span class="break-words min-w-0">Cấp tài khoản cho chủ hộ (cần số điện thoại, hệ thống sẽ hiện mật khẩu tạm một lần)</span>
            </label>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="window.closeOverlay('rent-modal')" class="min-h-[44px] px-6 bg-slate-100 text-slate-600 rounded-xl text-sm font-bold hover:bg-slate-200 transition-all">Đóng</button>
                <button type="submit" class="min-h-[44px] px-6 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-md hover:bg-indigo-700 transition-all">Cho thuê</button>
            </div>
        </form>
    </div>

    {{-- Modal thêm người ở cùng --}}
    <div id="member-modal" data-close-fn="closeActiveModal" onclick="if (event.target === this) window.closeOverlay('member-modal')"
         class="hidden fixed inset-0 z-50 bg-slate-900/40 opacity-0 transition-opacity duration-300 flex items-center justify-center p-4">
        <form id="member-form" method="POST" data-panel onsubmit="window.showGlobalLoading()"
              class="bg-white rounded-2xl w-full max-w-lg max-h-[90dvh] overflow-y-auto p-5 md:p-6 space-y-4 scale-95 transition-transform duration-300">
            @csrf
            <h3 class="text-lg font-black text-slate-800">Thêm người ở cùng</h3>
            <div>
                <label for="member-tenant" class="{{ $lbl }}">Chọn khách có sẵn</label>
                <select id="member-tenant" name="tenant_id" class="{{ $inp }}">
                    <option value="">— Tạo người mới —</option>
                    @foreach($availableTenants as $t)
                        <option value="{{ $t->id }}">{{ $t->full_name }}{{ $t->phone ? ' · '.$t->phone : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="member-name" class="{{ $lbl }}">Họ và tên người mới</label>
                    <input type="text" id="member-name" name="full_name" maxlength="255" class="{{ $inp }}">
                </div>
                <div>
                    <label for="member-phone" class="{{ $lbl }}">Số điện thoại</label>
                    <input type="text" id="member-phone" name="phone" inputmode="numeric" class="{{ $inp }}">
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="window.closeOverlay('member-modal')" class="min-h-[44px] px-6 bg-slate-100 text-slate-600 rounded-xl text-sm font-bold hover:bg-slate-200 transition-all">Đóng</button>
                <button type="submit" class="min-h-[44px] px-6 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-md hover:bg-indigo-700 transition-all">Thêm</button>
            </div>
        </form>
    </div>

    {{-- Modal thay đồng hồ --}}
    <div id="meter-modal" data-close-fn="closeActiveModal" onclick="if (event.target === this) window.closeOverlay('meter-modal')"
         class="hidden fixed inset-0 z-50 bg-slate-900/40 opacity-0 transition-opacity duration-300 flex items-center justify-center p-4">
        <form id="meter-form" method="POST" data-panel onsubmit="window.showGlobalLoading()"
              class="bg-white rounded-2xl w-full max-w-md max-h-[90dvh] overflow-y-auto p-5 md:p-6 space-y-4 scale-95 transition-transform duration-300">
            @csrf
            <input type="hidden" name="type" id="meter-type">
            <h3 id="meter-modal-title" class="text-lg font-black text-slate-800">Thay đồng hồ</h3>
            <p class="text-xs text-slate-500 font-medium">Đồng hồ cũ được đóng lại theo ngày thay; đồng hồ mới bắt đầu từ chỉ số đầu bạn nhập.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="meter-int" class="{{ $lbl }}">Chữ số nguyên</label>
                    <input type="number" id="meter-int" name="integer_digits" required min="1" max="10" class="{{ $inp }}">
                </div>
                <div>
                    <label for="meter-dec" class="{{ $lbl }}">Chữ số thập phân</label>
                    <input type="number" id="meter-dec" name="decimal_digits" required min="0" max="3" class="{{ $inp }}">
                </div>
                <div>
                    <label for="meter-init" class="{{ $lbl }}">Chỉ số đầu</label>
                    <input type="number" step="any" id="meter-init" name="initial_reading" required min="0" value="0" class="{{ $inp }}">
                </div>
                <div>
                    <label for="meter-date" class="{{ $lbl }}">Ngày thay</label>
                    <input type="date" id="meter-date" name="replaced_at" required max="{{ now()->toDateString() }}" value="{{ now()->toDateString() }}" class="{{ $inp }}">
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="window.closeOverlay('meter-modal')" class="min-h-[44px] px-6 bg-slate-100 text-slate-600 rounded-xl text-sm font-bold hover:bg-slate-200 transition-all">Đóng</button>
                <button type="submit" class="min-h-[44px] px-6 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-md hover:bg-indigo-700 transition-all">Thay đồng hồ</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    const rooms = @json($roomData);
    const roomUrls = {
        update: @js(route('manage.rooms.update', ['room' => '__ID__'])),
        status: @js(route('manage.rooms.status', ['room' => '__ID__'])),
        destroy: @js(route('manage.rooms.destroy', ['room' => '__ID__'])),
        meter: @js(route('manage.rooms.meters.replace', ['room' => '__ID__'])),
        rent: @js(route('manage.rooms.rent', ['room' => '__ID__'])),
        addMember: @js(route('manage.contracts.members.store', ['contract' => '__ID__'])),
        checkout: @js(route('manage.contracts.checkout', ['contract' => '__ID__'])),
        leave: @js(route('manage.members.leave', ['link' => '__ID__'])),
        account: @js(route('manage.tenants.account', ['tenant' => '__ID__']))
    };
    let currentRoomId = null;

    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, c => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c]));
    const fill = (id, value) => { document.getElementById(id).value = value ?? ''; };

    window.openRoomDrawer = function (id) {
        const r = rooms[id];
        currentRoomId = id;

        document.getElementById('room-drawer-title').textContent = 'Phòng ' + r.name;
        document.getElementById('room-drawer-status').textContent = r.status_label;
        document.getElementById('room-household').innerHTML = r.has_contract
            ? '<p class="font-bold text-slate-800 break-words">Chủ hộ: ' + esc(r.owner_name ?? '—') + '</p><p class="text-xs text-slate-500 mt-1">' + r.residents + '/' + r.max_occupants + ' người đang ở</p>'
            : '<p class="font-bold text-slate-600">Chưa có hộ thuê</p>';

        document.getElementById('room-edit-form').action = roomUrls.update.replace('__ID__', id);
        fill('room-name', r.name); fill('room-floor', r.floor); fill('room-area', r.area);
        fill('room-max', r.max_occupants); fill('room-rent', r.base_rent); fill('room-note', r.note);

        document.getElementById('room-meters').innerHTML = r.meters.map(m =>
            '<div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 px-4 py-3">'
            + '<div class="min-w-0"><p class="text-sm font-bold text-slate-800">' + esc(m.label) + '</p>'
            + '<p class="text-xs text-slate-500 break-words">' + m.integer_digits + ' số nguyên · ' + m.decimal_digits + ' số lẻ · đầu kỳ ' + esc(Number(m.initial_reading)) + ' · từ ' + esc(m.installed_at) + '</p></div>'
            + '<button type="button" onclick="window.openMeterModal(\'' + m.type + '\')" class="min-h-[44px] px-3 shrink-0 text-xs font-bold text-indigo-600 hover:bg-indigo-50 rounded-xl">Thay</button></div>'
        ).join('');

        const btn0 = 'min-h-[44px] px-4 rounded-xl text-sm font-bold transition-all ';
        document.getElementById('room-links').innerHTML = r.links.map(l =>
            '<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 rounded-xl border border-slate-200 px-4 py-3">'
            + '<div class="min-w-0"><p class="text-sm font-bold text-slate-800 break-words">' + esc(l.name) + (l.is_primary ? ' <span class="text-[10px] text-indigo-600 uppercase">Chủ hộ</span>' : '') + '</p>'
            + '<p class="text-xs text-slate-500 break-words">' + esc(l.phone ?? 'Chưa có SĐT') + ' · ' + esc(l.approval_label) + (l.has_account ? ' · đã có tài khoản' : '') + '</p></div>'
            + '<div class="flex gap-2 shrink-0">'
            + (l.approval === 'approved' && !l.has_account && l.phone ? '<button type="button" onclick="window.issueAccount(' + l.tenant_id + ')" class="' + btn0 + 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100">Cấp TK</button>' : '')
            + (!l.is_primary ? '<button type="button" onclick="window.leaveRoom(' + l.id + ')" class="' + btn0 + 'bg-slate-100 text-slate-600 hover:bg-slate-200">Rời đi</button>' : '')
            + '</div></div>'
        ).join('');

        document.getElementById('room-rent-actions').innerHTML = !r.has_contract && r.status === 'vacant'
            ? '<button type="button" onclick="window.openRentModal()" class="flex-1 ' + btn0 + 'bg-indigo-600 text-white shadow-md hover:bg-indigo-700">Cho thuê phòng</button>'
            : (r.has_contract
                ? '<button type="button" onclick="window.openMemberModal()" class="flex-1 ' + btn0 + 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100">Thêm người ở cùng</button>'
                  + '<button type="button" onclick="window.checkoutRoom()" class="flex-1 ' + btn0 + 'bg-amber-50 text-amber-700 hover:bg-amber-100">Trả phòng</button>'
                : '');

        const actions = document.getElementById('room-manual-actions');
        const btn = 'min-h-[44px] px-4 rounded-xl text-sm font-bold transition-all ';
        let html = '';
        if (!r.has_contract) {
            html += r.status === 'maintenance'
                ? '<button type="button" onclick="window.setRoomStatus(\'vacant\')" class="' + btn + 'bg-green-50 text-green-700 hover:bg-green-100">Đánh dấu phòng trống</button>'
                : '<button type="button" onclick="window.setRoomStatus(\'maintenance\')" class="' + btn + 'bg-amber-50 text-amber-700 hover:bg-amber-100">Đánh dấu đang sửa</button>';
        }
        html += '<button type="button" onclick="window.deleteRoom()" class="' + btn + 'bg-rose-50 text-rose-600 hover:bg-rose-100">Xóa phòng</button>';
        actions.innerHTML = html;

        window.openOverlay('room-drawer');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    };

    window.setRoomStatus = function (status) {
        const form = document.getElementById('room-status-form');
        form.action = roomUrls.status.replace('__ID__', currentRoomId);
        form.querySelector('[name="status"]').value = status;
        window.confirmSubmit('room-status-form', { title: status === 'vacant' ? 'Đánh dấu phòng trống?' : 'Đánh dấu phòng đang sửa?', html: '<p class="text-sm text-slate-600">Phòng ' + esc(rooms[currentRoomId].name) + '</p>' });
    };

    window.deleteRoom = function () {
        document.getElementById('room-delete-form').action = roomUrls.destroy.replace('__ID__', currentRoomId);
        window.confirmSubmit('room-delete-form', { title: 'Xóa phòng?', html: '<p class="text-sm text-slate-600 break-words">Phòng ' + esc(rooms[currentRoomId].name) + ' chỉ xóa được khi chưa từng có hợp đồng.</p>', confirmText: 'Xóa phòng', danger: true });
    };

    window.openMeterModal = function (type) {
        const m = rooms[currentRoomId].meters.find(x => x.type === type);
        document.getElementById('meter-form').action = roomUrls.meter.replace('__ID__', currentRoomId);
        document.getElementById('meter-type').value = type;
        document.getElementById('meter-modal-title').textContent = 'Thay đồng hồ ' + m.label.toLowerCase();
        fill('meter-int', m.integer_digits); fill('meter-dec', m.decimal_digits); fill('meter-init', 0);
        window.openOverlay('meter-modal');
    };

    window.toggleNewTenant = function () {
        document.getElementById('rent-new-tenant').classList.toggle('hidden', !!document.getElementById('rent-tenant').value);
    };

    window.openRentModal = function () {
        document.getElementById('rent-form').action = roomUrls.rent.replace('__ID__', currentRoomId);
        document.getElementById('rent-title').textContent = 'Cho thuê phòng ' + rooms[currentRoomId].name;
        window.toggleNewTenant();
        window.openOverlay('rent-modal');
    };

    window.openMemberModal = function () {
        document.getElementById('member-form').action = roomUrls.addMember.replace('__ID__', rooms[currentRoomId].contract_id);
        window.openOverlay('member-modal');
    };

    window.leaveRoom = function (linkId) {
        document.getElementById('link-leave-form').action = roomUrls.leave.replace('__ID__', linkId);
        window.confirmSubmit('link-leave-form', { title: 'Người này rời phòng?', html: '<p class="text-sm text-slate-600 break-words">Tài khoản của họ (nếu có) sẽ bị khóa.</p>', confirmText: 'Xác nhận rời đi', danger: true });
    };

    window.issueAccount = function (tenantId) {
        const name = rooms[currentRoomId].links.find(l => l.tenant_id === tenantId)?.name;
        document.getElementById('account-form').action = roomUrls.account.replace('__ID__', tenantId);
        window.confirmSubmit('account-form', { title: 'Cấp tài khoản?', html: '<p class="text-sm text-slate-600 break-words">Cấp tài khoản cho <b>' + esc(name) + '</b>. Mật khẩu tạm chỉ hiện một lần.</p>' });
    };

    window.checkoutRoom = function () {
        const today = new Date().toISOString().slice(0, 10);
        document.getElementById('checkout-form').action = roomUrls.checkout.replace('__ID__', rooms[currentRoomId].contract_id);
        document.getElementById('checkout-form').querySelector('[name="ended_at"]').value = today;
        window.confirmSubmit('checkout-form', { title: 'Trả phòng ' + rooms[currentRoomId].name + '?', html: '<p class="text-sm text-slate-600 break-words">Hợp đồng kết thúc hôm nay, phòng chuyển về trống. Tài khoản của hộ vẫn mở để xem công nợ.</p>', confirmText: 'Trả phòng', danger: true });
    };
</script>
@endpush
