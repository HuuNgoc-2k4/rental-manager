@extends('layouts.app')

@section('title', $property->name)
@section('subtitle', $property->address)

@php
    $breadcrumbs = [
        ['name' => 'Khu trọ', 'url' => route('manage.properties.index')],
        ['name' => $property->name, 'url' => '#']
    ];
    // Giao diện theo trạng thái phòng: [viền ô, dải màu đầu ô, ô icon, icon]
    $statusTheme = [
        'vacant' => ['hover:border-emerald-300', 'bg-emerald-400', 'bg-emerald-50 text-emerald-600', 'door-open'],
        'occupied' => ['hover:border-indigo-300', 'bg-indigo-500', 'bg-indigo-50 text-indigo-600', 'users'],
        'maintenance' => ['hover:border-amber-300', 'bg-amber-400', 'bg-amber-50 text-amber-600', 'wrench']
    ];
    $roomData = $rooms->mapWithKeys(function ($room) use ($property) {
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
            'status_color' => \App\Enums\Property\RoomStatus::tryFrom($room->status)?->color(),
            'property_name' => $property->name,
            'has_contract' => (bool) $contract,
            'contract_id' => $contract?->id,
            'start_date' => $contract?->start_date?->format('d/m/Y'),
            'links' => ($contract?->liveLinks ?? collect())->map(fn ($l) => [
                'id' => $l->id,
                'tenant_id' => $l->tenant_id,
                'name' => $l->tenant?->full_name,
                'phone' => $l->tenant?->phone,
                'is_primary' => $l->is_primary,
                'approval' => $l->approval_status,
                'approval_label' => \App\Enums\Contract\MemberApproval::tryFrom($l->approval_status)?->label(),
                'approval_color' => \App\Enums\Contract\MemberApproval::tryFrom($l->approval_status)?->color(),
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
    $statusCounts = $rooms->countBy('status');
@endphp

@section('page_actions')
    <button type="button" onclick="window.openOverlay('room-create-modal')" class="btn btn-primary flex-1 sm:flex-none">
        <i data-lucide="plus" class="w-4 h-4"></i> Thêm phòng
    </button>
    <form id="property-delete-form" method="POST" action="{{ route('manage.properties.destroy', $property) }}" class="hidden">@csrf @method('DELETE')</form>
    <button type="button" aria-label="Xóa khu"
            onclick="window.confirmSubmit('property-delete-form', { title: 'Xóa khu trọ?', html: '<p class=&quot;text-sm text-slate-600 break-words&quot;>Chỉ xóa được khi khu chưa có phòng nào.</p>', confirmText: 'Xóa khu', danger: true })"
            class="btn-icon bg-white border-2 border-slate-100 text-slate-400 hover:text-rose-500 hover:border-rose-100">
        <i data-lucide="trash-2" class="w-4 h-4"></i>
    </button>
@endsection

@section('content')
    {{-- Bộ lọc theo trạng thái: bấm thẻ để chỉ hiện phòng trạng thái đó --}}
    <div class="grid grid-cols-4 gap-2 sm:gap-3 mb-6">
        <button type="button" data-room-filter="" onclick="window.filterRooms('')"
                class="room-filter card p-2.5 sm:p-4 text-center lg:text-left min-h-[44px] transition-all ring-2 ring-indigo-500">
            <p class="section-label text-[10px] sm:text-[11px] tracking-wide lg:tracking-widest">Tất cả</p>
            <p class="text-xl sm:text-2xl font-bold text-slate-900 tabular-nums mt-1">{{ $rooms->count() }}</p>
        </button>
        @foreach($statuses as $st)
            <button type="button" data-room-filter="{{ $st->value }}" onclick="window.filterRooms('{{ $st->value }}')"
                    class="room-filter card p-2.5 sm:p-4 text-center lg:text-left min-h-[44px] transition-all hover:border-slate-300">
                <div class="flex items-center justify-center lg:justify-between gap-2">
                    <p class="section-label text-[10px] sm:text-[11px] tracking-wide lg:tracking-widest break-words min-w-0">{{ $st->label() }}</p>
                    <span class="hidden lg:flex w-8 h-8 rounded-lg items-center justify-center shrink-0 {{ $statusTheme[$st->value][2] }}">
                        <i data-lucide="{{ $statusTheme[$st->value][3] }}" class="w-4 h-4"></i>
                    </span>
                </div>
                <p class="text-xl sm:text-2xl font-bold text-slate-900 tabular-nums mt-1">{{ $statusCounts[$st->value] ?? 0 }}</p>
            </button>
        @endforeach
    </div>

    @forelse($floors as $floor => $floorRooms)
        <section class="mb-8" data-floor>
            <div class="flex items-center gap-3 mb-3">
                <span class="w-8 h-8 rounded-lg bg-slate-900 text-white flex items-center justify-center text-xs font-bold shrink-0">
                    {{ $floor === 'none' ? '–' : $floor }}
                </span>
                <p class="text-sm font-bold text-slate-800">{{ $floor === 'none' ? 'Chưa phân tầng' : 'Tầng '.$floor }}</p>
                <span class="text-xs font-bold text-slate-400">{{ $floorRooms->count() }} phòng</span>
                <span class="flex-1 h-px bg-slate-200"></span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
                @foreach($floorRooms as $room)
                    @php
                        $data = $roomData[$room->id];
                        $theme = $statusTheme[$room->status] ?? $statusTheme['vacant'];
                    @endphp
                    <button type="button" onclick="window.openRoomDrawer({{ $room->id }})" data-room-status="{{ $room->status }}"
                            class="group relative text-left card overflow-hidden flex flex-col hover:shadow-md transition-all {{ $theme[0] }}">
                        <span class="h-1.5 w-full {{ $theme[1] }}"></span>
                        <span class="p-3 sm:p-4 flex flex-col gap-2 flex-1">
                            <span class="flex items-start justify-between gap-2">
                                <span class="text-xl font-bold text-slate-900 break-words min-w-0 leading-tight">{{ $room->name }}</span>
                                <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 {{ $theme[2] }}">
                                    <i data-lucide="{{ $theme[3] }}" class="w-4 h-4"></i>
                                </span>
                            </span>
                            <span class="text-xs font-bold break-words min-w-0 {{ $data['owner_name'] ? 'text-slate-700' : 'text-slate-400' }}">
                                {{ $data['owner_name'] ?? $data['status_label'] }}
                            </span>
                            <span class="mt-auto flex flex-wrap items-center justify-between gap-x-2 gap-y-1 pt-2 border-t border-slate-100">
                                <span class="flex items-center gap-1 text-[11px] font-bold text-slate-500 whitespace-nowrap">
                                    <i data-lucide="user" class="w-3.5 h-3.5"></i>{{ $data['residents'] }}/{{ $room->max_occupants }}
                                </span>
                                <span class="text-[11px] font-bold text-slate-800 whitespace-nowrap tabular-nums">{{ number_format($room->base_rent, 0, ',', '.') }}đ</span>
                            </span>
                        </span>
                    </button>
                @endforeach
            </div>
        </section>
    @empty
        <div class="card p-10 text-center">
            <div class="w-16 h-16 rounded-3xl bg-indigo-50 flex items-center justify-center mx-auto mb-4">
                <i data-lucide="door-closed" class="w-8 h-8 text-indigo-500"></i>
            </div>
            <p class="text-base font-bold text-slate-800">Khu này chưa có phòng</p>
            <p class="text-sm text-slate-500 font-medium mt-1">Bấm "Thêm phòng" để khai báo phòng và đồng hồ điện nước.</p>
            <button type="button" onclick="window.openOverlay('room-create-modal')" class="btn btn-primary mt-6"><i data-lucide="plus" class="w-4 h-4"></i> Thêm phòng</button>
        </div>
    @endforelse

    <div id="room-filter-empty" class="hidden card p-8 text-center text-sm font-semibold text-slate-400">Không có phòng nào ở trạng thái này.</div>

    {{-- Forms ẩn: đặt NGOÀI drawer để không lồng form trong form --}}
    <form id="room-status-form" method="POST" class="hidden">@csrf @method('PATCH')<input type="hidden" name="status"></form>
    <form id="room-delete-form" method="POST" class="hidden">@csrf @method('DELETE')</form>
    <form id="link-leave-form" method="POST" class="hidden">@csrf @method('DELETE')</form>
    <form id="account-form" method="POST" class="hidden">@csrf</form>
    <form id="checkout-form" method="POST" class="hidden">@csrf<input type="hidden" name="ended_at"></form>

    {{-- Drawer phòng --}}
    <div id="room-drawer" data-drawer-overlay data-close-fn="closeActiveDrawer" onclick="if (event.target === this) window.closeOverlay('room-drawer')"
         class="hidden fixed inset-0 z-[100] bg-slate-900/40 backdrop-blur-sm opacity-0 transition-opacity duration-300">
        <div data-panel class="absolute right-0 top-0 h-full w-full lg:w-[52vw] xl:w-[44vw] bg-slate-50 shadow-2xl z-[101] translate-x-full transition-transform duration-300 ease-in-out overflow-y-auto">
            <div class="px-5 sm:px-6 py-4 border-b border-slate-200 flex items-center gap-3 sticky top-0 bg-white z-10">
                <span id="room-drawer-icon" class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0"></span>
                <div class="min-w-0 flex-1">
                    <p id="room-drawer-status" class="text-[11px] font-bold text-slate-400 uppercase tracking-widest"></p>
                    <h3 id="room-drawer-title" class="text-lg font-bold text-slate-900 break-words min-w-0"></h3>
                </div>
                <button type="button" onclick="window.closeOverlay('room-drawer')" aria-label="Đóng" class="btn-icon -mr-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="p-4 sm:p-6 space-y-4 pb-safe">
                {{-- Hộ đang thuê --}}
                <div class="card p-4 sm:p-5 space-y-4">
                    <div class="flex items-center gap-2">
                        <i data-lucide="home" class="w-4 h-4 text-indigo-500"></i>
                        <p class="section-label">Hộ đang thuê</p>
                    </div>
                    <div id="room-household"></div>
                    <div id="room-links" class="space-y-2"></div>
                    <div id="room-rent-actions" class="grid grid-cols-1 sm:grid-cols-2 gap-2"></div>
                </div>

                {{-- Thông tin phòng --}}
                <form id="room-edit-form" method="POST" onsubmit="window.showGlobalLoading()" class="card p-4 sm:p-5 space-y-4">
                    @csrf @method('PUT')
                    <div class="flex items-center gap-2">
                        <i data-lucide="settings-2" class="w-4 h-4 text-indigo-500"></i>
                        <p class="section-label">Thông tin phòng</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <label for="room-name" class="form-label">Tên / số phòng</label>
                            <input type="text" id="room-name" name="name" required maxlength="50" class="form-input">
                        </div>
                        <div>
                            <label for="room-floor" class="form-label">Tầng</label>
                            <input type="number" id="room-floor" name="floor" class="form-input">
                        </div>
                        <div>
                            <label for="room-area" class="form-label">Diện tích (m²)</label>
                            <input type="number" step="0.01" id="room-area" name="area" class="form-input">
                        </div>
                        <div>
                            <label for="room-max" class="form-label">Số người tối đa</label>
                            <input type="number" id="room-max" name="max_occupants" required min="1" class="form-input">
                        </div>
                    </div>
                    <div>
                        <label for="room-rent" class="form-label">Giá thuê mặc định (đ/tháng)</label>
                        <input type="number" id="room-rent" name="base_rent" required min="0" class="form-input">
                    </div>
                    <div>
                        <label for="room-note" class="form-label">Ghi chú</label>
                        <textarea id="room-note" name="note" rows="2" maxlength="2000" class="form-input"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-full"><i data-lucide="save" class="w-4 h-4"></i> Lưu phòng</button>
                </form>

                {{-- Đồng hồ --}}
                <div class="card p-4 sm:p-5 space-y-3">
                    <div class="flex items-center gap-2">
                        <i data-lucide="gauge" class="w-4 h-4 text-indigo-500"></i>
                        <p class="section-label">Đồng hồ đang dùng</p>
                    </div>
                    <div id="room-meters" class="grid grid-cols-1 sm:grid-cols-2 gap-3"></div>
                </div>

                {{-- Thao tác khác --}}
                <div class="card p-4 sm:p-5 space-y-3">
                    <div class="flex items-center gap-2">
                        <i data-lucide="sliders-horizontal" class="w-4 h-4 text-indigo-500"></i>
                        <p class="section-label">Thao tác khác</p>
                    </div>
                    <div id="room-manual-actions" class="grid grid-cols-1 sm:grid-cols-2 gap-2"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal thêm phòng --}}
    <div id="room-create-modal" data-close-fn="closeActiveModal" onclick="if (event.target === this) window.closeOverlay('room-create-modal')"
         class="hidden fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm opacity-0 transition-opacity duration-300 flex items-end sm:items-center justify-center sm:p-4">
        <form method="POST" action="{{ route('manage.properties.rooms.store', $property) }}" data-panel onsubmit="window.showGlobalLoading()"
              class="bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-xl max-h-[92dvh] overflow-y-auto scale-95 transition-transform duration-300 pb-safe">
            @csrf
            @include('partials.modal-header', ['modalId' => 'room-create-modal', 'icon' => 'door-closed', 'title' => 'Thêm phòng'])
            <div class="p-5 sm:p-6 space-y-4">
                <div class="grid grid-cols-2 gap-3 sm:gap-4">
                    <div>
                        <label for="new-name" class="form-label">Tên / số phòng</label>
                        <input type="text" id="new-name" name="name" required maxlength="50" value="{{ old('name') }}" placeholder="VD: 101" class="form-input">
                    </div>
                    <div>
                        <label for="new-floor" class="form-label">Tầng</label>
                        <input type="number" id="new-floor" name="floor" value="{{ old('floor') }}" class="form-input">
                    </div>
                    <div>
                        <label for="new-area" class="form-label">Diện tích (m²)</label>
                        <input type="number" step="0.01" id="new-area" name="area" value="{{ old('area') }}" class="form-input">
                    </div>
                    <div>
                        <label for="new-max" class="form-label">Số người tối đa</label>
                        <input type="number" id="new-max" name="max_occupants" required min="1" value="{{ old('max_occupants', 2) }}" class="form-input">
                    </div>
                </div>
                <div>
                    <label for="new-rent" class="form-label">Giá thuê mặc định (đ/tháng)</label>
                    <input type="number" id="new-rent" name="base_rent" required min="0" value="{{ old('base_rent') }}" class="form-input">
                </div>

                @foreach(\App\Enums\Meter\MeterType::cases() as $type)
                    <fieldset class="rounded-2xl border-2 border-slate-100 p-4">
                        <legend class="px-2 flex items-center gap-1.5 text-[11px] font-bold text-slate-500 uppercase tracking-widest">
                            <i data-lucide="{{ $type === \App\Enums\Meter\MeterType::Electric ? 'zap' : 'droplet' }}" class="w-3.5 h-3.5 {{ $type === \App\Enums\Meter\MeterType::Electric ? 'text-amber-500' : 'text-sky-500' }}"></i>
                            Đồng hồ {{ $type->label() }}
                        </legend>
                        <div class="grid grid-cols-3 gap-2 sm:gap-3">
                            <div>
                                <label for="new-{{ $type->value }}-int" class="form-label">Số nguyên</label>
                                <input type="number" id="new-{{ $type->value }}-int" name="{{ $type->value }}_integer_digits" required min="1" max="10" value="{{ old($type->value.'_integer_digits', 5) }}" class="form-input">
                            </div>
                            <div>
                                <label for="new-{{ $type->value }}-dec" class="form-label">Số lẻ</label>
                                <input type="number" id="new-{{ $type->value }}-dec" name="{{ $type->value }}_decimal_digits" required min="0" max="3" value="{{ old($type->value.'_decimal_digits', 0) }}" class="form-input">
                            </div>
                            <div>
                                <label for="new-{{ $type->value }}-init" class="form-label">Chỉ số đầu</label>
                                <input type="number" step="any" id="new-{{ $type->value }}-init" name="{{ $type->value }}_initial_reading" required min="0" value="{{ old($type->value.'_initial_reading', 0) }}" class="form-input">
                            </div>
                        </div>
                    </fieldset>
                @endforeach
            </div>
            <div class="px-5 sm:px-6 py-4 border-t border-slate-100 grid grid-cols-2 sm:flex sm:justify-end gap-3">
                <button type="button" onclick="window.closeOverlay('room-create-modal')" class="btn btn-secondary">Đóng</button>
                <button type="submit" class="btn btn-primary"><i data-lucide="plus" class="w-4 h-4"></i> Thêm phòng</button>
            </div>
        </form>
    </div>

    {{-- Modal cho thuê phòng --}}
    <div id="rent-modal" data-close-fn="closeActiveModal" onclick="if (event.target === this) window.closeOverlay('rent-modal')"
         class="hidden fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm opacity-0 transition-opacity duration-300 flex items-end sm:items-center justify-center sm:p-4">
        <form id="rent-form" method="POST" data-panel onsubmit="window.showGlobalLoading()"
              class="bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-lg max-h-[92dvh] overflow-y-auto scale-95 transition-transform duration-300 pb-safe">
            @csrf
            @include('partials.modal-header', ['modalId' => 'rent-modal', 'icon' => 'key-square', 'title' => 'Cho thuê phòng', 'titleId' => 'rent-title'])
            <div class="p-5 sm:p-6 space-y-4">
                <div>
                    <label for="rent-tenant" class="form-label">Chủ hộ</label>
                    <select id="rent-tenant" name="tenant_id" onchange="window.toggleNewTenant()" class="form-input">
                        <option value="">— Tạo khách mới —</option>
                        @foreach($availableTenants as $t)
                            <option value="{{ $t->id }}">{{ $t->full_name }}{{ $t->phone ? ' · '.$t->phone : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="rent-new-tenant" class="grid grid-cols-1 sm:grid-cols-2 gap-4 rounded-2xl bg-slate-50 p-4">
                    <div>
                        <label for="rent-name" class="form-label">Họ và tên khách mới</label>
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

    {{-- Modal thêm người ở cùng --}}
    <div id="member-modal" data-close-fn="closeActiveModal" onclick="if (event.target === this) window.closeOverlay('member-modal')"
         class="hidden fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm opacity-0 transition-opacity duration-300 flex items-end sm:items-center justify-center sm:p-4">
        <form id="member-form" method="POST" data-panel onsubmit="window.showGlobalLoading()"
              class="bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-lg max-h-[92dvh] overflow-y-auto scale-95 transition-transform duration-300 pb-safe">
            @csrf
            @include('partials.modal-header', ['modalId' => 'member-modal', 'icon' => 'user-plus', 'title' => 'Thêm người ở cùng'])
            <div class="p-5 sm:p-6 space-y-4">
                <div>
                    <label for="member-tenant" class="form-label">Chọn khách có sẵn</label>
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

    {{-- Modal thay đồng hồ --}}
    <div id="meter-modal" data-close-fn="closeActiveModal" onclick="if (event.target === this) window.closeOverlay('meter-modal')"
         class="hidden fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm opacity-0 transition-opacity duration-300 flex items-end sm:items-center justify-center sm:p-4">
        <form id="meter-form" method="POST" data-panel onsubmit="window.showGlobalLoading()"
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
    const statusTheme = @json(collect($statusTheme)->map(fn ($t) => ['tone' => $t[2], 'icon' => $t[3]]));
    const meterTheme = { electric: ['zap', 'bg-amber-50 text-amber-600'], water: ['droplet', 'bg-sky-50 text-sky-600'] };
    let currentRoomId = null;

    const esc = window.escapeHtml;
    const fill = (id, value) => { document.getElementById(id).value = value ?? ''; };
    const icon = (name, cls = 'w-4 h-4') => '<i data-lucide="' + name + '" class="' + cls + '"></i>';
    const actionBtn = (onclick, cls, iconName, label) => '<button type="button" onclick="' + onclick + '" class="btn ' + cls + '">' + icon(iconName) + label + '</button>';

    /** Lọc ô phòng theo trạng thái; ẩn cả tầng không còn phòng nào khớp. */
    window.filterRooms = function (status) {
        document.querySelectorAll('.room-filter').forEach(btn => {
            const active = btn.dataset.roomFilter === status;
            btn.classList.toggle('ring-2', active);
            btn.classList.toggle('ring-indigo-500', active);
        });
        let visibleTotal = 0;
        document.querySelectorAll('[data-floor]').forEach(section => {
            let visible = 0;
            section.querySelectorAll('[data-room-status]').forEach(tile => {
                const show = !status || tile.dataset.roomStatus === status;
                tile.classList.toggle('hidden', !show);
                if (show) visible++;
            });
            section.classList.toggle('hidden', visible === 0);
            visibleTotal += visible;
        });
        document.getElementById('room-filter-empty')?.classList.toggle('hidden', visibleTotal > 0 || !document.querySelector('[data-floor]'));
    };

    window.openRoomDrawer = function (id) {
        const r = rooms[id];
        const theme = statusTheme[r.status] ?? statusTheme.vacant;
        currentRoomId = id;

        const drawerIcon = document.getElementById('room-drawer-icon');
        drawerIcon.className = 'w-11 h-11 rounded-xl flex items-center justify-center shrink-0 ' + theme.tone;
        drawerIcon.innerHTML = icon(theme.icon, 'w-5 h-5');
        document.getElementById('room-drawer-title').textContent = 'Phòng ' + r.name;
        document.getElementById('room-drawer-status').textContent = r.status_label + ' · ' + r.property_name + (r.floor ? ' · Tầng ' + r.floor : '');

        // Hộ đang thuê: chủ hộ + thanh số người / tối đa
        const percent = Math.min(100, Math.round(r.residents * 100 / Math.max(r.max_occupants, 1)));
        document.getElementById('room-household').innerHTML = r.has_contract
            ? '<div class="flex items-center justify-between gap-3">'
              + '<div class="min-w-0"><p class="text-base font-bold text-slate-900 break-words">' + esc(r.owner_name ?? '—') + '</p>'
              + '<p class="text-xs text-slate-500 font-semibold mt-0.5">Chủ hộ · vào ở từ ' + esc(r.start_date) + '</p></div>'
              + '<p class="text-sm font-bold text-slate-900 whitespace-nowrap tabular-nums">' + r.residents + '<span class="text-slate-400">/' + r.max_occupants + ' người</span></p></div>'
              + '<div class="h-2 rounded-full bg-slate-100 mt-3 overflow-hidden"><div class="h-full rounded-full bg-indigo-500" style="width:' + percent + '%"></div></div>'
            : '<div class="flex items-center gap-3 rounded-xl bg-slate-50 p-4">' + icon(r.status === 'maintenance' ? 'wrench' : 'door-open', 'w-5 h-5 text-slate-400')
              + '<p class="text-sm font-bold text-slate-600">' + (r.status === 'maintenance' ? 'Phòng đang sửa, chưa cho thuê được' : 'Phòng đang trống') + '</p></div>';

        document.getElementById('room-links').innerHTML = r.links.map(l =>
            '<div class="flex flex-col sm:flex-row sm:items-center gap-3 rounded-xl border border-slate-200 bg-white p-3">'
            + '<div class="flex items-center gap-3 min-w-0 flex-1">' + window.avatarHtml(l.name, 'w-10 h-10 text-xs')
            + '<div class="min-w-0"><p class="text-sm font-bold text-slate-800 break-words">' + esc(l.name)
            + (l.is_primary ? ' <span class="badge ml-1 bg-indigo-50 text-indigo-700 ring-indigo-600/20">Chủ hộ</span>' : '') + '</p>'
            + '<div class="flex flex-wrap items-center gap-1.5 mt-1 text-[11px] font-semibold text-slate-500">'
            + '<span class="flex items-center gap-1">' + icon('phone', 'w-3 h-3') + esc(l.phone ?? 'Chưa có SĐT') + '</span>'
            + '<span class="badge ' + l.approval_color + '">' + esc(l.approval_label) + '</span>'
            + (l.has_account ? '<span class="badge bg-emerald-50 text-emerald-700 ring-emerald-600/20">' + icon('shield-check', 'w-3 h-3') + 'Có tài khoản</span>' : '')
            + '</div></div></div>'
            + '<div class="flex gap-2 shrink-0">'
            + (l.approval === 'approved' && !l.has_account && l.phone ? actionBtn('window.issueAccount(' + l.tenant_id + ')', 'btn-soft flex-1 px-4', 'key-round', 'Cấp TK') : '')
            + (!l.is_primary ? actionBtn('window.leaveRoom(' + l.id + ')', 'btn-secondary flex-1 px-4', 'log-out', 'Rời đi') : '')
            + '</div></div>'
        ).join('');

        document.getElementById('room-rent-actions').innerHTML = !r.has_contract && r.status === 'vacant'
            ? '<button type="button" onclick="window.openRentModal()" class="btn btn-primary sm:col-span-2">' + icon('key-square') + 'Cho thuê phòng</button>'
            : (r.has_contract
                ? actionBtn('window.openMemberModal()', 'btn-soft', 'user-plus', 'Thêm người ở cùng')
                  + actionBtn('window.checkoutRoom()', 'btn-warning-soft', 'door-open', 'Trả phòng')
                : '');

        document.getElementById('room-edit-form').action = roomUrls.update.replace('__ID__', id);
        fill('room-name', r.name); fill('room-floor', r.floor); fill('room-area', r.area);
        fill('room-max', r.max_occupants); fill('room-rent', r.base_rent); fill('room-note', r.note);

        document.getElementById('room-meters').innerHTML = r.meters.map(m => {
            const [meterIcon, meterTone] = meterTheme[m.type] ?? ['gauge', 'bg-slate-100 text-slate-600'];
            return '<div class="rounded-xl border border-slate-200 bg-white p-3 flex items-start gap-3">'
                + '<span class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 ' + meterTone + '">' + icon(meterIcon, 'w-5 h-5') + '</span>'
                + '<div class="min-w-0 flex-1"><p class="text-sm font-bold text-slate-800">' + esc(m.label) + '</p>'
                + '<p class="text-[11px] text-slate-500 font-semibold break-words mt-0.5">Đầu kỳ <b class="text-slate-700">' + esc(Number(m.initial_reading)) + '</b> · ' + m.integer_digits + '+' + m.decimal_digits + ' chữ số</p>'
                + '<p class="text-[11px] text-slate-400 font-semibold">Lắp từ ' + esc(m.installed_at) + '</p></div>'
                + '<button type="button" onclick="window.openMeterModal(\'' + m.type + '\')" class="btn-icon text-indigo-600 hover:bg-indigo-50" aria-label="Thay đồng hồ">' + icon('refresh-cw') + '</button></div>';
        }).join('');

        let manual = '';
        if (!r.has_contract) {
            manual += r.status === 'maintenance'
                ? actionBtn("window.setRoomStatus('vacant')", 'btn-soft', 'door-open', 'Đánh dấu phòng trống')
                : actionBtn("window.setRoomStatus('maintenance')", 'btn-warning-soft', 'wrench', 'Đánh dấu đang sửa');
        }
        manual += actionBtn('window.deleteRoom()', 'btn-danger-soft', 'trash-2', 'Xóa phòng');
        document.getElementById('room-manual-actions').innerHTML = manual;

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
