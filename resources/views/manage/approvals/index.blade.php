@extends('layouts.app')

@section('title', 'Chờ duyệt')
@section('subtitle', 'Người ở cùng do chủ hộ đề xuất cần chủ nhà duyệt.')

@section('content')
    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Thành viên chờ duyệt ({{ $pendingMembers->count() }})</p>

    @forelse($pendingMembers as $link)
        <div class="bg-white rounded-2xl border border-slate-200 p-4 mb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-sm font-bold text-slate-800 break-words">{{ $link->tenant->full_name }}</p>
                <p class="text-xs text-slate-500 font-medium break-words">
                    {{ $link->tenant->phone ?? 'Chưa có SĐT' }} · Phòng {{ $link->contract->room->name }} – {{ $link->contract->room->property->name }}
                    · đề xuất {{ $link->created_at?->format('d/m/Y') }}
                </p>
            </div>
            <div class="flex gap-2 shrink-0">
                <form method="POST" action="{{ route('manage.approvals.members.approve', $link) }}" onsubmit="window.showGlobalLoading()">
                    @csrf
                    <button type="submit" class="min-h-[44px] px-5 bg-emerald-600 text-white rounded-xl text-sm font-bold hover:bg-emerald-700 transition-all">Duyệt</button>
                </form>
                <button type="button" onclick="window.rejectMember({{ $link->id }}, @js($link->tenant->full_name))"
                        class="min-h-[44px] px-5 bg-slate-100 text-slate-600 rounded-xl text-sm font-bold hover:bg-slate-200 transition-all">Từ chối</button>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center text-sm font-medium text-slate-400">Không có yêu cầu nào đang chờ.</div>
    @endforelse

    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-8 mb-3">Khai báo thông tin chờ duyệt ({{ $declarations->count() }})</p>

    @forelse($declarations as $tenant)
        <div class="bg-white rounded-2xl border border-slate-200 p-4 mb-3">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                <div class="min-w-0 text-sm">
                    <p class="font-bold text-slate-800 break-words">{{ $tenant->full_name }}</p>
                    <p class="text-xs text-slate-500 font-medium break-words mt-1">
                        CCCD {{ $tenant->id_number }} · Sinh {{ $tenant->date_of_birth?->format('d/m/Y') }} · {{ $tenant->phone ?? 'Chưa có SĐT' }}
                    </p>
                    <p class="text-xs text-slate-500 font-medium break-words">{{ $tenant->permanent_address }}</p>
                    <div class="flex gap-4 mt-2">
                        @foreach(['front' => 'Mặt trước', 'back' => 'Mặt sau'] as $side => $label)
                            @if($side === 'front' ? $tenant->id_front_path : $tenant->id_back_path)
                                <a href="{{ route('tenants.id-image', [$tenant, $side]) }}" target="_blank" rel="noopener" class="text-xs font-bold text-indigo-600 underline">{{ $label }}</a>
                            @endif
                        @endforeach
                    </div>
                </div>
                <div class="flex gap-2 shrink-0">
                    <form method="POST" action="{{ route('manage.approvals.declarations.verify', $tenant) }}" onsubmit="window.showGlobalLoading()">
                        @csrf
                        <button type="submit" class="min-h-[44px] px-5 bg-emerald-600 text-white rounded-xl text-sm font-bold hover:bg-emerald-700 transition-all">Duyệt</button>
                    </form>
                    <button type="button" onclick="window.rejectMember({{ $tenant->id }}, @js($tenant->full_name), 'declaration')"
                            class="min-h-[44px] px-5 bg-slate-100 text-slate-600 rounded-xl text-sm font-bold hover:bg-slate-200 transition-all">Trả lại</button>
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center text-sm font-medium text-slate-400">Không có khai báo nào đang chờ.</div>
    @endforelse

    <form id="reject-form" method="POST" class="hidden">@csrf<input type="hidden" name="reason"></form>
@endsection

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
            title: (kind === 'declaration' ? 'Trả lại khai báo của ' : 'Từ chối ') + name + '?',
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
