{{--
    Việc chờ chủ nhà duyệt, đặt đầu trang Thành viên: người ở cùng do chủ hộ đề xuất + khai báo thông tin cá nhân.

    @param \Illuminate\Support\Collection $pendingMembers
    @param \Illuminate\Support\Collection $declarations
--}}
<div id="approvals" class="grid grid-cols-1 xl:grid-cols-2 gap-6 items-start">
    {{-- Thành viên chờ duyệt --}}
    <section class="space-y-3">
        <div class="flex items-center gap-3">
            <span class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0"><i data-lucide="user-plus" class="w-5 h-5"></i></span>
            <div class="min-w-0 flex-1">
                <h2 class="font-bold text-slate-800">Thành viên chờ duyệt</h2>
                <p class="text-[11px] font-semibold text-slate-400">Chủ hộ xin thêm người ở cùng phòng</p>
            </div>
            <span class="min-w-[28px] h-7 px-2 rounded-full bg-amber-100 text-amber-700 text-xs font-bold flex items-center justify-center tabular-nums">{{ $pendingMembers->count() }}</span>
        </div>

        @forelse($pendingMembers as $link)
            <div class="card p-4">
                <div class="flex items-start gap-3">
                    @include('partials.avatar', ['name' => $link->tenant->full_name, 'size' => 'w-11 h-11 text-sm'])
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-slate-800 break-words">{{ $link->tenant->full_name }}</p>
                        <div class="mt-1 flex flex-wrap gap-x-3 gap-y-1 text-[11px] font-semibold text-slate-500">
                            <span class="flex items-center gap-1"><i data-lucide="phone" class="w-3 h-3 text-slate-300"></i>{{ $link->tenant->phone ?? 'Chưa có SĐT' }}</span>
                            <span class="flex items-center gap-1"><i data-lucide="calendar" class="w-3 h-3 text-slate-300"></i>Đề xuất {{ $link->created_at?->format('d/m/Y') }}</span>
                        </div>
                        <a href="{{ route('manage.rooms.show', $link->contract->room) }}" class="mt-2 inline-flex items-center gap-1.5 min-h-[32px] px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 text-[11px] font-bold break-words hover:bg-indigo-100">
                            <i data-lucide="door-open" class="w-3.5 h-3.5 shrink-0"></i>Phòng {{ $link->contract->room->name }} · {{ $link->contract->room->property->name }}
                        </a>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2 mt-4">
                    <button type="button" onclick="window.rejectMember({{ $link->id }}, @js($link->tenant->full_name))" class="btn btn-secondary">
                        <i data-lucide="x" class="w-4 h-4"></i> Từ chối
                    </button>
                    <form method="POST" action="{{ route('manage.approvals.members.approve', $link) }}" onsubmit="window.showGlobalLoading()">
                        @csrf
                        <button type="submit" class="btn btn-success w-full"><i data-lucide="check" class="w-4 h-4"></i> Duyệt</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="card">
                @include('partials.empty-state', ['icon' => 'check-check', 'title' => 'Không có yêu cầu nào đang chờ', 'text' => null])
            </div>
        @endforelse
    </section>

    {{-- Khai báo thông tin chờ duyệt --}}
    <section class="space-y-3">
        <div class="flex items-center gap-3">
            <span class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0"><i data-lucide="id-card" class="w-5 h-5"></i></span>
            <div class="min-w-0 flex-1">
                <h2 class="font-bold text-slate-800">Khai báo thông tin chờ duyệt</h2>
                <p class="text-[11px] font-semibold text-slate-400">Đối chiếu với ảnh CCCD trước khi xác minh</p>
            </div>
            <span class="min-w-[28px] h-7 px-2 rounded-full bg-sky-100 text-sky-700 text-xs font-bold flex items-center justify-center tabular-nums">{{ $declarations->count() }}</span>
        </div>

        @forelse($declarations as $tenant)
            @php
                $facts = [
                    ['id-card', 'CCCD', $tenant->id_number],
                    ['cake', 'Ngày sinh', $tenant->date_of_birth?->format('d/m/Y')],
                    ['phone', 'Điện thoại', $tenant->phone],
                    ['map-pin', 'Thường trú', $tenant->permanent_address]
                ];
                $images = array_filter([
                    'front' => $tenant->id_front_path ? 'Mặt trước' : null,
                    'back' => $tenant->id_back_path ? 'Mặt sau' : null
                ]);
            @endphp
            <div class="card p-4">
                <div class="flex items-start gap-3">
                    @include('partials.avatar', ['name' => $tenant->full_name, 'size' => 'w-11 h-11 text-sm'])
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-slate-800 break-words">{{ $tenant->full_name }}</p>
                        <p class="text-[11px] font-semibold text-slate-400">Gửi lúc {{ $tenant->updated_at?->format('H:i d/m/Y') }}</p>
                    </div>
                </div>

                <dl class="grid grid-cols-2 gap-2 mt-4">
                    @foreach($facts as [$icon, $label, $value])
                        <div class="rounded-xl bg-slate-50 px-3 py-2.5 min-w-0 {{ $loop->last ? 'col-span-2' : '' }}">
                            <dt class="flex items-center gap-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest"><i data-lucide="{{ $icon }}" class="w-3 h-3"></i>{{ $label }}</dt>
                            <dd class="text-sm font-bold text-slate-800 break-words mt-0.5">{{ $value ?? '—' }}</dd>
                        </div>
                    @endforeach
                </dl>

                @if($images)
                    <div class="grid grid-cols-2 gap-2 mt-3">
                        @foreach($images as $side => $label)
                            <a href="{{ route('tenants.id-image', [$tenant, $side]) }}" target="_blank" rel="noopener" class="group relative block rounded-xl overflow-hidden border border-slate-200 bg-slate-100 aspect-[3/2]">
                                <img src="{{ route('tenants.id-image', [$tenant, $side]) }}" loading="lazy" alt="CCCD {{ $label }}" class="w-full h-full object-cover">
                                <span class="absolute left-2 bottom-2 px-2 py-0.5 rounded-md bg-slate-900/70 text-white text-[10px] font-bold">{{ $label }}</span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="mt-3 rounded-xl bg-amber-50 text-amber-700 text-xs font-bold px-3 py-2.5 flex items-center gap-2"><i data-lucide="image-off" class="w-4 h-4 shrink-0"></i>Chưa tải ảnh CCCD</p>
                @endif

                <div class="grid grid-cols-2 gap-2 mt-4">
                    <button type="button" onclick="window.rejectMember({{ $tenant->id }}, @js($tenant->full_name), 'declaration')" class="btn btn-secondary">
                        <i data-lucide="undo-2" class="w-4 h-4"></i> Trả lại
                    </button>
                    <form method="POST" action="{{ route('manage.approvals.declarations.verify', $tenant) }}" onsubmit="window.showGlobalLoading()">
                        @csrf
                        <button type="submit" class="btn btn-success w-full"><i data-lucide="badge-check" class="w-4 h-4"></i> Duyệt</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="card">
                @include('partials.empty-state', ['icon' => 'check-check', 'title' => 'Không có khai báo nào đang chờ', 'text' => null])
            </div>
        @endforelse
    </section>
</div>

<form id="reject-form" method="POST" class="hidden">@csrf<input type="hidden" name="reason"></form>

@push('scripts')
<script>
    const rejectUrls = {
        member: @js(route('manage.approvals.members.reject', ['link' => '__ID__'])),
        declaration: @js(route('manage.approvals.declarations.return', ['tenant' => '__ID__']))
    };

    /** Từ chối bắt buộc có lý do — hỏi bằng Swal input rồi submit form ẩn. */
    window.rejectMember = function (id, name, kind = 'member') {
        window.swalDanger.fire({
            icon: 'warning',
            iconColor: '#e11d48',
            titleText: (kind === 'declaration' ? 'Trả lại khai báo của ' : 'Từ chối ') + name + '?',
            input: 'textarea',
            inputPlaceholder: 'Lý do từ chối (bắt buộc)',
            inputValidator: (v) => !v || !v.trim() ? 'Vui lòng nhập lý do.' : null,
            showCancelButton: true,
            confirmButtonText: 'Từ chối',
            cancelButtonText: 'Hủy'
        }).then(r => {
            if (!r.isConfirmed) return;
            const form = document.getElementById('reject-form');
            form.action = rejectUrls[kind].replace('__ID__', id);
            form.querySelector('[name="reason"]').value = r.value;
            window.showGlobalLoading();
            form.submit();
        });
    };
</script>
@endpush
