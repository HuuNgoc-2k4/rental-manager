{{-- Thanh điều hướng đáy — chỉ mobile. Là phần tử cuối của cột flex (không fixed) nên vùng cuộn <main>
     tự dừng phía trên nó, không cần padding bù. Mục "Menu" mở sidebar cho các trang còn lại. --}}
@php
    // [route, mẫu routeIs, icon, nhãn, badge]
    $bottomItems = Auth::user()->isAdmin()
        ? [
            ['dashboard', 'dashboard', 'layout-dashboard', 'Tổng quan', 0],
            ['manage.rooms.index', ['manage.rooms.*', 'manage.contracts.*'], 'door-open', 'Phòng trọ', 0],
            ['manage.tenants.index', 'manage.tenants.*', 'contact', 'Thành viên', $pendingApprovalCount ?? 0]
        ]
        : [
            ['dashboard', 'dashboard', 'door-open', 'Phòng', 0],
            ['member.contract.show', 'member.contract.*', 'file-signature', 'Hợp đồng', 0],
            ['member.profile.edit', 'member.profile.*', 'id-card', 'Hồ sơ', 0],
            ['password.edit', 'password.*', 'key-round', 'Mật khẩu', 0]
        ];
@endphp
<nav class="md:hidden shrink-0 bg-white border-t border-slate-200 pb-safe z-30" aria-label="Điều hướng nhanh">
    <div class="flex">
        @foreach($bottomItems as [$routeName, $pattern, $icon, $label, $badge])
            @php $isActive = request()->routeIs($pattern); @endphp
            <a href="{{ route($routeName) }}"
               class="flex-1 min-w-0 flex flex-col items-center justify-center gap-1 h-16 text-[11px] font-bold transition-colors {{ $isActive ? 'text-indigo-600' : 'text-slate-400' }}">
                <span class="relative flex items-center justify-center w-12 h-7 rounded-full {{ $isActive ? 'bg-indigo-50' : '' }}">
                    <i data-lucide="{{ $icon }}" class="w-5 h-5"></i>
                    @if($badge > 0)
                        <span class="absolute -top-1 right-0.5 min-w-[18px] h-[18px] px-1 bg-rose-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center ring-2 ring-white tabular-nums">{{ $badge > 99 ? '99+' : $badge }}</span>
                    @endif
                </span>
                <span class="break-words text-center leading-none">{{ $label }}</span>
            </a>
        @endforeach
        <button type="button" onclick="window.toggleMobileSidebar()"
                class="flex-1 min-w-0 flex flex-col items-center justify-center gap-1 h-16 text-[11px] font-bold text-slate-400">
            <span class="flex items-center justify-center w-12 h-7 rounded-full"><i data-lucide="menu" class="w-5 h-5"></i></span>
            <span class="leading-none">Menu</span>
        </button>
    </div>
</nav>
