@extends('layouts.app')

@section('title', 'Khu trọ')
@section('subtitle', 'Danh sách khu trọ / toà nhà. Bấm vào khu để xem sơ đồ phòng.')

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
            <p class="text-sm text-slate-500 font-medium mt-1">Bấm "Thêm khu" để bắt đầu khai báo toà nhà của bạn.</p>
            <button type="button" onclick="window.openPropertyModal()" class="btn btn-primary mt-6"><i data-lucide="plus" class="w-4 h-4"></i> Thêm khu đầu tiên</button>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-5">
            @foreach($properties as $property)
                @php
                    $editData = ['id' => $property->id, 'name' => $property->name, 'address' => $property->address, 'note' => $property->note];
                    $roomTotal = max($property->rooms_count, 1);
                    $occupancy = $property->rooms_count > 0 ? (int) round($property->occupied_rooms_count * 100 / $property->rooms_count) : 0;
                    $counters = [
                        ['Trống', $property->vacant_rooms_count, 'bg-emerald-400', 'text-emerald-600'],
                        ['Đang thuê', $property->occupied_rooms_count, 'bg-indigo-500', 'text-indigo-600'],
                        ['Đang sửa', $property->maintenance_rooms_count, 'bg-amber-400', 'text-amber-600']
                    ];
                @endphp
                <div class="card overflow-hidden flex flex-col hover:border-indigo-200 hover:shadow-md transition-all">
                    <div class="p-5 flex items-start gap-4">
                        <span class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-indigo-200">
                            <i data-lucide="building-2" class="w-6 h-6"></i>
                        </span>
                        <a href="{{ route('manage.properties.show', $property) }}" class="min-w-0 flex-1">
                            <p class="text-base font-bold text-slate-900 break-words hover:text-indigo-600 transition-colors">{{ $property->name }}</p>
                            <p class="text-xs text-slate-500 font-medium mt-1 break-words flex items-start gap-1">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5 shrink-0 mt-px text-slate-400"></i>{{ $property->address }}
                            </p>
                        </a>
                        <button type="button" aria-label="Sửa khu" onclick="window.openPropertyModal(@js($editData))"
                                class="btn-icon -mr-2 -mt-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50">
                            <i data-lucide="pencil" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <a href="{{ route('manage.properties.show', $property) }}" class="px-5 pb-5 flex-1">
                        <div class="flex items-end justify-between gap-3">
                            <p class="section-label">Lấp đầy</p>
                            <p class="text-sm font-bold text-slate-900 tabular-nums">{{ $occupancy }}% <span class="text-slate-400 font-bold">· {{ $property->rooms_count }} phòng</span></p>
                        </div>
                        <div class="flex h-2.5 rounded-full overflow-hidden bg-slate-100 mt-2">
                            @foreach([[$property->occupied_rooms_count, 'bg-indigo-500'], [$property->vacant_rooms_count, 'bg-emerald-400'], [$property->maintenance_rooms_count, 'bg-amber-400']] as [$count, $color])
                                @if($count > 0)
                                    <div class="{{ $color }}" style="width: {{ $count * 100 / $roomTotal }}%"></div>
                                @endif
                            @endforeach
                        </div>
                        <div class="grid grid-cols-3 gap-2 mt-4">
                            @foreach($counters as [$label, $count, $dot, $text])
                                <div class="rounded-xl bg-slate-50 px-2 py-2.5 text-center min-w-0">
                                    <p class="text-lg font-bold tabular-nums {{ $text }}">{{ $count }}</p>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide flex items-center justify-center gap-1 break-words">
                                        <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $dot }}"></span>{{ $label }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </a>

                    <a href="{{ route('manage.properties.show', $property) }}"
                       class="flex items-center justify-between min-h-[48px] px-5 border-t border-slate-100 text-xs font-bold text-indigo-600 uppercase tracking-wider hover:bg-indigo-50/50 transition-colors">
                        Xem sơ đồ phòng <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
            @endforeach

            <button type="button" onclick="window.openPropertyModal()"
                    class="rounded-2xl border-2 border-dashed border-slate-200 min-h-[200px] flex flex-col items-center justify-center gap-3 text-slate-400 hover:border-indigo-300 hover:text-indigo-600 hover:bg-indigo-50/40 transition-all">
                <span class="w-12 h-12 rounded-2xl bg-white border border-slate-200 flex items-center justify-center"><i data-lucide="plus" class="w-6 h-6"></i></span>
                <span class="text-sm font-bold">Thêm khu mới</span>
            </button>
        </div>
    @endif

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
@endsection

@push('scripts')
<script>
    const propertyStoreUrl = @js(route('manage.properties.store'));
    const propertyUpdateUrl = @js(route('manage.properties.update', ['property' => '__ID__']));

    /** Không truyền data = thêm mới; truyền data = sửa khu. */
    window.openPropertyModal = function (data = null) {
        const form = document.getElementById('property-form');
        form.action = data ? propertyUpdateUrl.replace('__ID__', data.id) : propertyStoreUrl;
        document.getElementById('property-method').value = data ? 'PUT' : 'POST';
        document.getElementById('property-modal-title').textContent = data ? 'Sửa khu' : 'Thêm khu';
        document.getElementById('property-name').value = data ? data.name : '';
        document.getElementById('property-address').value = data ? data.address : '';
        document.getElementById('property-note').value = data && data.note ? data.note : '';
        window.openOverlay('property-modal');
    };
</script>
@endpush
