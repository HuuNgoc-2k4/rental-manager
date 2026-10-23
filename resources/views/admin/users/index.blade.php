@extends('layouts.app')

@section('title', 'Người dùng')
@section('subtitle', 'Quản lý tài khoản và vai trò: quản trị viên, chủ hộ, thành viên.')

@section('content')
    @if($pendingUsers->isNotEmpty())
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 mb-6">
            <p class="text-xs font-black text-amber-700 uppercase tracking-widest mb-4">
                {{ $pendingUsers->count() }} tài khoản chờ duyệt
            </p>
            <div class="space-y-2">
                @foreach($pendingUsers as $pending)
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white rounded-xl px-4 py-3 border border-amber-100">
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-slate-800 break-words">{{ $pending->name }}</p>
                            <p class="text-xs text-slate-500 font-medium break-words">
                                {{ $pending->email ?? $pending->phone }} · {{ $pending->roleEnum()?->label() }}
                            </p>
                        </div>
                        <div class="flex gap-2 shrink-0">
                            <button type="button" onclick="window.changeUserStatus({{ $pending->id }}, 'active', @js($pending->name))"
                                    class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-xs font-bold hover:bg-emerald-700 transition-all">
                                Duyệt
                            </button>
                            <button type="button" onclick="window.changeUserStatus({{ $pending->id }}, 'blocked', @js($pending->name))"
                                    class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg text-xs font-bold hover:bg-slate-200 transition-all">
                                Từ chối
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <form method="GET" action="{{ route('admin.users.index') }}" class="bg-white rounded-2xl border border-slate-200 p-4 mb-6 flex flex-col md:flex-row gap-3">
        <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="Tìm theo tên, email, số điện thoại"
               class="flex-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all">
        <select name="role" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:border-indigo-500 outline-none">
            <option value="">Tất cả vai trò</option>
            @foreach($roles as $role)
                <option value="{{ $role->value }}" @selected(request('role') === $role->value)>{{ $role->label() }}</option>
            @endforeach
        </select>
        <select name="status" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:border-indigo-500 outline-none">
            <option value="">Tất cả trạng thái</option>
            @foreach($statuses as $status)
                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-6 py-2.5 bg-slate-800 text-white rounded-xl text-sm font-bold hover:bg-slate-900 transition-all">
            Lọc
        </button>
    </form>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                        <th class="px-5 py-3 text-left">Người dùng</th>
                        <th class="px-5 py-3 text-left">Liên hệ</th>
                        <th class="px-5 py-3 text-left">Vai trò</th>
                        <th class="px-5 py-3 text-left">Trạng thái</th>
                        <th class="px-5 py-3 text-left whitespace-nowrap">Ngày tạo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $item)
                        <tr onclick="window.openUserDrawer(this)" class="hover:bg-slate-50 cursor-pointer transition-colors"
                            data-id="{{ $item->id }}"
                            data-name="{{ $item->name }}"
                            data-email="{{ $item->email }}"
                            data-phone="{{ $item->phone }}"
                            data-role="{{ $item->role }}"
                            data-status="{{ $item->status }}">
                            <td class="px-5 py-4 font-bold text-slate-800 break-words min-w-0">{{ $item->name }}</td>
                            <td class="px-5 py-4 text-slate-500 font-medium break-words min-w-0">
                                {{ $item->email ?? '—' }}
                                @if($item->phone)
                                    <span class="block text-xs text-slate-400">{{ $item->phone }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if($roleEnum = $item->roleEnum())
                                    <span class="whitespace-nowrap px-2.5 py-1 rounded-full text-[11px] font-bold ring-1 ring-inset {{ $roleEnum->color() }}">
                                        {{ $roleEnum->label() }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if($statusEnum = $item->statusEnum())
                                    <span class="inline-flex whitespace-nowrap items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold ring-1 ring-inset {{ $statusEnum->color() }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $statusEnum->iconColor() }}"></span>
                                        {{ $statusEnum->label() }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-slate-400 font-medium whitespace-nowrap">{{ $item->created_at?->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-slate-400 font-medium">Không có tài khoản nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">{{ $users->links() }}</div>
        @endif
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

        drawerOverlay.classList.remove('hidden');
        requestAnimationFrame(() => {
            drawerOverlay.classList.remove('opacity-0');
            drawerPanel.classList.remove('translate-x-full');
        });
    };

    window.closeUserDrawer = function () {
        drawerOverlay.classList.add('opacity-0');
        drawerPanel.classList.add('translate-x-full');
        setTimeout(() => drawerOverlay.classList.add('hidden'), 300);
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
            html: '<p class="text-sm text-slate-600 break-words">Tài khoản: <b>' + userName + '</b></p>',
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
