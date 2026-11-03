@php
    $isAdminUser = Auth::user()->isAdmin();

    // Mục đơn: [gate, route, mẫu routeIs, icon, nhãn, badge]; nhóm có submenu: ['group' => id, 'icon', 'label', 'items' => [[gate, route, mẫu routeIs, icon, nhãn, badge]]]
    $navEntries = [
        [null, 'dashboard', 'dashboard', $isAdminUser ? 'layout-dashboard' : 'door-open', $isAdminUser ? 'Tổng quan' : 'Phòng của tôi', 0],
        ['access-member-portal', 'member.contract.show', 'member.contract.*', 'file-signature', 'Hợp đồng thuê', 0],
        ['access-member-portal', 'member.profile.edit', 'member.profile.*', 'id-card', 'Hồ sơ của tôi', 0],
        // Hợp đồng thao tác trong chi tiết phòng nên trang hợp đồng cũng sáng mục "Phòng trọ"
        ['manage-rental', 'manage.rooms.index', ['manage.rooms.*', 'manage.contracts.*'], 'door-open', 'Phòng trọ', 0],
        ['manage-rental', 'manage.tenants.index', 'manage.tenants.*', 'contact', 'Thành viên', $pendingApprovalCount ?? 0],
        ['is-admin', 'admin.users.index', 'admin.users.*', 'users', 'Người dùng', 0]
    ];
    // Phân hệ trong đề tài nhưng chưa làm — gom một nhóm mờ để chủ nhà biết lộ trình.
    $upcomingModules = [['gauge', 'Chốt chỉ số'], ['receipt-text', 'Hóa đơn & công nợ']];

    $itemClass = fn (bool $active) => $active ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-600 font-semibold hover:bg-slate-50 hover:text-slate-900';
    $subItemClass = fn (bool $active) => $active ? 'text-indigo-700 font-bold bg-indigo-50/60' : 'text-slate-500 font-medium hover:text-indigo-600 hover:bg-slate-50';
@endphp

<div id="sidebar-overlay" onclick="window.toggleMobileSidebar()"
     class="hidden md:hidden fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-sm opacity-0 transition-opacity duration-300"></div>

<aside id="app-sidebar"
       class="fixed md:static inset-y-0 left-0 z-50 w-72 md:w-60 bg-white border-r border-slate-200 flex flex-col shrink-0
              -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out">
    <div class="h-14 flex items-center gap-3 px-4 border-b border-slate-100 shrink-0">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 min-w-0 flex-1">
            <span class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shrink-0 shadow-md shadow-indigo-200">
                <i data-lucide="home" class="w-5 h-5 text-white"></i>
            </span>
            <span class="font-bold text-slate-900 tracking-tight break-words min-w-0">Nhà cho thuê</span>
        </a>
        <button type="button" onclick="window.toggleMobileSidebar()" aria-label="Đóng menu"
                class="md:hidden btn-icon text-slate-400 hover:text-rose-600 hover:bg-rose-50">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1.5 pb-safe">
        @foreach($navEntries as $entry)
            @if(isset($entry['group']))
                @continue(!Gate::allows($entry['gate']))
                @php
                    $groupBadge = collect($entry['items'])->sum(5);
                    $groupActive = collect($entry['items'])->contains(fn ($item) => request()->routeIs($item[2]));
                @endphp
                <div>
                    <button type="button" data-submenu-target="{{ $entry['group'] }}" aria-expanded="{{ $groupActive ? 'true' : 'false' }}"
                            class="w-full flex items-center gap-3 min-h-[44px] px-3 rounded-xl text-[13px] font-semibold transition-all focus:outline-none {{ $groupActive ? 'text-indigo-700 bg-indigo-50/60' : 'text-slate-600 hover:bg-slate-50' }}">
                        <i data-lucide="{{ $entry['icon'] }}" class="w-5 h-5 shrink-0"></i>
                        <span class="flex-1 min-w-0 text-left break-words">{{ $entry['label'] }}</span>
                        {{-- Nhóm đang đóng vẫn báo có việc chờ --}}
                        @if($groupBadge > 0)
                            <span class="min-w-[20px] h-5 px-1.5 rounded-full bg-rose-500 text-white text-[10px] font-bold flex items-center justify-center tabular-nums {{ $groupActive ? 'hidden' : '' }}" data-group-badge>{{ $groupBadge > 99 ? '99+' : $groupBadge }}</span>
                        @endif
                        <i data-lucide="chevron-down" class="w-4 h-4 shrink-0 submenu-chevron transition-transform duration-300 {{ $groupActive ? 'rotate-180' : '' }}"></i>
                    </button>
                    <div id="{{ $entry['group'] }}" class="smooth-collapse {{ $groupActive ? 'is-open' : '' }}">
                        <div class="smooth-collapse-inner">
                            <div class="mt-1 ml-5 pl-2 space-y-1 border-l-2 border-slate-100 py-1">
                                @foreach($entry['items'] as [$gate, $routeName, $pattern, $icon, $label, $badge])
                                    @continue(!Gate::allows($gate))
                                    <a href="{{ route($routeName) }}"
                                       class="flex items-center gap-3 min-h-[40px] px-3 rounded-lg text-[13px] transition-all {{ $subItemClass(request()->routeIs($pattern)) }}">
                                        <i data-lucide="{{ $icon }}" class="w-4 h-4 shrink-0"></i>
                                        <span class="flex-1 min-w-0 break-words">{{ $label }}</span>
                                        @if($badge > 0)
                                            <span class="min-w-[20px] h-5 px-1.5 rounded-full bg-rose-500 text-white text-[10px] font-bold flex items-center justify-center tabular-nums">{{ $badge > 99 ? '99+' : $badge }}</span>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @else
                @continue($entry[0] !== null && !Gate::allows($entry[0]))
                <a href="{{ route($entry[1]) }}"
                   class="flex items-center gap-3 min-h-[44px] px-3 rounded-xl text-[13px] transition-all {{ $itemClass(request()->routeIs($entry[2])) }}">
                    <i data-lucide="{{ $entry[3] }}" class="w-5 h-5 shrink-0"></i>
                    <span class="flex-1 min-w-0 break-words">{{ $entry[4] }}</span>
                    @if($entry[5] > 0)
                        <span class="min-w-[20px] h-5 px-1.5 rounded-full bg-rose-500 text-white text-[10px] font-bold flex items-center justify-center tabular-nums">{{ $entry[5] > 99 ? '99+' : $entry[5] }}</span>
                    @endif
                </a>
            @endif
        @endforeach

        @if($isAdminUser)
            <div class="pt-2">
                <button type="button" data-submenu-target="upcoming-menu" aria-expanded="false"
                        class="w-full flex items-center gap-3 min-h-[44px] px-3 rounded-xl text-[13px] font-semibold text-slate-400 hover:bg-slate-50 transition-all focus:outline-none">
                    <i data-lucide="hourglass" class="w-5 h-5 shrink-0"></i>
                    <span class="flex-1 min-w-0 text-left">Sắp ra mắt</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 shrink-0 submenu-chevron transition-transform duration-300"></i>
                </button>
                <div id="upcoming-menu" class="smooth-collapse">
                    <div class="smooth-collapse-inner">
                        <div class="mt-1 ml-5 pl-2 space-y-1 border-l-2 border-slate-100 py-1">
                            @foreach($upcomingModules as [$icon, $label])
                                <div class="flex items-center gap-3 min-h-[40px] px-3 rounded-lg text-[13px] font-medium text-slate-400 cursor-not-allowed">
                                    <i data-lucide="{{ $icon }}" class="w-4 h-4 shrink-0"></i>
                                    <span class="flex-1 min-w-0 break-words">{{ $label }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </nav>
</aside>

@push('scripts')
    <script>
        // Bấm tiêu đề nhóm → đóng/mở submenu; badge của nhóm chỉ hiện khi nhóm đóng
        document.querySelectorAll('[data-submenu-target]').forEach(function (trigger) {
            trigger.addEventListener('click', function () {
                const submenu = document.getElementById(this.dataset.submenuTarget);
                if (!submenu) return;
                const isOpen = submenu.classList.toggle('is-open');
                this.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                this.querySelector('.submenu-chevron')?.classList.toggle('rotate-180', isOpen);
                this.querySelector('[data-group-badge]')?.classList.toggle('hidden', isOpen);
            });
        });
    </script>
@endpush
