@extends('layouts.app')

@section('title', 'Thành viên')
@section('subtitle', 'Người thuê: vai trò trong hộ, phòng đang ở và hồ sơ cá nhân. Việc chờ duyệt nằm ở đầu trang.')

@section('page_actions')
    <button type="button" onclick="window.openTenantDrawer()" class="btn btn-primary w-full sm:w-auto">
        <i data-lucide="user-plus" class="w-4 h-4"></i> Thêm thành viên
    </button>
@endsection

@php
    $tenantData = $tenants->mapWithKeys(fn ($t) => [$t->id => [
        'id' => $t->id,
        'full_name' => $t->full_name,
        'phone' => $t->phone,
        'email' => $t->email,
        'date_of_birth' => $t->date_of_birth?->toDateString(),
        'gender' => $t->gender,
        'id_number' => $t->id_number,
        'id_issued_date' => $t->id_issued_date?->toDateString(),
        'id_issued_place' => $t->id_issued_place,
        'permanent_address' => $t->permanent_address,
        'occupation' => $t->occupation,
        'note' => $t->note,
        'has_account' => (bool) $t->user_id,
        'front_url' => $t->id_front_path ? route('tenants.id-image', [$t, 'front']) : null,
        'back_url' => $t->id_back_path ? route('tenants.id-image', [$t, 'back']) : null,
        'status_label' => \App\Enums\Tenant\TenantInfoStatus::tryFrom($t->info_status)?->label(),
        'status_color' => \App\Enums\Tenant\TenantInfoStatus::tryFrom($t->info_status)?->color(),
        'reject_reason' => $t->info_reject_reason
    ]]);
    // Vai trò suy từ chỗ ở hiện tại (không phải users.role — người chưa có tài khoản vẫn có vai trò): [nhãn, màu badge, icon]
    $roleOf = function ($tenant) {
        $link = $tenant->liveLink;

        return match (true) {
            !$link => ['Chưa ở phòng nào', 'bg-slate-50 text-slate-500 ring-slate-500/20', 'user-x'],
            \App\Enums\Contract\MemberApproval::isPending($link->approval_status) => ['Chờ duyệt vào phòng', 'bg-amber-50 text-amber-700 ring-amber-600/20', 'hourglass'],
            $link->is_primary => ['Chủ hộ', 'bg-indigo-50 text-indigo-700 ring-indigo-600/20', 'crown'],
            default => ['Thành viên', 'bg-sky-50 text-sky-700 ring-sky-600/20', 'user']
        };
    };
    $tabBase = request()->except(['info_status', 'page']);
    $activeStatus = request('info_status', '');
@endphp

@section('content')
    <div class="space-y-5">
        @if($pendingMembers->isNotEmpty() || $declarations->isNotEmpty())
            @include('manage.tenants.partials.approvals')
        @endif

        <div class="card p-4 sm:p-5 space-y-4">
            <form method="GET" action="{{ route('manage.tenants.index') }}" class="flex flex-col sm:flex-row gap-3">
                <input type="hidden" name="info_status" value="{{ $activeStatus }}">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 pointer-events-none"><i data-lucide="search" class="w-5 h-5"></i></span>
                    <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="Tìm theo tên, SĐT, CCCD" class="form-input pl-12">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-dark flex-1 sm:flex-none"><i data-lucide="search" class="w-4 h-4"></i> Tìm</button>
                    <a href="{{ route('manage.tenants.index') }}" class="btn-icon btn-outline border-2" title="Xóa bộ lọc" aria-label="Xóa bộ lọc"><i data-lucide="rotate-ccw" class="w-4 h-4"></i></a>
                </div>
            </form>

            {{-- Tab trạng thái khai báo — cuộn ngang trên mobile --}}
            <div class="-mx-4 sm:mx-0 px-4 sm:px-0 overflow-x-auto">
                <div class="flex gap-2 w-max">
                    <a href="{{ route('manage.tenants.index', $tabBase) }}"
                       class="inline-flex items-center min-h-[40px] px-4 rounded-full text-xs font-bold whitespace-nowrap transition-all {{ $activeStatus === '' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}">Tất cả</a>
                    @foreach($infoStatuses as $st)
                        <a href="{{ route('manage.tenants.index', array_merge($tabBase, ['info_status' => $st->value])) }}"
                           class="inline-flex items-center min-h-[40px] px-4 rounded-full text-xs font-bold whitespace-nowrap transition-all {{ $activeStatus === $st->value ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}">{{ $st->label() }}</a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
                <p class="text-sm font-bold text-slate-800">{{ $tenants->total() }} thành viên</p>
                <p class="text-[11px] font-semibold text-slate-400">Bấm vào thành viên để xem / sửa hồ sơ</p>
            </div>

            {{-- Mobile: danh sách thẻ --}}
            <div class="lg:hidden divide-y divide-slate-100">
                @forelse($tenants as $tenant)
                    @php
                        $st = \App\Enums\Tenant\TenantInfoStatus::tryFrom($tenant->info_status);
                        [$roleLabel, $roleColor, $roleIcon] = $roleOf($tenant);
                        $room = $tenant->liveLink?->contract?->room;
                    @endphp
                    <button type="button" onclick="window.openTenantDrawer({{ $tenant->id }})" class="w-full text-left p-4 flex items-start gap-3 hover:bg-slate-50/70 transition-colors">
                        @include('partials.avatar', ['name' => $tenant->full_name, 'size' => 'w-11 h-11 text-sm'])
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-bold text-slate-800 break-words">{{ $tenant->full_name }}</span>
                            <span class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] font-semibold text-slate-500">
                                <span class="flex items-center gap-1"><i data-lucide="phone" class="w-3 h-3 text-slate-300"></i>{{ $tenant->phone ?? 'Chưa có SĐT' }}</span>
                                @if($tenant->maskedIdNumber())
                                    <span class="flex items-center gap-1 font-mono"><i data-lucide="id-card" class="w-3 h-3 text-slate-300"></i>{{ $tenant->maskedIdNumber() }}</span>
                                @endif
                            </span>
                            <span class="mt-2 flex flex-wrap gap-1.5">
                                <span class="badge {{ $roleColor }}"><i data-lucide="{{ $roleIcon }}" class="w-3 h-3"></i>{{ $roleLabel }}</span>
                                @if($room)
                                    <span class="badge bg-white text-slate-600 ring-slate-300/60"><i data-lucide="door-open" class="w-3 h-3"></i>Phòng {{ $room->name }} · {{ $room->property?->name }}</span>
                                @endif
                                @if($st)<span class="badge {{ $st->color() }}">{{ $st->label() }}</span>@endif
                                @if($tenant->user_id)
                                    <span class="badge bg-emerald-50 text-emerald-700 ring-emerald-600/20"><i data-lucide="shield-check" class="w-3 h-3"></i>Có tài khoản</span>
                                @endif
                            </span>
                        </span>
                        <i data-lucide="chevron-right" class="w-5 h-5 text-slate-300 shrink-0 mt-3"></i>
                    </button>
                @empty
                    @include('partials.empty-state', ['icon' => 'contact', 'title' => 'Chưa có thành viên nào', 'text' => 'Bấm "Thêm thành viên" để tạo hồ sơ đầu tiên.'])
                @endforelse
            </div>

            {{-- Desktop: bảng, mỗi dòng là một thẻ --}}
            <div class="hidden lg:block px-4 pb-2 overflow-x-auto">
                <table class="w-full text-left border-separate border-spacing-y-2">
                    <thead>
                        <tr class="text-[10px] uppercase tracking-widest text-slate-400 font-bold">
                            <th class="py-3 pl-4 pr-4">Thành viên</th>
                            <th class="p-3">Vai trò · Phòng</th>
                            <th class="p-3">Liên hệ</th>
                            <th class="p-3">CCCD</th>
                            <th class="p-3 pr-4">Khai báo · Tài khoản</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tenants as $tenant)
                            @php
                                $st = \App\Enums\Tenant\TenantInfoStatus::tryFrom($tenant->info_status);
                                [$roleLabel, $roleColor, $roleIcon] = $roleOf($tenant);
                                $room = $tenant->liveLink?->contract?->room;
                            @endphp
                            <tr onclick="window.openTenantDrawer({{ $tenant->id }})" class="group cursor-pointer text-sm">
                                <td class="py-3 pl-4 pr-4 bg-white border-y border-l border-slate-100 rounded-l-2xl group-hover:border-indigo-100 group-hover:bg-indigo-50/30 transition-colors">
                                    <div class="flex items-center gap-3">
                                        @include('partials.avatar', ['name' => $tenant->full_name, 'size' => 'w-10 h-10 text-xs'])
                                        <div class="min-w-0">
                                            <p class="font-bold text-slate-800 break-words group-hover:text-indigo-600 transition-colors">{{ $tenant->full_name }}</p>
                                            <p class="text-[11px] font-semibold text-slate-400 break-words">{{ $tenant->occupation ?? '—' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-3 bg-white border-y border-slate-100 group-hover:border-indigo-100 group-hover:bg-indigo-50/30 transition-colors">
                                    <span class="badge {{ $roleColor }}"><i data-lucide="{{ $roleIcon }}" class="w-3 h-3"></i>{{ $roleLabel }}</span>
                                    @if($room)
                                        <a href="{{ route('manage.rooms.show', $room) }}" onclick="event.stopPropagation()" class="block mt-1 text-xs font-bold text-slate-600 hover:text-indigo-600 break-words">Phòng {{ $room->name }} · {{ $room->property?->name }}</a>
                                    @endif
                                </td>
                                <td class="p-3 bg-white border-y border-slate-100 group-hover:border-indigo-100 group-hover:bg-indigo-50/30 transition-colors">
                                    <div class="flex flex-col gap-1 text-xs font-semibold text-slate-500">
                                        <span class="flex items-center gap-2"><i data-lucide="phone" class="w-3.5 h-3.5 text-slate-300 shrink-0"></i>{{ $tenant->phone ?? '—' }}</span>
                                        @if($tenant->email)
                                            <span class="flex items-center gap-2 break-all"><i data-lucide="mail" class="w-3.5 h-3.5 text-slate-300 shrink-0"></i>{{ $tenant->email }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="p-3 bg-white border-y border-slate-100 group-hover:border-indigo-100 group-hover:bg-indigo-50/30 transition-colors text-xs font-mono font-bold text-slate-500 whitespace-nowrap">{{ $tenant->maskedIdNumber() ?? '—' }}</td>
                                <td class="p-3 pr-4 bg-white border-y border-r border-slate-100 rounded-r-2xl group-hover:border-indigo-100 group-hover:bg-indigo-50/30 transition-colors">
                                    <div class="flex flex-wrap gap-1.5">
                                        @if($st)<span class="badge {{ $st->color() }}">{{ $st->label() }}</span>@endif
                                        @if($tenant->user_id)
                                            <span class="badge bg-emerald-50 text-emerald-700 ring-emerald-600/20"><i data-lucide="shield-check" class="w-3 h-3"></i>Có tài khoản</span>
                                        @else
                                            <span class="badge bg-slate-50 text-slate-500 ring-slate-500/20">Chưa có tài khoản</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5">@include('partials.empty-state', ['icon' => 'contact', 'title' => 'Chưa có thành viên nào', 'text' => 'Bấm "Thêm thành viên" để tạo hồ sơ đầu tiên.'])</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($tenants->hasPages())
                <div class="px-5 py-4 border-t border-slate-100">{{ $tenants->links() }}</div>
            @endif
        </div>
    </div>

    <form id="tenant-delete-form" method="POST" class="hidden">@csrf @method('DELETE')</form>

    <div id="tenant-drawer" data-drawer-overlay data-close-fn="closeActiveDrawer" onclick="if (event.target === this) window.closeOverlay('tenant-drawer')"
         class="hidden fixed inset-0 z-[100] bg-slate-900/40 backdrop-blur-sm opacity-0 transition-opacity duration-300">
        <div data-panel class="absolute right-0 top-0 h-full w-full lg:w-[52vw] xl:w-[44vw] bg-slate-50 shadow-2xl z-[101] translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
            <div class="px-5 sm:px-6 py-4 border-b border-slate-200 flex items-center gap-3 bg-white shrink-0">
                <span id="tenant-drawer-avatar" class="shrink-0"></span>
                <div class="min-w-0 flex-1">
                    <p id="tenant-drawer-sub" class="text-[11px] font-bold text-slate-400 uppercase tracking-widest"></p>
                    <h3 id="tenant-drawer-title" class="text-lg font-bold text-slate-900 break-words min-w-0"></h3>
                </div>
                <button type="button" onclick="window.closeOverlay('tenant-drawer')" aria-label="Đóng" class="btn-icon -mr-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form id="tenant-form" method="POST" enctype="multipart/form-data" onsubmit="window.showGlobalLoading()" class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-4">
                @csrf
                <input type="hidden" name="_method" id="tenant-method" value="POST">

                <div id="tenant-reject" class="hidden rounded-2xl bg-rose-50 border border-rose-100 text-rose-700 text-xs font-bold p-4 break-words"></div>

                <div class="card p-4 sm:p-5 space-y-4">
                    <div class="flex items-center gap-2"><i data-lucide="user" class="w-4 h-4 text-indigo-500"></i><p class="section-label">Thông tin cơ bản</p></div>
                    <div>
                        <label for="t-full_name" class="form-label">Họ và tên</label>
                        <input type="text" id="t-full_name" name="full_name" required maxlength="255" class="form-input">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div><label for="t-phone" class="form-label">Số điện thoại</label><input type="text" id="t-phone" name="phone" inputmode="numeric" class="form-input"></div>
                        <div><label for="t-email" class="form-label">Email</label><input type="email" id="t-email" name="email" class="form-input"></div>
                        <div><label for="t-date_of_birth" class="form-label">Ngày sinh</label><input type="date" id="t-date_of_birth" name="date_of_birth" class="form-input"></div>
                        <div>
                            <label for="t-gender" class="form-label">Giới tính</label>
                            <select id="t-gender" name="gender" class="form-input">
                                <option value="">—</option>
                                @foreach($genders as $g)<option value="{{ $g->value }}">{{ $g->label() }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                    <div><label for="t-occupation" class="form-label">Nghề nghiệp</label><input type="text" id="t-occupation" name="occupation" class="form-input"></div>
                </div>

                <div class="card p-4 sm:p-5 space-y-4">
                    <div class="flex items-center gap-2"><i data-lucide="id-card" class="w-4 h-4 text-indigo-500"></i><p class="section-label">Giấy tờ tùy thân</p></div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div><label for="t-id_number" class="form-label">Số CCCD / CMND</label><input type="text" id="t-id_number" name="id_number" inputmode="numeric" class="form-input"></div>
                        <div><label for="t-id_issued_date" class="form-label">Ngày cấp</label><input type="date" id="t-id_issued_date" name="id_issued_date" class="form-input"></div>
                    </div>
                    <div><label for="t-id_issued_place" class="form-label">Nơi cấp</label><input type="text" id="t-id_issued_place" name="id_issued_place" class="form-input"></div>
                    <div><label for="t-permanent_address" class="form-label">Địa chỉ thường trú</label><input type="text" id="t-permanent_address" name="permanent_address" class="form-input"></div>

                    <div class="grid grid-cols-2 gap-3">
                        @foreach(['front' => 'CCCD mặt trước', 'back' => 'CCCD mặt sau'] as $side => $label)
                            {{-- Ảnh riêng tư: src chỉ gán khi mở drawer, không tải sẵn cho mọi dòng --}}
                            <div id="t-{{ $side }}-field">
                                @include('partials.image-input', ['inputId' => 't-id_'.$side, 'name' => 'id_'.$side, 'label' => $label, 'url' => null])
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="card p-4 sm:p-5 space-y-3">
                    <div class="flex items-center gap-2"><i data-lucide="lock" class="w-4 h-4 text-indigo-500"></i><p class="section-label">Ghi chú nội bộ</p></div>
                    <textarea id="t-note" name="note" rows="2" maxlength="2000" placeholder="Khách không thấy ghi chú này" class="form-input"></textarea>
                </div>

            </form>

            <div class="px-4 sm:px-6 py-3 border-t border-slate-200 bg-white shrink-0 flex gap-2 pb-safe">
                <button type="button" id="tenant-delete-btn" onclick="window.deleteTenant()" class="hidden btn-icon btn-danger-soft" aria-label="Xóa hồ sơ" title="Xóa hồ sơ">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
                <button type="button" onclick="window.closeOverlay('tenant-drawer')" class="btn btn-secondary flex-1 sm:flex-none">Đóng</button>
                <button type="submit" form="tenant-form" class="btn btn-primary flex-1 sm:ml-auto"><i data-lucide="save" class="w-4 h-4"></i> Lưu hồ sơ</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const tenants = @json($tenantData);
    const tenantUrls = {
        store: @js(route('manage.tenants.store')),
        update: @js(route('manage.tenants.update', ['tenant' => '__ID__'])),
        destroy: @js(route('manage.tenants.destroy', ['tenant' => '__ID__']))
    };
    const tenantFields = ['full_name', 'phone', 'email', 'date_of_birth', 'gender', 'id_number', 'id_issued_date', 'id_issued_place', 'permanent_address', 'occupation', 'note'];
    let currentTenantId = null;

    /** Không truyền id = thêm khách mới. */
    window.openTenantDrawer = function (id = null) {
        const t = id ? tenants[id] : null;
        currentTenantId = id;

        document.getElementById('tenant-drawer-avatar').innerHTML = window.avatarHtml(t ? t.full_name : '+', 'w-11 h-11 text-sm');
        document.getElementById('tenant-drawer-title').textContent = t ? t.full_name : 'Thêm thành viên';
        document.getElementById('tenant-drawer-sub').textContent = t ? (t.status_label + (t.has_account ? ' · đã có tài khoản' : '')) : 'Hồ sơ mới';
        document.getElementById('tenant-form').action = t ? tenantUrls.update.replace('__ID__', id) : tenantUrls.store;
        document.getElementById('tenant-method').value = t ? 'PUT' : 'POST';
        tenantFields.forEach(f => { document.getElementById('t-' + f).value = t ? (t[f] ?? '') : ''; });

        ['front', 'back'].forEach(side => {
            document.getElementById('t-id_' + side).value = '';
            window.setImageField(document.getElementById('t-' + side + '-field'), t ? t[side + '_url'] : null);
        });

        const reject = document.getElementById('tenant-reject');
        reject.classList.toggle('hidden', !(t && t.reject_reason));
        reject.textContent = t && t.reject_reason ? 'Lý do trả lại: ' + t.reject_reason : '';
        document.getElementById('tenant-delete-btn').classList.toggle('hidden', !t);

        window.openOverlay('tenant-drawer');
    };

    window.deleteTenant = function () {
        document.getElementById('tenant-delete-form').action = tenantUrls.destroy.replace('__ID__', currentTenantId);
        window.confirmSubmit('tenant-delete-form', { title: 'Xóa hồ sơ thành viên?', html: '<p class="text-sm text-slate-600 break-words">Chỉ xóa được hồ sơ chưa từng có trong hợp đồng nào.</p>', confirmText: 'Xóa hồ sơ', danger: true });
    };
</script>
@endpush
