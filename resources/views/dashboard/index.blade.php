@extends('layouts.app')

@section('title', 'Tổng quan')
@section('subtitle', 'Tình hình cho thuê và các việc đang chờ bạn xử lý.')

@php
    $totalRooms = $properties->sum('rooms_count');
    $occupiedRooms = $properties->sum('occupied_rooms_count');
    $vacantRooms = $properties->sum('vacant_rooms_count');
    $maintenanceRooms = $properties->sum('maintenance_rooms_count');
    $occupancyRate = $totalRooms > 0 ? (int) round($occupiedRooms * 100 / $totalRooms) : 0;
    $pendingTotal = $pendingMembers->count() + $declarations->count();
    // Vòng tròn tỉ lệ lấp đầy: chu vi đường tròn bán kính 42 trong viewBox 100
    $ringCircumference = 2 * M_PI * 42;
    $statCards = [
        ['building-2', 'Khu trọ', $properties->count(), 'toà nhà đang quản lý', 'bg-indigo-50 text-indigo-600'],
        ['layout-grid', 'Tổng phòng', $totalRooms, $maintenanceRooms.' phòng đang sửa', 'bg-sky-50 text-sky-600'],
        ['door-open', 'Phòng trống', $vacantRooms, 'sẵn sàng cho thuê', 'bg-emerald-50 text-emerald-600'],
        ['door-closed', 'Đang thuê', $occupiedRooms, 'phòng đang có người ở', 'bg-violet-50 text-violet-600']
    ];
@endphp

@section('content')
    <div class="space-y-6">
        {{-- Hero: lời chào + tỉ lệ lấp đầy --}}
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-600 via-indigo-600 to-violet-600 text-white p-5 sm:p-8 shadow-xl shadow-indigo-200">
            <div class="absolute -right-16 -top-16 w-64 h-64 rounded-full bg-white/10"></div>
            <div class="absolute right-24 -bottom-20 w-48 h-48 rounded-full bg-white/5"></div>

            <div class="relative flex items-center justify-between gap-4 sm:gap-6">
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-bold uppercase tracking-widest text-indigo-200">Xin chào</p>
                    <p class="text-xl sm:text-3xl font-bold tracking-tight break-words mt-1">{{ $user->name }}</p>
                </div>

                <div class="relative w-24 h-24 sm:w-32 sm:h-32 shrink-0">
                    <svg viewBox="0 0 100 100" class="w-full h-full -rotate-90">
                        <circle cx="50" cy="50" r="42" fill="none" stroke="currentColor" stroke-width="10" class="text-white/15"/>
                        <circle cx="50" cy="50" r="42" fill="none" stroke="currentColor" stroke-width="10" stroke-linecap="round"
                                stroke-dasharray="{{ $ringCircumference }}" stroke-dashoffset="{{ $ringCircumference * (1 - $occupancyRate / 100) }}"
                                class="text-white"/>
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-xl sm:text-3xl font-bold tabular-nums">{{ $occupancyRate }}%</span>
                        <span class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-indigo-200">lấp đầy</span>
                    </div>
                </div>
            </div>

        </div>

        {{-- Thẻ số liệu --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            @foreach($statCards as [$icon, $label, $value, $caption, $tone])
                <div class="card p-4 sm:p-5">
                    <div class="flex items-start justify-between gap-2">
                        <p class="section-label break-words min-w-0">{{ $label }}</p>
                        <span class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl flex items-center justify-center shrink-0 {{ $tone }}">
                            <i data-lucide="{{ $icon }}" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                        </span>
                    </div>
                    <p class="text-2xl sm:text-3xl font-bold text-slate-900 tabular-nums mt-2">{{ $value }}</p>
                    <p class="text-[11px] sm:text-xs font-semibold text-slate-400 mt-1 break-words">{{ $caption }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Tình trạng theo khu --}}
            <div class="card lg:col-span-2 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                            <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                        </span>
                        <div class="min-w-0">
                            <h3 class="font-bold text-slate-800">Tình trạng theo khu</h3>
                            <p class="text-[11px] font-semibold text-slate-400">Trống · đang thuê · đang sửa</p>
                        </div>
                    </div>
                </div>

                @forelse($properties as $property)
                    @php
                        $roomTotal = max($property->rooms_count, 1);
                        $segments = [
                            ['bg-indigo-500', $property->occupied_rooms_count],
                            ['bg-emerald-400', $property->vacant_rooms_count],
                            ['bg-amber-400', $property->maintenance_rooms_count]
                        ];
                    @endphp
                    <a href="{{ route('manage.rooms.index').'#property-'.$property->id }}" class="block px-5 py-4 border-b border-slate-50 last:border-b-0 hover:bg-slate-50/70 transition-colors">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-bold text-slate-800 break-words">{{ $property->name }}</p>
                                <p class="text-xs text-slate-400 font-medium break-words flex items-start gap-1 mt-0.5">
                                    <i data-lucide="map-pin" class="w-3.5 h-3.5 shrink-0 mt-px"></i>{{ $property->address }}
                                </p>
                            </div>
                            <p class="text-sm font-bold text-slate-800 whitespace-nowrap tabular-nums">
                                {{ $property->occupied_rooms_count }}<span class="text-slate-400 font-bold">/{{ $property->rooms_count }}</span>
                            </p>
                        </div>
                        <div class="flex h-2.5 rounded-full overflow-hidden bg-slate-100 mt-3">
                            @foreach($segments as [$color, $count])
                                @if($count > 0)
                                    <div class="{{ $color }}" style="width: {{ $count * 100 / $roomTotal }}%"></div>
                                @endif
                            @endforeach
                        </div>
                        <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2 text-[11px] font-bold text-slate-500">
                            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-indigo-500"></span>{{ $property->occupied_rooms_count }} đang thuê</span>
                            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-400"></span>{{ $property->vacant_rooms_count }} trống</span>
                            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-amber-400"></span>{{ $property->maintenance_rooms_count }} đang sửa</span>
                        </div>
                    </a>
                @empty
                    <div class="px-5 py-12 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-3">
                            <i data-lucide="building-2" class="w-6 h-6 text-slate-400"></i>
                        </div>
                        <p class="text-sm font-bold text-slate-700">Chưa có khu trọ nào</p>
                        <a href="{{ route('manage.rooms.index') }}" class="btn btn-soft mt-4"><i data-lucide="plus" class="w-4 h-4"></i> Thêm khu đầu tiên</a>
                    </div>
                @endforelse
            </div>

            {{-- Việc cần làm --}}
            <div class="card overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                            <i data-lucide="list-checks" class="w-5 h-5"></i>
                        </span>
                        <div class="min-w-0">
                            <h3 class="font-bold text-slate-800">Việc cần duyệt</h3>
                            <p class="text-[11px] font-semibold text-slate-400">{{ $pendingTotal }} mục đang chờ</p>
                        </div>
                    </div>
                </div>

                <div class="divide-y divide-slate-50">
                    @foreach($pendingMembers->take(4) as $link)
                        <a href="{{ route('manage.tenants.index').'#approvals' }}" class="flex items-center gap-3 px-5 py-3 min-h-[56px] hover:bg-slate-50/70 transition-colors">
                            @include('partials.avatar', ['name' => $link->tenant->full_name, 'size' => 'w-9 h-9 text-xs'])
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-slate-800 break-words">{{ $link->tenant->full_name }}</p>
                                <p class="text-[11px] font-semibold text-slate-400 break-words">Xin vào phòng {{ $link->contract->room->name }} · {{ $link->contract->room->property->name }}</p>
                            </div>
                            <span class="badge bg-amber-50 text-amber-700 ring-amber-600/20">Thành viên</span>
                        </a>
                    @endforeach
                    @foreach($declarations->take(4) as $tenant)
                        <a href="{{ route('manage.tenants.index').'#approvals' }}" class="flex items-center gap-3 px-5 py-3 min-h-[56px] hover:bg-slate-50/70 transition-colors">
                            @include('partials.avatar', ['name' => $tenant->full_name, 'size' => 'w-9 h-9 text-xs'])
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-slate-800 break-words">{{ $tenant->full_name }}</p>
                                <p class="text-[11px] font-semibold text-slate-400 break-words">Gửi khai báo {{ $tenant->updated_at?->locale('vi')->diffForHumans() }}</p>
                            </div>
                            <span class="badge bg-sky-50 text-sky-700 ring-sky-600/20">Khai báo</span>
                        </a>
                    @endforeach

                    @if($pendingTotal === 0)
                        <div class="px-5 py-12 text-center">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center mx-auto mb-3">
                                <i data-lucide="check-check" class="w-6 h-6 text-emerald-600"></i>
                            </div>
                            <p class="text-sm font-bold text-slate-700">Đã xử lý hết</p>
                            <p class="text-xs text-slate-400 font-medium mt-1">Không có yêu cầu nào đang chờ.</p>
                        </div>
                    @endif
                </div>

                @if($pendingTotal > 0)
                    <a href="{{ route('manage.tenants.index').'#approvals' }}" class="flex items-center justify-center gap-2 min-h-[48px] border-t border-slate-100 text-xs font-bold text-indigo-600 uppercase tracking-wider hover:bg-indigo-50/50">
                        Duyệt ở trang Thành viên <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>
        </div>
    </div>
@endsection
