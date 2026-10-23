<div id="sidebar-overlay" onclick="window.toggleMobileSidebar()"
     class="hidden md:hidden fixed inset-0 z-40 bg-slate-900/40 opacity-0 transition-opacity duration-300"></div>

<aside id="app-sidebar"
       class="fixed md:static inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200 flex flex-col
              -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out">
    <div class="h-14 flex items-center gap-2.5 px-5 border-b border-slate-200 shrink-0">
        <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center shrink-0">
            <i data-lucide="home" class="w-4 h-4 text-white"></i>
        </div>
        <span class="font-black text-slate-800 tracking-tight">Nhà cho thuê</span>
    </div>

    <nav class="flex-1 overflow-y-auto py-4">
        <a href="{{ route('dashboard') }}"
           class="flex items-center gap-3 px-5 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50 transition-colors {{ request()->routeIs('dashboard') ? 'sidebar-item-active' : '' }}">
            <i data-lucide="layout-dashboard" class="w-4 h-4 shrink-0"></i>
            <span class="break-words min-w-0">Tổng quan</span>
        </a>

        {{-- Menu 8 module nghiệp vụ (khu trọ, phòng, khách thuê, hợp đồng, chốt số, hóa đơn, thu tiền, báo cáo)
             thêm vào đây khi từng module hoàn thành — bọc trong @can('manage-rental') (chỉ admin). --}}

        @can('is-admin')
            <p class="px-5 pt-6 pb-2 text-[10px] font-black text-slate-400 uppercase tracking-widest">Hệ thống</p>
            <a href="{{ route('admin.users.index') }}"
               class="flex items-center gap-3 px-5 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50 transition-colors {{ request()->routeIs('admin.users.*') ? 'sidebar-item-active' : '' }}">
                <i data-lucide="users" class="w-4 h-4 shrink-0"></i>
                <span class="break-words min-w-0">Người dùng</span>
            </a>
        @endcan
    </nav>
</aside>
