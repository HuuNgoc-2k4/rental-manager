@extends('layouts.app')

@section('title', 'Khách thuê')
@section('subtitle', 'Hồ sơ cá nhân của khách. Tài khoản đăng nhập chỉ được cấp cho người đã có phòng.')

@section('page_actions')
    <button type="button" onclick="window.openTenantDrawer()"
            class="min-h-[44px] px-5 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-md hover:bg-indigo-700 transition-all flex items-center gap-2">
        <i data-lucide="plus" class="w-4 h-4"></i> Thêm khách
    </button>
@endsection

@php
    $inp = 'w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all';
    $lbl = 'block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2';
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
        'reject_reason' => $t->info_reject_reason
    ]]);
@endphp

@section('content')
    <form method="GET" action="{{ route('manage.tenants.index') }}" class="bg-white rounded-2xl border border-slate-200 p-4 mb-6 flex flex-col md:flex-row gap-3">
        <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="Tìm theo tên, SĐT, CCCD" class="flex-1 {{ $inp }}">
        <select name="info_status" class="{{ $inp }} md:w-56">
            <option value="">Mọi trạng thái khai báo</option>
            @foreach($infoStatuses as $st)
                <option value="{{ $st->value }}" @selected(request('info_status') === $st->value)>{{ $st->label() }}</option>
            @endforeach
        </select>
        <button type="submit" class="min-h-[44px] px-6 bg-slate-800 text-white rounded-xl text-sm font-bold hover:bg-slate-900 transition-all">Lọc</button>
    </form>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                        <th class="px-5 py-3 text-left">Họ tên</th>
                        <th class="px-5 py-3 text-left">Liên hệ</th>
                        <th class="px-5 py-3 text-left">CCCD</th>
                        <th class="px-5 py-3 text-left">Khai báo</th>
                        <th class="px-5 py-3 text-left">Tài khoản</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($tenants as $tenant)
                        @php $st = \App\Enums\Tenant\TenantInfoStatus::tryFrom($tenant->info_status); @endphp
                        <tr onclick="window.openTenantDrawer({{ $tenant->id }})" class="hover:bg-slate-50 cursor-pointer transition-colors">
                            <td class="px-5 py-4 font-bold text-slate-800 break-words min-w-0">{{ $tenant->full_name }}</td>
                            <td class="px-5 py-4 text-slate-500 font-medium break-words min-w-0">{{ $tenant->phone ?? '—' }}</td>
                            <td class="px-5 py-4 text-slate-500 font-medium whitespace-nowrap">{{ $tenant->maskedIdNumber() ?? '—' }}</td>
                            <td class="px-5 py-4">
                                @if($st)
                                    <span class="whitespace-nowrap px-2.5 py-1 rounded-full text-[11px] font-bold ring-1 ring-inset {{ $st->color() }}">{{ $st->label() }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-slate-500 font-medium whitespace-nowrap">{{ $tenant->user_id ? 'Đã cấp' : 'Chưa có' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-12 text-center text-slate-400 font-medium">Chưa có khách nào.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tenants->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">{{ $tenants->links() }}</div>
        @endif
    </div>

    <form id="tenant-delete-form" method="POST" class="hidden">@csrf @method('DELETE')</form>

    <div id="tenant-drawer" data-drawer-overlay data-close-fn="closeActiveDrawer" onclick="if (event.target === this) window.closeOverlay('tenant-drawer')"
         class="hidden fixed inset-0 z-[100] bg-slate-900/40 opacity-0 transition-opacity duration-300">
        <div data-panel class="absolute right-0 top-0 h-full w-full lg:w-[52vw] xl:w-[44vw] bg-white shadow-2xl z-[101] translate-x-full transition-transform duration-300 ease-in-out overflow-y-auto">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white z-10">
                <div class="min-w-0">
                    <p id="tenant-drawer-sub" class="text-[10px] font-black text-slate-400 uppercase tracking-widest"></p>
                    <h3 id="tenant-drawer-title" class="text-lg font-black text-slate-800 break-words min-w-0"></h3>
                </div>
                <button type="button" onclick="window.closeOverlay('tenant-drawer')" aria-label="Đóng" class="w-11 h-11 flex items-center justify-center text-slate-400 hover:text-slate-600 shrink-0">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="p-6 space-y-6">
                <div id="tenant-reject" class="hidden rounded-xl bg-rose-50 text-rose-700 text-xs font-bold p-4 break-words"></div>

                <form id="tenant-form" method="POST" enctype="multipart/form-data" onsubmit="window.showGlobalLoading()" class="space-y-4">
                    @csrf
                    <input type="hidden" name="_method" id="tenant-method" value="POST">
                    <div>
                        <label for="t-full_name" class="{{ $lbl }}">Họ và tên</label>
                        <input type="text" id="t-full_name" name="full_name" required maxlength="255" class="{{ $inp }}">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div><label for="t-phone" class="{{ $lbl }}">Số điện thoại</label><input type="text" id="t-phone" name="phone" inputmode="numeric" class="{{ $inp }}"></div>
                        <div><label for="t-email" class="{{ $lbl }}">Email</label><input type="email" id="t-email" name="email" class="{{ $inp }}"></div>
                        <div><label for="t-date_of_birth" class="{{ $lbl }}">Ngày sinh</label><input type="date" id="t-date_of_birth" name="date_of_birth" class="{{ $inp }}"></div>
                        <div>
                            <label for="t-gender" class="{{ $lbl }}">Giới tính</label>
                            <select id="t-gender" name="gender" class="{{ $inp }}">
                                <option value="">—</option>
                                @foreach($genders as $g)<option value="{{ $g->value }}">{{ $g->label() }}</option>@endforeach
                            </select>
                        </div>
                        <div><label for="t-id_number" class="{{ $lbl }}">Số CCCD / CMND</label><input type="text" id="t-id_number" name="id_number" inputmode="numeric" class="{{ $inp }}"></div>
                        <div><label for="t-id_issued_date" class="{{ $lbl }}">Ngày cấp</label><input type="date" id="t-id_issued_date" name="id_issued_date" class="{{ $inp }}"></div>
                    </div>
                    <div><label for="t-id_issued_place" class="{{ $lbl }}">Nơi cấp</label><input type="text" id="t-id_issued_place" name="id_issued_place" class="{{ $inp }}"></div>
                    <div><label for="t-permanent_address" class="{{ $lbl }}">Địa chỉ thường trú</label><input type="text" id="t-permanent_address" name="permanent_address" class="{{ $inp }}"></div>
                    <div><label for="t-occupation" class="{{ $lbl }}">Nghề nghiệp</label><input type="text" id="t-occupation" name="occupation" class="{{ $inp }}"></div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach(['front' => 'Ảnh CCCD mặt trước', 'back' => 'Ảnh CCCD mặt sau'] as $side => $label)
                            <div>
                                <label for="t-id_{{ $side }}" class="{{ $lbl }}">{{ $label }}</label>
                                <a id="t-{{ $side }}-link" href="#" target="_blank" rel="noopener" class="hidden mb-2 text-xs font-bold text-indigo-600 underline">Xem ảnh hiện tại</a>
                                <input type="file" id="t-id_{{ $side }}" name="id_{{ $side }}" accept="image/*" capture="environment" class="w-full text-xs font-medium text-slate-500">
                            </div>
                        @endforeach
                    </div>

                    <div><label for="t-note" class="{{ $lbl }}">Ghi chú nội bộ (khách không thấy)</label><textarea id="t-note" name="note" rows="2" maxlength="2000" class="{{ $inp }}"></textarea></div>
                    <button type="submit" class="w-full min-h-[44px] bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-md hover:bg-indigo-700 transition-all">Lưu hồ sơ</button>
                </form>

                <div id="tenant-extra"></div>
                <button type="button" id="tenant-delete-btn" onclick="window.deleteTenant()" class="hidden w-full min-h-[44px] bg-rose-50 text-rose-600 rounded-xl text-sm font-bold hover:bg-rose-100 transition-all">Xóa hồ sơ</button>
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

        document.getElementById('tenant-drawer-title').textContent = t ? t.full_name : 'Thêm khách';
        document.getElementById('tenant-drawer-sub').textContent = t ? (t.status_label + (t.has_account ? ' · đã có tài khoản' : '')) : 'Hồ sơ mới';
        document.getElementById('tenant-form').action = t ? tenantUrls.update.replace('__ID__', id) : tenantUrls.store;
        document.getElementById('tenant-method').value = t ? 'PUT' : 'POST';
        tenantFields.forEach(f => { document.getElementById('t-' + f).value = t ? (t[f] ?? '') : ''; });

        ['front', 'back'].forEach(side => {
            const link = document.getElementById('t-' + side + '-link');
            const url = t ? t[side + '_url'] : null;
            link.classList.toggle('hidden', !url);
            if (url) link.href = url;
            document.getElementById('t-id_' + side).value = '';
        });

        const reject = document.getElementById('tenant-reject');
        reject.classList.toggle('hidden', !(t && t.reject_reason));
        reject.textContent = t && t.reject_reason ? 'Lý do trả lại: ' + t.reject_reason : '';
        document.getElementById('tenant-delete-btn').classList.toggle('hidden', !t);

        window.openOverlay('tenant-drawer');
    };

    window.deleteTenant = function () {
        document.getElementById('tenant-delete-form').action = tenantUrls.destroy.replace('__ID__', currentTenantId);
        window.confirmSubmit('tenant-delete-form', { title: 'Xóa hồ sơ khách?', html: '<p class="text-sm text-slate-600 break-words">Chỉ xóa được hồ sơ chưa từng có trong hợp đồng nào.</p>', confirmText: 'Xóa hồ sơ', danger: true });
    };
</script>
@endpush
