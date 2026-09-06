@php
    $currentUser = Auth::user();
    $roleEnum = $currentUser->roleEnum();
@endphp
<header class="h-14 bg-white border-b border-slate-200 flex items-center justify-between px-6 z-40 relative">
    <div class="flex items-center gap-4 flex-1">
        <button type="button" onclick="window.toggleMobileSidebar()" aria-label="Mở menu"
                class="md:hidden p-2 -ml-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors">
            <i data-lucide="menu" class="w-6 h-6"></i>
        </button>
    </div>

    <div class="flex items-center gap-4">
        @if($roleEnum)
            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold ring-1 ring-inset {{ $roleEnum->color() }}">
                {{ $roleEnum->label() }}
            </span>
        @endif

        <div class="h-8 w-[1px] bg-slate-200"></div>

        <div class="flex items-center gap-3">
            <div class="text-right min-w-0">
                <p class="text-sm font-bold text-slate-800 break-words">{{ $currentUser->name }}</p>
                <p class="text-[11px] text-slate-500 font-medium break-words">{{ $currentUser->email ?? $currentUser->phone }}</p>
            </div>
            <div class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center overflow-hidden">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($currentUser->name) }}&background=4f46e5&color=fff" alt="Avatar">
            </div>
            <form action="{{ route('logout') }}" method="POST" id="logout-form" class="hidden">@csrf</form>
            <button type="button" onclick="window.confirmLogout()" class="text-slate-400 hover:text-rose-500 transition-colors" aria-label="Đăng xuất">
                <i data-lucide="log-out" class="w-5 h-5"></i>
            </button>
        </div>
    </div>
</header>
