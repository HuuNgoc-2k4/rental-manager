@extends('layouts.app')

@section('title', 'Khu trọ')
@section('subtitle', 'Danh sách khu trọ / toà nhà. Bấm vào khu để xem sơ đồ phòng.')

@section('page_actions')
    <button type="button" onclick="window.openPropertyModal()"
            class="min-h-[44px] px-5 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-md hover:bg-indigo-700 transition-all flex items-center gap-2">
        <i data-lucide="plus" class="w-4 h-4"></i> Thêm khu
    </button>
@endsection

@section('content')
    @if($properties->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center">
            <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-4">
                <i data-lucide="building-2" class="w-6 h-6 text-slate-400"></i>
            </div>
            <p class="text-sm font-bold text-slate-700">Chưa có khu trọ nào</p>
            <p class="text-xs text-slate-500 font-medium mt-1">Bấm "Thêm khu" để bắt đầu khai báo toà nhà của bạn.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($properties as $property)
                @php
                    $editData = ['id' => $property->id, 'name' => $property->name, 'address' => $property->address, 'note' => $property->note];
                @endphp
                <div class="bg-white rounded-2xl border border-slate-200 p-5 flex flex-col gap-4">
                    <div class="flex items-start justify-between gap-3">
                        <a href="{{ route('manage.properties.show', $property) }}" class="min-w-0 flex-1">
                            <p class="text-base font-black text-slate-800 break-words">{{ $property->name }}</p>
                            <p class="text-xs text-slate-500 font-medium mt-1 break-words">{{ $property->address }}</p>
                        </a>
                        <button type="button" aria-label="Sửa khu"
                                onclick="window.openPropertyModal(@js($editData))"
                                class="w-11 h-11 shrink-0 flex items-center justify-center rounded-xl text-slate-400 hover:text-indigo-600 hover:bg-slate-50 transition-colors">
                            <i data-lucide="pencil" class="w-4 h-4"></i>
                        </button>
                    </div>
                    <a href="{{ route('manage.properties.show', $property) }}" class="grid grid-cols-4 gap-2 text-center">
                        @foreach([
                            ['Tổng', $property->rooms_count, 'text-slate-800'],
                            ['Trống', $property->vacant_rooms_count, 'text-green-600'],
                            ['Đang thuê', $property->occupied_rooms_count, 'text-indigo-600'],
                            ['Đang sửa', $property->maintenance_rooms_count, 'text-amber-600']
                        ] as [$label, $count, $color])
                            <div class="bg-slate-50 rounded-xl py-3">
                                <p class="text-lg font-black {{ $color }}">{{ $count }}</p>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ $label }}</p>
                            </div>
                        @endforeach
                    </a>
                </div>
            @endforeach
        </div>
    @endif

    @php
        $inp = 'w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all';
        $lbl = 'block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2';
    @endphp

    <div id="property-modal" data-close-fn="closeActiveModal" onclick="if (event.target === this) window.closeOverlay('property-modal')"
         class="hidden fixed inset-0 z-50 bg-slate-900/40 opacity-0 transition-opacity duration-300 flex items-center justify-center p-4">
        <form id="property-form" method="POST" action="{{ route('manage.properties.store') }}" data-panel onsubmit="window.showGlobalLoading()"
              class="bg-white rounded-2xl w-full max-w-lg max-h-[90dvh] overflow-y-auto p-5 md:p-6 space-y-4 scale-95 transition-transform duration-300">
            @csrf
            <input type="hidden" name="_method" value="POST" id="property-method">
            <h3 id="property-modal-title" class="text-lg font-black text-slate-800">Thêm khu</h3>
            <div>
                <label for="property-name" class="{{ $lbl }}">Tên khu</label>
                <input type="text" id="property-name" name="name" required maxlength="255" value="{{ old('name') }}" class="{{ $inp }}">
            </div>
            <div>
                <label for="property-address" class="{{ $lbl }}">Địa chỉ</label>
                <input type="text" id="property-address" name="address" required maxlength="500" value="{{ old('address') }}" class="{{ $inp }}">
            </div>
            <div>
                <label for="property-note" class="{{ $lbl }}">Ghi chú</label>
                <textarea id="property-note" name="note" rows="3" maxlength="2000" class="{{ $inp }}">{{ old('note') }}</textarea>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="window.closeOverlay('property-modal')" class="min-h-[44px] px-6 bg-slate-100 text-slate-600 rounded-xl text-sm font-bold hover:bg-slate-200 transition-all">Đóng</button>
                <button type="submit" class="min-h-[44px] px-6 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-md hover:bg-indigo-700 transition-all">Lưu</button>
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
