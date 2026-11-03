@extends('layouts.app')

@section('title', 'Phòng trọ')
@section('subtitle', 'Tất cả phòng theo khu. Bấm vào phòng để xem chi tiết, hộ đang thuê và hợp đồng.')

@php
    // Giao diện theo trạng thái phòng: [chấm màu, ô icon, icon, nhãn trạng thái]
    $statusTheme = [
        'vacant' => ['bg-emerald-400', 'bg-emerald-50 text-emerald-600', 'door-open', 'bg-emerald-50 text-emerald-700'],
        'occupied' => ['bg-indigo-500', 'bg-indigo-50 text-indigo-600', 'users', 'bg-indigo-50 text-indigo-700'],
        'maintenance' => ['bg-amber-400', 'bg-amber-50 text-amber-600', 'wrench', 'bg-amber-50 text-amber-700']
    ];
    // Mỗi phòng một dòng: dưới xl 3 cột (phòng | người thuê + thông tin | giá), từ xl tách thành 9 cột
    $rowGrid = 'grid-cols-[4.5rem_minmax(0,1fr)_auto] xl:grid-cols-[4.5rem_7rem_4.5rem_6rem_minmax(0,1fr)_4.5rem_6rem_6.5rem_6.5rem]';
    $furnitureTone = ['broken' => 'bg-rose-50 text-rose-700', 'worn' => 'bg-amber-50 text-amber-700'];
    $allRooms = $roomsByProperty->flatten();
    $statusCounts = $allRooms->countBy('status');
@endphp

@section('page_actions')
    <button type="button" onclick="window.openPropertyModal()" class="btn btn-primary w-full sm:w-auto">
        <i data-lucide="plus" class="w-4 h-4"></i> Thêm khu
    </button>
@endsection

@section('content')
    @if($properties->isEmpty())
        <div class="card p-10 text-center">
            <div class="w-16 h-16 rounded-3xl bg-indigo-50 flex items-center justify-center mx-auto mb-4">
                <i data-lucide="building-2" class="w-8 h-8 text-indigo-500"></i>
            </div>
            <p class="text-base font-bold text-slate-800">Chưa có khu trọ nào</p>
            <p class="text-sm text-slate-500 font-medium mt-1">Thêm khu (toà nhà) trước, rồi khai báo phòng trong khu.</p>
            <button type="button" onclick="window.openPropertyModal()" class="btn btn-primary mt-6"><i data-lucide="plus" class="w-4 h-4"></i> Thêm khu đầu tiên</button>
        </div>
    @else
        {{-- Lọc theo trạng thái: bấm thẻ để chỉ hiện phòng trạng thái đó --}}
        <div class="grid grid-cols-4 gap-2 sm:gap-3 mb-4">
            <button type="button" data-room-filter="" onclick="window.filterRooms({ status: '' })"
                    class="room-filter card p-2.5 sm:p-4 text-center lg:text-left min-h-[44px] transition-all ring-2 ring-indigo-500">
                <p class="section-label text-[10px] sm:text-[11px] tracking-wide lg:tracking-widest">Tất cả</p>
                <p class="text-xl sm:text-2xl font-bold text-slate-900 tabular-nums mt-1">{{ $allRooms->count() }}</p>
            </button>
            @foreach($statuses as $st)
                <button type="button" data-room-filter="{{ $st->value }}" onclick="window.filterRooms({ status: '{{ $st->value }}' })"
                        class="room-filter card p-2.5 sm:p-4 text-center lg:text-left min-h-[44px] transition-all hover:border-slate-300">
                    <div class="flex items-center justify-center lg:justify-between gap-2">
                        <p class="section-label text-[10px] sm:text-[11px] tracking-wide lg:tracking-widest break-words min-w-0">{{ $st->label() }}</p>
                        <span class="hidden lg:flex w-8 h-8 rounded-lg items-center justify-center shrink-0 {{ $statusTheme[$st->value][1] }}">
                            <i data-lucide="{{ $statusTheme[$st->value][2] }}" class="w-4 h-4"></i>
                        </span>
                    </div>
                    <p class="text-xl sm:text-2xl font-bold text-slate-900 tabular-nums mt-1">{{ $statusCounts[$st->value] ?? 0 }}</p>
                </button>
            @endforeach
        </div>

        {{-- Lọc theo khu — chỉ khi có từ 2 khu, cuộn ngang trên mobile --}}
        @if($properties->count() > 1)
            <div class="-mx-4 sm:mx-0 px-4 sm:px-0 overflow-x-auto mb-6">
                <div class="flex gap-2 w-max">
                    <button type="button" data-property-filter="" onclick="window.filterRooms({ property: '' })"
                            class="property-filter inline-flex items-center min-h-[40px] px-4 rounded-full text-xs font-bold whitespace-nowrap transition-all bg-slate-900 text-white">Tất cả khu</button>
                    @foreach($properties as $property)
                        <button type="button" data-property-filter="{{ $property->id }}" onclick="window.filterRooms({ property: '{{ $property->id }}' })"
                                class="property-filter inline-flex items-center min-h-[40px] px-4 rounded-full text-xs font-bold whitespace-nowrap transition-all bg-white border border-slate-200 text-slate-500 hover:bg-slate-100">{{ $property->name }}</button>
                    @endforeach
                </div>
            </div>
        @endif

        @foreach($properties as $property)
            @php
                $propertyRooms = $roomsByProperty->get($property->id, collect());
                $editData = ['id' => $property->id, 'name' => $property->name, 'address' => $property->address, 'note' => $property->note];
            @endphp
            <section id="property-{{ $property->id }}" class="card overflow-hidden mb-6 scroll-mt-4" data-property="{{ $property->id }}">
                {{-- Đầu khu: tên, địa chỉ, số phòng đang thuê + thao tác khu --}}
                <div class="p-4 flex flex-col sm:flex-row sm:items-center gap-3 border-b border-slate-100">
                    <div class="flex items-start gap-3 min-w-0 flex-1">
                        <span class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white flex items-center justify-center shrink-0">
                            <i data-lucide="building-2" class="w-5 h-5"></i>
                        </span>
                        <div class="min-w-0">
                            <p class="text-base font-bold text-slate-900 break-words">
                                {{ $property->name }}
                                <span class="ml-1 text-[11px] font-bold text-slate-400 whitespace-nowrap">{{ $property->occupied_rooms_count }}/{{ $property->rooms_count }} phòng đang thuê</span>
                            </p>
                            <p class="text-xs text-slate-500 font-medium mt-0.5 break-words flex items-start gap-1">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5 shrink-0 mt-px text-slate-400"></i>{{ $property->address }}
                            </p>
                        </div>
                    </div>
                    <div class="flex gap-2 shrink-0">
                        <button type="button" onclick="window.openRoomCreateModal({{ $property->id }}, @js($property->name))" class="btn btn-soft flex-1 sm:flex-none">
                            <i data-lucide="plus" class="w-4 h-4"></i> Thêm phòng
                        </button>
                        <button type="button" aria-label="Sửa khu" onclick="window.openPropertyModal(@js($editData))"
                                class="btn-icon bg-white border-2 border-slate-100 text-slate-400 hover:text-indigo-600 hover:border-indigo-100">
                            <i data-lucide="pencil" class="w-4 h-4"></i>
                        </button>
                        <button type="button" aria-label="Xóa khu" onclick="window.deleteProperty({{ $property->id }}, @js($property->name))"
                                class="btn-icon bg-white border-2 border-slate-100 text-slate-400 hover:text-rose-500 hover:border-rose-100">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                @if($propertyRooms->isNotEmpty())
                    {{-- Tên cột: chỉ từ xl, mobile đã có đủ thông tin ngay trong dòng --}}
                    <div class="hidden xl:grid {{ $rowGrid }} gap-x-2 px-4 py-2 bg-slate-50 border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                        <span>Phòng</span><span>Loại phòng</span><span>Diện tích</span><span>Trạng thái</span><span>Người thuê</span><span>Người ở</span><span>Hợp đồng</span><span>Nội thất</span><span class="text-right">Giá thuê</span>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @foreach($propertyRooms as $room)
                            @php
                                $theme = $statusTheme[$room->status] ?? $statusTheme['vacant'];
                                $statusLabel = \App\Enums\Property\RoomStatus::tryFrom($room->status)?->label();
                                $contract = $room->activeContract;
                                $residents = $contract?->residents ?? collect();
                                $ownerName = $residents->firstWhere('is_primary', true)?->tenant?->full_name;
                                $documentStatus = $contract?->document ? \App\Enums\Contract\ContractDocumentStatus::tryFrom($contract->document->status) : null;
                                $documentText = $contract ? ($documentStatus?->label() ?? 'Chưa lập') : '—';
                                $documentClass = $contract && !$documentStatus ? 'text-amber-600' : 'text-slate-500';
                                $furniture = collect($room->furniture ?? []);
                                $brokenCount = $furniture->where('condition', 'broken')->count();
                                $areaText = $room->area ? str_replace('.', ',', (string) (float) $room->area).' m²' : null;
                            @endphp
                            <a href="{{ route('manage.rooms.show', $room) }}" data-room-status="{{ $room->status }}"
                               class="grid {{ $rowGrid }} items-center gap-x-3 xl:gap-x-2 px-3 xl:px-4 py-2.5 min-h-[52px] hover:bg-slate-50 transition-colors">
                                <span class="flex items-center gap-2 min-w-0">
                                    <span class="w-2 h-2 rounded-full shrink-0 {{ $theme[0] }}"></span>
                                    <span class="text-sm font-bold text-slate-900 break-words min-w-0">{{ $room->name }}</span>
                                </span>
                                <span class="hidden xl:block min-w-0 text-xs font-semibold break-words {{ $room->room_type ? 'text-slate-600' : 'text-slate-300' }}">{{ $room->room_type ?: '—' }}</span>
                                <span class="hidden xl:block text-xs font-semibold tabular-nums whitespace-nowrap {{ $areaText ? 'text-slate-600' : 'text-slate-300' }}">{{ $areaText ?? '—' }}</span>

                                <span class="xl:hidden min-w-0">
                                    <span class="block text-sm font-semibold break-words {{ $ownerName ? 'text-slate-800' : 'text-slate-400' }}">{{ $ownerName ?? $statusLabel }}</span>
                                    <span class="block text-[11px] font-semibold text-slate-400 break-words">
                                        {{ $areaText }}@if($contract){{ $areaText ? ' · ' : '' }}{{ $residents->count() }} người · HĐ <span class="{{ $documentClass }}">{{ mb_strtolower($documentText) }}</span>@endif
                                        @if($brokenCount){{ $areaText || $contract ? '· ' : '' }}<span class="text-rose-600">{{ $brokenCount }} đồ hỏng</span>@endif
                                    </span>
                                </span>

                                <span class="hidden xl:inline-flex w-max px-2 py-0.5 rounded-md text-[11px] font-bold {{ $theme[3] }}">{{ $statusLabel }}</span>
                                <span class="hidden xl:block min-w-0 text-sm font-semibold break-words {{ $ownerName ? 'text-slate-800' : 'text-slate-300' }}">{{ $ownerName ?? '—' }}</span>
                                <span class="hidden xl:block text-sm font-semibold text-slate-600 tabular-nums">{{ $contract ? $residents->count().' người' : '' }}</span>
                                <span class="hidden xl:block text-xs font-bold {{ $documentClass }}">{{ $documentText }}</span>
                                <span class="hidden xl:flex items-center gap-1.5 text-xs font-semibold text-slate-500">
                                    @if($furniture->isEmpty())
                                        <span class="text-slate-300">—</span>
                                    @else
                                        {{ $furniture->count() }} món
                                        @if($brokenCount)<span class="px-1.5 py-0.5 rounded-md text-[10px] font-bold {{ $furnitureTone['broken'] }}">{{ $brokenCount }} hỏng</span>@endif
                                    @endif
                                </span>
                                <span class="text-sm font-bold text-slate-800 whitespace-nowrap tabular-nums text-right">{{ number_format($room->base_rent, 0, ',', '.') }}đ</span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="p-6 text-center text-sm font-semibold text-slate-400">Khu này chưa có phòng. Bấm "Thêm phòng" để khai báo phòng và đồng hồ điện nước.</p>
                @endif
            </section>
        @endforeach

        <div id="room-filter-empty" class="hidden card p-8 text-center text-sm font-semibold text-slate-400">Không có phòng nào khớp bộ lọc.</div>
    @endif

    <datalist id="room-type-options">
        @foreach(\App\Models\Room::ROOM_TYPE_SUGGESTIONS as $suggestion)<option value="{{ $suggestion }}">@endforeach
    </datalist>

    <form id="property-delete-form" method="POST" class="hidden">@csrf @method('DELETE')</form>

    {{-- Modal thêm / sửa khu --}}
    <div id="property-modal" data-close-fn="closeActiveModal" onclick="if (event.target === this) window.closeOverlay('property-modal')"
         class="hidden fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm opacity-0 transition-opacity duration-300 flex items-end sm:items-center justify-center sm:p-4">
        <form id="property-form" method="POST" action="{{ route('manage.properties.store') }}" data-panel onsubmit="window.showGlobalLoading()"
              class="bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-lg max-h-[92dvh] overflow-y-auto scale-95 transition-transform duration-300 pb-safe">
            @csrf
            <input type="hidden" name="_method" value="POST" id="property-method">
            @include('partials.modal-header', ['modalId' => 'property-modal', 'icon' => 'building-2', 'title' => 'Thêm khu', 'titleId' => 'property-modal-title'])
            <div class="p-5 sm:p-6 space-y-4">
                <div>
                    <label for="property-name" class="form-label">Tên khu</label>
                    <input type="text" id="property-name" name="name" required maxlength="255" value="{{ old('name') }}" placeholder="VD: Toà A" class="form-input">
                </div>
                <div>
                    <label for="property-address" class="form-label">Địa chỉ</label>
                    <input type="text" id="property-address" name="address" required maxlength="500" value="{{ old('address') }}" placeholder="Số nhà, đường, quận" class="form-input">
                </div>
                <div>
                    <label for="property-note" class="form-label">Ghi chú</label>
                    <textarea id="property-note" name="note" rows="3" maxlength="2000" class="form-input">{{ old('note') }}</textarea>
                </div>
            </div>
            <div class="px-5 sm:px-6 py-4 border-t border-slate-100 grid grid-cols-2 sm:flex sm:justify-end gap-3">
                <button type="button" onclick="window.closeOverlay('property-modal')" class="btn btn-secondary">Đóng</button>
                <button type="submit" class="btn btn-primary"><i data-lucide="save" class="w-4 h-4"></i> Lưu</button>
            </div>
        </form>
    </div>

    {{-- Modal thêm phòng --}}
    <div id="room-create-modal" data-close-fn="closeActiveModal" onclick="if (event.target === this) window.closeOverlay('room-create-modal')"
         class="hidden fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm opacity-0 transition-opacity duration-300 flex items-end sm:items-center justify-center sm:p-4">
        <form id="room-create-form" method="POST" data-panel onsubmit="window.showGlobalLoading()"
              class="bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-xl max-h-[92dvh] overflow-y-auto scale-95 transition-transform duration-300 pb-safe">
            @csrf
            @include('partials.modal-header', ['modalId' => 'room-create-modal', 'icon' => 'door-closed', 'title' => 'Thêm phòng', 'titleId' => 'room-create-title'])
            <div class="p-5 sm:p-6 space-y-4">
                <div class="grid grid-cols-2 gap-3 sm:gap-4">
                    <div>
                        <label for="new-name" class="form-label">Tên / số phòng</label>
                        <input type="text" id="new-name" name="name" required maxlength="50" value="{{ old('name') }}" placeholder="VD: 101" class="form-input">
                    </div>
                    <div>
                        <label for="new-area" class="form-label">Diện tích (m²)</label>
                        <input type="number" step="0.01" id="new-area" name="area" value="{{ old('area') }}" class="form-input">
                    </div>
                    <div class="col-span-2">
                        <label for="new-type" class="form-label">Loại phòng</label>
                        <input type="text" id="new-type" name="room_type" maxlength="50" list="room-type-options" value="{{ old('room_type') }}" placeholder="VD: Phòng đơn, Có gác lửng, Studio" class="form-input">
                    </div>
                </div>
                <div>
                    <label for="new-rent" class="form-label">Giá thuê mặc định (đ/tháng)</label>
                    <input type="number" id="new-rent" name="base_rent" required min="0" value="{{ old('base_rent') }}" class="form-input">
                </div>

                @include('manage.rooms.partials.meter-fields', ['prefix' => 'new', 'meters' => null])
            </div>
            <div class="px-5 sm:px-6 py-4 border-t border-slate-100 grid grid-cols-2 sm:flex sm:justify-end gap-3">
                <button type="button" onclick="window.closeOverlay('room-create-modal')" class="btn btn-secondary">Đóng</button>
                <button type="submit" class="btn btn-primary"><i data-lucide="plus" class="w-4 h-4"></i> Thêm phòng</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    const propertyUrls = {
        store: @js(route('manage.properties.store')),
        update: @js(route('manage.properties.update', ['property' => '__ID__'])),
        destroy: @js(route('manage.properties.destroy', ['property' => '__ID__'])),
        storeRoom: @js(route('manage.properties.rooms.store', ['property' => '__ID__']))
    };
    const roomFilter = { status: '', property: '' };

    /** Lọc theo trạng thái và/hoặc khu; ẩn khu không còn phòng nào khớp. */
    window.filterRooms = function (change) {
        Object.assign(roomFilter, change);
        document.querySelectorAll('.room-filter').forEach(btn => {
            const active = btn.dataset.roomFilter === roomFilter.status;
            btn.classList.toggle('ring-2', active);
            btn.classList.toggle('ring-indigo-500', active);
        });
        document.querySelectorAll('.property-filter').forEach(btn => {
            const active = btn.dataset.propertyFilter === roomFilter.property;
            btn.classList.toggle('bg-slate-900', active);
            btn.classList.toggle('text-white', active);
            btn.classList.toggle('bg-white', !active);
            btn.classList.toggle('border', !active);
        });
        let visibleTotal = 0;
        document.querySelectorAll('[data-property]').forEach(section => {
            const propertyMatch = !roomFilter.property || section.dataset.property === roomFilter.property;
            let visibleInProperty = 0;
            section.querySelectorAll('[data-room-status]').forEach(row => {
                const show = propertyMatch && (!roomFilter.status || row.dataset.roomStatus === roomFilter.status);
                row.classList.toggle('hidden', !show);
                if (show) visibleInProperty++;
            });
            // Chưa lọc trạng thái thì vẫn hiện khu trống để còn bấm "Thêm phòng"
            section.classList.toggle('hidden', !propertyMatch || (roomFilter.status !== '' && visibleInProperty === 0));
            visibleTotal += visibleInProperty;
        });
        document.getElementById('room-filter-empty')?.classList.toggle('hidden', visibleTotal > 0 || roomFilter.status === '');
    };

    /** Không truyền data = thêm mới; truyền data = sửa khu. */
    window.openPropertyModal = function (data = null) {
        const form = document.getElementById('property-form');
        form.action = data ? propertyUrls.update.replace('__ID__', data.id) : propertyUrls.store;
        document.getElementById('property-method').value = data ? 'PUT' : 'POST';
        document.getElementById('property-modal-title').textContent = data ? 'Sửa khu' : 'Thêm khu';
        document.getElementById('property-name').value = data ? data.name : '';
        document.getElementById('property-address').value = data ? data.address : '';
        document.getElementById('property-note').value = data && data.note ? data.note : '';
        window.openOverlay('property-modal');
    };

    window.deleteProperty = function (id, name) {
        document.getElementById('property-delete-form').action = propertyUrls.destroy.replace('__ID__', id);
        window.confirmSubmit('property-delete-form', { title: 'Xóa khu ' + window.escapeHtml(name) + '?', html: '<p class="text-sm text-slate-600 break-words">Chỉ xóa được khi khu chưa có phòng nào.</p>', confirmText: 'Xóa khu', danger: true });
    };

    window.openRoomCreateModal = function (propertyId, propertyName) {
        document.getElementById('room-create-form').action = propertyUrls.storeRoom.replace('__ID__', propertyId);
        document.getElementById('room-create-title').textContent = 'Thêm phòng · ' + propertyName;
        window.openOverlay('room-create-modal');
    };
</script>
@endpush
