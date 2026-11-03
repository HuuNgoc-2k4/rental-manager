{{--
    Thông tin phòng dùng chung: trang chi tiết phòng (admin) và "Phòng của tôi" (hộ thuê).
    Giá thuê / cọc / điện nước / dịch vụ chỉ có trên văn bản hợp đồng (phòng chỉ có giá niêm yết base_rent),
    nên có văn bản thì lấy theo văn bản, chưa có thì hiện giá niêm yết.

    @param \App\Models\Room $room             đã nạp property
    @param \App\Models\ContractDocument|null $document  văn bản người xem được phép thấy
    @param int $residentCount                 số người đang ở (đã duyệt)
    @param bool $canEditFurniture             admin: hiện nút sửa checklist nội thất (mặc định chỉ xem)
--}}
@php
    $vnd = fn ($amount) => number_format((int) $amount, 0, ',', '.').'đ';
    $facts = array_filter([
        ['banknote', $document ? 'Giá thuê (theo HĐ)' : 'Giá thuê niêm yết', $vnd($document->rent ?? $room->base_rent).'/tháng'],
        $document ? ['piggy-bank', 'Tiền cọc', $vnd($document->deposit)] : null,
        ['ruler', 'Diện tích', $room->area ? str_replace('.', ',', (string) (float) $room->area).' m²' : '—'],
        ['door-closed', 'Loại phòng', $room->room_type ?: '—'],
        // Phòng chưa có người ở thì không hiện số người.
        $residentCount > 0 ? ['users', 'Số người ở', $residentCount.' người'] : null,
        $document ? ['calendar-clock', 'Hạn đóng tiền', 'Ngày '.$document->payment_day.' hằng tháng'] : null
    ]);
    $furniture = collect($room->furniture ?? []);
    $brokenCount = $furniture->where('condition', 'broken')->count();
    $charges = $document ? array_merge(
        [['zap', 'Điện', $vnd($document->electric_price), 'kWh'], ['droplet', 'Nước', $vnd($document->water_price), 'm³']],
        array_map(fn ($service) => ['plus-circle', $service['name'] ?? '', $vnd($service['amount'] ?? 0), $service['unit'] ?? ''], $document->services ?? [])
    ) : [];
@endphp
<div class="card p-4 sm:p-5 space-y-4">
    <div class="flex items-center gap-2">
        <i data-lucide="door-open" class="w-4 h-4 text-indigo-500"></i>
        <p class="section-label">Thông tin phòng</p>
    </div>

    <dl class="grid grid-cols-2 sm:grid-cols-3 gap-2">
        @foreach($facts as [$icon, $label, $value])
            <div class="rounded-xl bg-slate-50 px-3 py-2.5 min-w-0">
                <dt class="flex items-center gap-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest"><i data-lucide="{{ $icon }}" class="w-3 h-3 shrink-0"></i><span class="break-words min-w-0">{{ $label }}</span></dt>
                <dd class="text-sm font-bold text-slate-800 break-words mt-0.5">{{ $value }}</dd>
            </div>
        @endforeach
    </dl>

    {{-- Checklist nội thất và hiện trạng --}}
    <div>
        <div class="flex items-center justify-between gap-2 mb-2">
            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest min-w-0">
                Nội thất & hiện trạng
                @if($furniture->isNotEmpty())<span class="text-slate-400 normal-case tracking-normal">· {{ $furniture->count() }} món</span>@endif
                @if($brokenCount)<span class="ml-1 px-1.5 py-0.5 rounded-md text-[10px] bg-rose-50 text-rose-700 normal-case tracking-normal">{{ $brokenCount }} hỏng</span>@endif
            </p>
            @if(!empty($canEditFurniture))
                <button type="button" onclick="window.openOverlay('furniture-modal')" class="btn btn-soft px-4 shrink-0"><i data-lucide="pencil" class="w-4 h-4"></i> Sửa</button>
            @endif
        </div>
        @if($furniture->isNotEmpty())
            <ul class="divide-y divide-slate-100 rounded-xl border border-slate-100">
                @foreach($furniture as $item)
                    @php $condition = \App\Enums\Property\FurnitureCondition::tryFrom($item['condition'] ?? ''); @endphp
                    <li class="flex items-start gap-3 px-3 py-2.5">
                        <span class="flex-1 min-w-0">
                            <span class="block text-sm font-semibold text-slate-700 break-words">{{ $item['name'] ?? '' }}@if(($item['quantity'] ?? 1) > 1) <span class="text-slate-400">× {{ $item['quantity'] }}</span>@endif</span>
                            @if(filled($item['note'] ?? null))<span class="block text-[11px] font-medium text-slate-400 break-words">{{ $item['note'] }}</span>@endif
                        </span>
                        @if($condition)<span class="badge shrink-0 {{ $condition->color() }}">{{ $condition->label() }}</span>@endif
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-xs text-slate-500 font-medium rounded-xl bg-slate-50 p-3 flex gap-2 break-words">
                <i data-lucide="info" class="w-4 h-4 shrink-0 text-slate-400"></i>
                Chưa khai báo nội thất. Checklist này được chụp vào hợp đồng khi lập hợp đồng.
            </p>
        @endif
    </div>

    <div>
        <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-2">Điện nước & dịch vụ</p>
        @if($charges)
            <ul class="divide-y divide-slate-100 rounded-xl border border-slate-100">
                @foreach($charges as [$icon, $name, $amount, $unit])
                    <li class="flex items-center gap-3 px-3 py-2.5">
                        <i data-lucide="{{ $icon }}" class="w-4 h-4 shrink-0 text-slate-400"></i>
                        <span class="flex-1 min-w-0 text-sm font-semibold text-slate-700 break-words">{{ $name }}</span>
                        <span class="text-sm font-bold text-slate-900 whitespace-nowrap tabular-nums">{{ $amount }}<span class="text-xs font-semibold text-slate-400">/{{ $unit }}</span></span>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-xs text-slate-500 font-medium rounded-xl bg-slate-50 p-3 flex gap-2 break-words">
                <i data-lucide="info" class="w-4 h-4 shrink-0 text-slate-400"></i>
                Đơn giá điện nước và dịch vụ hiện ở đây khi đã có hợp đồng.
            </p>
        @endif
    </div>
</div>
