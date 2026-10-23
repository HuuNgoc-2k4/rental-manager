@php
    $currentUser = Auth::user();
    $roleEnum = $currentUser->roleEnum();
@endphp
<header class="h-14 bg-white border-b border-slate-200 flex items-center justify-between gap-2 px-4 sm:px-6 z-30 relative shrink-0">
    <div class="flex items-center gap-2 flex-1 min-w-0">
        {{-- Mobile: sidebar ẩn nên hiện logo ở navbar --}}
        <a href="{{ route('dashboard') }}" class="md:hidden flex items-center gap-2 min-w-0">
            <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shrink-0">
                <i data-lucide="home" class="w-4 h-4 text-white"></i>
            </span>
            <span class="font-bold text-slate-900 tracking-tight break-words min-w-0">Nhà cho thuê</span>
        </a>
    </div>

    <div class="flex items-center gap-1 sm:gap-3 shrink-0">
        <div class="relative" id="user-menu">
            <button type="button" onclick="window.toggleUserMenu()" aria-label="Tài khoản" aria-haspopup="true"
                    class="flex items-center gap-3 min-h-[44px] pl-1 pr-1 sm:pr-3 rounded-full hover:bg-slate-100 transition-colors">
                @include('partials.avatar', ['name' => $currentUser->name, 'size' => 'w-9 h-9 text-xs'])
                <span class="hidden sm:block text-left min-w-0 max-w-[14rem]">
                    <span class="block text-sm font-bold text-slate-800 break-words leading-tight">{{ $currentUser->name }}</span>
                    <span class="block text-[11px] text-slate-400 font-semibold">{{ $roleEnum?->label() }}</span>
                </span>
                <i data-lucide="chevron-down" class="hidden sm:block w-4 h-4 text-slate-400"></i>
            </button>

            <div id="user-menu-panel"
                 class="hidden absolute right-0 top-full mt-2 w-[calc(100vw-2rem)] max-w-xs bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden z-50">
                <div class="p-4 bg-slate-50/80 border-b border-slate-100 flex items-center gap-3">
                    @include('partials.avatar', ['name' => $currentUser->name, 'size' => 'w-11 h-11 text-sm'])
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-slate-800 break-words">{{ $currentUser->name }}</p>
                        <p class="text-xs text-slate-500 font-medium break-words">{{ $currentUser->email ?? $currentUser->phone }}</p>
                        @if($roleEnum)
                            <span class="badge mt-2 {{ $roleEnum->color() }}">{{ $roleEnum->label() }}</span>
                        @endif
                    </div>
                </div>
                <div class="p-2">
                    @can('access-member-portal')
                        <a href="{{ route('member.profile.edit') }}" class="flex items-center gap-3 min-h-[44px] px-3 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-50">
                            <i data-lucide="id-card" class="w-4 h-4 text-slate-400"></i> Hồ sơ của tôi
                        </a>
                    @endcan
                    <a href="{{ route('password.edit') }}" class="flex items-center gap-3 min-h-[44px] px-3 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-50">
                        <i data-lucide="key-round" class="w-4 h-4 text-slate-400"></i> Đổi mật khẩu
                    </a>
                    <button type="button" onclick="window.confirmLogout()" class="w-full flex items-center gap-3 min-h-[44px] px-3 rounded-xl text-sm font-semibold text-rose-600 hover:bg-rose-50">
                        <i data-lucide="log-out" class="w-4 h-4"></i> Đăng xuất
                    </button>
                </div>
            </div>
        </div>
        <form action="{{ route('logout') }}" method="POST" id="logout-form" class="hidden">@csrf</form>
    </div>
</header>
