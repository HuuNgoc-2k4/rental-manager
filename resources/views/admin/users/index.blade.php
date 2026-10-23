@extends('layouts.app')

@section('title', 'Người dùng')
@section('subtitle', 'Quản lý tài khoản và vai trò: quản trị viên, chủ hộ, thành viên.')

@section('content')
    <div class="space-y-5">
        @if($pendingUsers->isNotEmpty())
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 sm:p-5">
                <div class="flex items-center gap-3 mb-4">
                    <span class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0"><i data-lucide="hourglass" class="w-5 h-5"></i></span>
                    <p class="text-sm font-bold text-amber-800">{{ $pendingUsers->count() }} tài khoản chờ duyệt</p>
                </div>
                <div class="space-y-2">
                    @foreach($pendingUsers as $pending)
                        <div class="flex flex-col sm:flex-row sm:items-center gap-3 bg-white rounded-xl p-3 border border-amber-100">
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                @include('partials.avatar', ['name' => $pending->name, 'size' => 'w-10 h-10 text-xs'])
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-slate-800 break-words">{{ $pending->name }}</p>
                                    <p class="text-xs text-slate-500 font-medium break-words">{{ $pending->email ?? $pending->phone }} · {{ $pending->roleEnum()?->label() }}</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 sm:flex gap-2 shrink-0">
                                <button type="button" onclick="window.changeUserStatus({{ $pending->id }}, 'blocked', @js($pending->name))" class="btn btn-secondary">Từ chối</button>
                                <button type="button" onclick="window.changeUserStatus({{ $pending->id }}, 'active', @js($pending->name))" class="btn btn-success"><i data-lucide="check" class="w-4 h-4"></i> Duyệt</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <form method="GET" action="{{ route('admin.users.index') }}" class="card p-4 sm:p-5 grid grid-cols-2 md:flex gap-3">
            <div class="relative col-span-2 md:flex-1">
                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 pointer-events-none"><i data-lucide="search" class="w-5 h-5"></i></span>
                <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="Tìm theo tên, email, số điện thoại" class="form-input pl-12">
            </div>
            <select name="role" class="form-input md:w-48">
                <option value="">Tất cả vai trò</option>
                @foreach($roles as $role)
                    <option value="{{ $role->value }}" @selected(request('role') === $role->value)>{{ $role->label() }}</option>
                @endforeach
            </select>
            <select name="status" class="form-input md:w-48">
                <option value="">Tất cả trạng thái</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <div class="col-span-2 md:col-span-1 flex gap-2">
                <button type="submit" class="btn btn-dark flex-1"><i data-lucide="filter" class="w-4 h-4"></i> Lọc</button>
                <a href="{{ route('admin.users.index') }}" class="btn-icon btn-outline" title="Xóa bộ lọc" aria-label="Xóa bộ lọc"><i data-lucide="rotate-ccw" class="w-4 h-4"></i></a>
            </div>
        </form>

        <div class="card overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <p class="text-sm font-bold text-slate-800">{{ $users->total() }} tài khoản</p>
            </div>

            {{-- Mobile: thẻ --}}
            <div class="lg:hidden divide-y divide-slate-100">
                @forelse($users as $item)
                    <button type="button" onclick="window.openUserDrawer(this)" class="w-full text-left p-4 flex items-start gap-3 hover:bg-slate-50/70 transition-colors"
                            data-id="{{ $item->id }}" data-name="{{ $item->name }}" data-email="{{ $item->email }}" data-phone="{{ $item->phone }}" data-role="{{ $item->role }}" data-status="{{ $item->status }}">
                        @include('partials.avatar', ['name' => $item->name, 'size' => 'w-11 h-11 text-sm'])
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-bold text-slate-800 break-words">{{ $item->name }}</span>
                            <span class="block text-[11px] font-semibold text-slate-500 break-all mt-0.5">{{ $item->email ?? $item->phone }}</span>
                            <span class="mt-2 flex flex-wrap gap-1.5">
                                @if($roleEnum = $item->roleEnum())<span class="badge {{ $roleEnum->color() }}">{{ $roleEnum->label() }}</span>@endif
                                @if($statusEnum = $item->statusEnum())
                                    <span class="badge {{ $statusEnum->color() }}"><span class="w-1.5 h-1.5 rounded-full {{ $statusEnum->iconColor() }}"></span>{{ $statusEnum->label() }}</span>
                                @endif
                            </span>
                        </span>
                        <i data-lucide="chevron-right" class="w-5 h-5 text-slate-300 shrink-0 mt-3"></i>
                    </button>
                @empty
                    @include('partials.empty-state', ['icon' => 'users', 'title' => 'Không có tài khoản nào', 'text' => null])
                @endforelse
            </div>

            {{-- Desktop: bảng --}}
            <div class="hidden lg:block px-4 pb-2 overflow-x-auto">
                <table class="w-full text-left border-separate border-spacing-y-2">
                    <thead>
                        <tr class="text-[10px] uppercase tracking-widest text-slate-400 font-bold">
                            <th class="py-3 pl-4 pr-4">Người dùng</th>
                            <th class="p-3">Liên hệ</th>
                            <th class="p-3">Vai trò</th>
                            <th class="p-3">Trạng thái</th>
                            <th class="p-3 pr-4 whitespace-nowrap">Ngày tạo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $item)
                            @php $cell = 'bg-white border-y border-slate-100 group-hover:border-indigo-100 group-hover:bg-indigo-50/30 transition-colors'; @endphp
                            <tr onclick="window.openUserDrawer(this)" class="group cursor-pointer text-sm"
                                data-id="{{ $item->id }}" data-name="{{ $item->name }}" data-email="{{ $item->email }}" data-phone="{{ $item->phone }}" data-role="{{ $item->role }}" data-status="{{ $item->status }}">
                                <td class="py-3 pl-4 pr-4 border-l rounded-l-2xl {{ $cell }}">
                                    <div class="flex items-center gap-3">
                                        @include('partials.avatar', ['name' => $item->name, 'size' => 'w-10 h-10 text-xs'])
                                        <p class="font-bold text-slate-800 break-words min-w-0 group-hover:text-indigo-600 transition-colors">{{ $item->name }}</p>
                                    </div>
                                </td>
                                <td class="p-3 {{ $cell }}">
                                    <div class="flex flex-col gap-1 text-xs font-semibold text-slate-500">
                                        <span class="flex items-center gap-2 break-all"><i data-lucide="mail" class="w-3.5 h-3.5 text-slate-300 shrink-0"></i>{{ $item->email ?? '—' }}</span>
                                        @if($item->phone)
                                            <span class="flex items-center gap-2"><i data-lucide="phone" class="w-3.5 h-3.5 text-slate-300 shrink-0"></i>{{ $item->phone }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="p-3 {{ $cell }}">
                                    @if($roleEnum = $item->roleEnum())<span class="badge {{ $roleEnum->color() }}">{{ $roleEnum->label() }}</span>@endif
                                </td>
                                <td class="p-3 {{ $cell }}">
                                    @if($statusEnum = $item->statusEnum())
                                        <span class="badge {{ $statusEnum->color() }}"><span class="w-1.5 h-1.5 rounded-full {{ $statusEnum->iconColor() }}"></span>{{ $statusEnum->label() }}</span>
                                    @endif
                                </td>
                                <td class="p-3 pr-4 border-r rounded-r-2xl text-xs font-semibold text-slate-400 whitespace-nowrap {{ $cell }}">{{ $item->created_at?->format('d/m/Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5">@include('partials.empty-state', ['icon' => 'users', 'title' => 'Không có tài khoản nào', 'text' => null])</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="px-5 py-4 border-t border-slate-100">{{ $users->links() }}</div>
            @endif
        </div>
    </div>

    @include('admin.users.partials.detail-drawer')
@endsection

@push('scripts')
<script>
    const drawerOverlay = document.getElementById('user-drawer-overlay');
    const drawerPanel = document.getElementById('user-drawer-panel');
    const editForm = document.getElementById('user-edit-form');
    const statusForm = document.getElementById('user-status-form');

    const updateUrlTemplate = @js(route('admin.users.update', ['user' => '__ID__']));
    const resetUrlTemplate = @js(route('admin.users.reset-password', ['user' => '__ID__']));
    let currentUserId = null;
    const statusUrlTemplate = @js(route('admin.users.status', ['user' => '__ID__']));

    window.openUserDrawer = function (row) {
        const data = row.dataset;
        currentUserId = data.id;
        editForm.action = updateUrlTemplate.replace('__ID__', data.id);
        editForm.querySelector('[name="name"]').value = data.name || '';
        editForm.querySelector('[name="email"]').value = data.email || '';
        editForm.querySelector('[name="phone"]').value = data.phone || '';
        editForm.querySelector('[name="role"]').value = data.role || '';
        editForm.querySelector('[name="status"]').value = data.status || '';
        editForm.querySelector('[name="password"]').value = '';
        document.getElementById('user-drawer-title').textContent = data.name || '';
        document.getElementById('user-drawer-avatar').innerHTML = window.avatarHtml(data.name, 'w-11 h-11 text-sm');

        drawerOverlay.classList.remove('hidden');
        requestAnimationFrame(() => {
            drawerOverlay.classList.remove('opacity-0');
            drawerPanel.classList.remove('translate-x-full');
        });
    };

    window.closeUserDrawer = function () {
        drawerOverlay.classList.add('opacity-0');
        drawerPanel.classList.add('translate-x-full');
        setTimeout(() => { if (drawerOverlay.classList.contains('opacity-0')) drawerOverlay.classList.add('hidden'); }, 300);
    };

    /** Duyệt / khóa / mở lại tài khoản — submit form ẩn sau khi người dùng xác nhận. */
    window.changeUserStatus = function (userId, status, userName) {
        const isDanger = status === 'blocked';
        const mixin = isDanger ? window.swalDanger : window.swalConfirm;
        const titles = { active: 'Kích hoạt tài khoản?', blocked: 'Khóa tài khoản?', pending: 'Chuyển về chờ duyệt?' };

        mixin.fire({
            icon: isDanger ? 'warning' : 'question',
            iconColor: isDanger ? '#e11d48' : '#4f46e5',
            title: titles[status],
            html: '<p class="text-sm text-slate-600 break-words">Tài khoản: <b>' + window.escapeHtml(userName) + '</b></p>',
            showCancelButton: true,
            confirmButtonText: isDanger ? 'Khóa tài khoản' : 'Xác nhận',
            cancelButtonText: 'Hủy'
        }).then(r => {
            if (!r.isConfirmed) return;
            statusForm.action = statusUrlTemplate.replace('__ID__', userId);
            statusForm.querySelector('[name="status"]').value = status;
            window.showGlobalLoading();
            statusForm.submit();
        });
    };

    window.resetUserPassword = function () {
        document.getElementById('user-reset-form').action = resetUrlTemplate.replace('__ID__', currentUserId);
        window.confirmSubmit('user-reset-form', { title: 'Đặt lại mật khẩu?', html: '<p class="text-sm text-slate-600 break-words">Hệ thống sinh mật khẩu tạm, hiện đúng một lần. Người dùng phải đổi mật khẩu khi đăng nhập.</p>', confirmText: 'Đặt lại' });
    };

    editForm.addEventListener('submit', () => window.showGlobalLoading());
</script>
@endpush
