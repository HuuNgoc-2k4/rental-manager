@extends('layouts.app')

@section('title', 'Phòng của tôi')
@section('subtitle', 'Xin chào ' . $user->name . ', đây là thông tin thuê phòng của bạn.')

@if($household && $household['isOwner'])
    @section('page_actions')
        <button type="button" onclick="window.openOverlay('household-member-modal')" class="btn btn-primary w-full sm:w-auto">
            <i data-lucide="user-plus" class="w-4 h-4"></i> Thêm người ở cùng
        </button>
    @endsection
@endif

@section('content')
    <div class="space-y-5">
        @if($profile && in_array($profile->info_status, ['draft', 'rejected'], true))
            @php $isRejected = $profile->info_status === 'rejected'; @endphp
            <a href="{{ route('member.profile.edit') }}"
               class="flex items-center gap-3 rounded-2xl border p-4 transition-colors {{ $isRejected ? 'bg-rose-50 border-rose-200 hover:bg-rose-100/70' : 'bg-amber-50 border-amber-200 hover:bg-amber-100/70' }}">
                <span class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0 {{ $isRejected ? 'bg-rose-100 text-rose-600' : 'bg-amber-100 text-amber-600' }}">
                    <i data-lucide="{{ $isRejected ? 'alert-triangle' : 'file-pen-line' }}" class="w-5 h-5"></i>
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block text-sm font-bold break-words {{ $isRejected ? 'text-rose-800' : 'text-amber-800' }}">
                        {{ $isRejected ? 'Khai báo của bạn bị trả lại' : 'Bạn chưa khai báo thông tin cá nhân' }}
                    </span>
                    <span class="block text-xs font-semibold break-words {{ $isRejected ? 'text-rose-600' : 'text-amber-700' }}">
                        {{ $isRejected ? 'Bấm để sửa và gửi lại cho chủ nhà.' : 'Bấm để khai báo ngay, chủ nhà cần để đăng ký tạm trú.' }}
                    </span>
                </span>
                <i data-lucide="chevron-right" class="w-5 h-5 shrink-0 {{ $isRejected ? 'text-rose-400' : 'text-amber-500' }}"></i>
            </a>
        @endif

        @if(!$household)
            <div class="card p-10 text-center">
                <div class="w-16 h-16 rounded-3xl bg-slate-100 flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="door-closed" class="w-8 h-8 text-slate-400"></i>
                </div>
                <p class="text-base font-bold text-slate-800">Bạn chưa thuộc phòng nào</p>
                <p class="text-sm text-slate-500 font-medium mt-1 break-words">Liên hệ chủ nhà nếu bạn nghĩ đây là nhầm lẫn.</p>
            </div>
        @else
            @php
                $room = $household['room'];
                $residentCount = $household['links']->where('approval_status', \App\Enums\Contract\MemberApproval::Approved->value)->count();
                $profileStatus = $profile ? \App\Enums\Tenant\TenantInfoStatus::tryFrom($profile->info_status) : null;
                // Tầng, diện tích, giá, dịch vụ nằm ở khối thông tin phòng dùng chung bên dưới
                $roomFacts = [['calendar-check', 'Vào ở '.$household['contract']->start_date->format('d/m/Y')]];
            @endphp

            {{-- Thẻ phòng --}}
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-600 via-indigo-600 to-violet-600 text-white p-5 sm:p-8 shadow-xl shadow-indigo-200">
                <div class="absolute -right-16 -top-16 w-64 h-64 rounded-full bg-white/10"></div>
                <div class="absolute right-20 -bottom-24 w-56 h-56 rounded-full bg-white/5"></div>
                <div class="relative">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-[11px] font-bold uppercase tracking-widest text-indigo-200 break-words min-w-0">{{ $room->property->name }}</p>
                        <span class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/15 ring-1 ring-inset ring-white/25 text-[11px] font-bold">
                            <i data-lucide="{{ $household['isOwner'] ? 'crown' : 'user' }}" class="w-3.5 h-3.5"></i>{{ $household['isOwner'] ? 'Bạn là chủ hộ' : 'Thành viên' }}
                        </span>
                    </div>
                    <p class="text-3xl sm:text-4xl font-bold tracking-tight break-words mt-2">Phòng {{ $room->name }}</p>
                    <p class="text-sm text-indigo-100 font-medium mt-2 break-words flex items-start gap-1.5">
                        <i data-lucide="map-pin" class="w-4 h-4 shrink-0 mt-0.5"></i>{{ $room->property->address }}
                    </p>
                    <div class="flex flex-wrap gap-2 mt-5">
                        @foreach($roomFacts as [$icon, $text])
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/15 text-xs font-bold whitespace-nowrap">
                                <i data-lucide="{{ $icon }}" class="w-3.5 h-3.5"></i>{{ $text }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>

            @include('partials.room-info', ['room' => $room, 'document' => $contractDocument, 'residentCount' => $residentCount])

            <a href="{{ route('member.profile.edit') }}" class="card p-4 flex items-center gap-3 hover:border-indigo-200 transition-colors">
                <span class="w-11 h-11 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0"><i data-lucide="id-card" class="w-5 h-5"></i></span>
                <span class="min-w-0 flex-1">
                    <span class="block text-sm font-bold text-slate-800">Hồ sơ của bạn</span>
                    @if($profileStatus)
                        <span class="badge mt-1 {{ $profileStatus->color() }}">{{ $profileStatus->label() }}</span>
                    @endif
                </span>
                <i data-lucide="chevron-right" class="w-5 h-5 shrink-0 text-slate-300"></i>
            </a>

            {{-- Hợp đồng thuê: chủ hộ có bản chờ duyệt thì nổi bật để không bỏ sót --}}
            @php
                $contractStatus = $contractDocument ? \App\Enums\Contract\ContractDocumentStatus::tryFrom($contractDocument->status) : null;
                $contractAwaiting = $household['isOwner'] && $contractStatus === \App\Enums\Contract\ContractDocumentStatus::Pending;
            @endphp
            <a href="{{ route('member.contract.show') }}"
               class="flex items-center gap-3 rounded-2xl border p-4 transition-colors {{ $contractAwaiting ? 'bg-amber-50 border-amber-200 hover:bg-amber-100/70' : 'card hover:border-indigo-200' }}">
                <span class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0 {{ $contractAwaiting ? 'bg-amber-100 text-amber-600' : 'bg-indigo-50 text-indigo-600' }}">
                    <i data-lucide="file-signature" class="w-5 h-5"></i>
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block text-sm font-bold break-words {{ $contractAwaiting ? 'text-amber-800' : 'text-slate-800' }}">
                        {{ $contractAwaiting ? 'Chủ nhà đã gửi hợp đồng, chờ bạn duyệt' : 'Hợp đồng thuê' }}
                    </span>
                    <span class="mt-1 flex flex-wrap items-center gap-1.5 text-xs font-semibold break-words {{ $contractAwaiting ? 'text-amber-700' : 'text-slate-500' }}">
                        @if($contractAwaiting)
                            Bấm để đọc toàn văn và đồng ý hoặc yêu cầu sửa.
                        @elseif($contractStatus)
                            <span class="badge {{ $contractStatus->color() }}">{{ $contractStatus->label() }}</span> Số {{ $contractDocument->code }}
                        @else
                            Chưa có hợp đồng để xem.
                        @endif
                    </span>
                </span>
                <i data-lucide="chevron-right" class="w-5 h-5 shrink-0 {{ $contractAwaiting ? 'text-amber-500' : 'text-slate-300' }}"></i>
            </a>

            {{-- Người trong phòng --}}
            <div class="card overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0"><i data-lucide="users-round" class="w-5 h-5"></i></span>
                    <div class="min-w-0">
                        <h3 class="font-bold text-slate-800">Người trong phòng</h3>
                        <p class="text-[11px] font-semibold text-slate-400">{{ $household['links']->count() }} người, gồm cả người đang chờ duyệt</p>
                    </div>
                </div>
                <div class="divide-y divide-slate-100">
                    @foreach($household['links'] as $link)
                        @php
                            $approval = \App\Enums\Contract\MemberApproval::tryFrom($link->approval_status);
                            $isMe = $link->tenant->user_id === $user->id;
                            $canManage = $household['isOwner'] && !$link->is_primary && $approval === \App\Enums\Contract\MemberApproval::Approved;
                            // Khai hộ: người chưa có tài khoản, do chính chủ hộ thêm, chưa được xác minh (server kiểm tra lại)
                            $canDeclareFor = $household['isOwner'] && !$link->is_primary && !$link->tenant->user_id
                                && $link->created_by === $user->id && !$link->tenant->isVerified();
                        @endphp
                        <div class="p-4 flex flex-col sm:flex-row sm:items-center gap-3">
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                @include('partials.avatar', ['name' => $link->tenant->full_name, 'size' => 'w-11 h-11 text-sm'])
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-slate-800 break-words">
                                        {{ $link->tenant->full_name }}
                                        @if($isMe)<span class="text-slate-400 font-bold">(bạn)</span>@endif
                                    </p>
                                    <div class="mt-1 flex flex-wrap items-center gap-1.5 text-[11px] font-semibold text-slate-500">
                                        <span class="flex items-center gap-1 mr-1"><i data-lucide="phone" class="w-3 h-3 text-slate-300"></i>{{ $link->tenant->phone ?? 'Chưa có SĐT' }}</span>
                                        @if($link->is_primary)<span class="badge bg-indigo-50 text-indigo-700 ring-indigo-600/20"><i data-lucide="crown" class="w-3 h-3"></i>Chủ hộ</span>@endif
                                        @if($approval && !$link->is_primary)<span class="badge {{ $approval->color() }}">{{ $approval->label() }}</span>@endif
                                        @if($link->tenant->user_id)
                                            <span class="badge bg-emerald-50 text-emerald-700 ring-emerald-600/20"><i data-lucide="shield-check" class="w-3 h-3"></i>Có tài khoản</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @if($canManage || $canDeclareFor)
                                <div class="flex flex-wrap gap-2 shrink-0">
                                    @if($canDeclareFor)
                                        <a href="{{ route('member.household.members.edit', $link->tenant) }}" class="btn btn-outline flex-1 sm:flex-none"><i data-lucide="file-pen-line" class="w-4 h-4"></i> Khai hộ</a>
                                    @endif
                                    @if($canManage && !$link->tenant->user_id && $link->tenant->phone)
                                        <form method="POST" action="{{ route('member.household.members.account', $link->tenant) }}" onsubmit="window.showGlobalLoading()" class="flex-1 sm:flex-none">
                                            @csrf
                                            <button type="submit" class="btn btn-soft w-full"><i data-lucide="key-round" class="w-4 h-4"></i> Cấp tài khoản</button>
                                        </form>
                                    @elseif($canManage && $link->tenant->user_id)
                                        <form method="POST" action="{{ route('member.household.members.reset-password', $link->tenant) }}" onsubmit="window.showGlobalLoading()" class="flex-1 sm:flex-none">
                                            @csrf
                                            <button type="submit" class="btn btn-warning-soft w-full"><i data-lucide="refresh-cw" class="w-4 h-4"></i> Đặt lại mật khẩu</button>
                                        </form>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl border-2 border-dashed border-slate-200 p-4 flex items-start gap-3">
                <span class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center shrink-0"><i data-lucide="receipt-text" class="w-5 h-5 text-slate-400"></i></span>
                <div class="min-w-0">
                    <p class="text-sm font-bold text-slate-600">Hóa đơn & công nợ</p>
                    <p class="text-xs text-slate-400 font-medium mt-0.5 break-words">Sắp ra mắt: xem hóa đơn hằng tháng, số còn nợ và ảnh chốt đồng hồ của phòng.</p>
                </div>
            </div>

            @if($household['isOwner'])
                <div id="household-member-modal" data-close-fn="closeActiveModal" onclick="if (event.target === this) window.closeOverlay('household-member-modal')"
                     class="hidden fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm opacity-0 transition-opacity duration-300 flex items-end sm:items-center justify-center sm:p-4">
                    <form method="POST" action="{{ route('member.household.members.store') }}" enctype="multipart/form-data" data-panel onsubmit="window.showGlobalLoading()"
                          class="bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-lg max-h-[92dvh] overflow-y-auto scale-95 transition-transform duration-300 pb-safe">
                        @csrf
                        @include('partials.modal-header', ['modalId' => 'household-member-modal', 'icon' => 'user-plus', 'title' => 'Thêm người ở cùng'])
                        <div class="p-5 sm:p-6 space-y-4">
                            <p class="text-xs text-slate-500 font-medium rounded-xl bg-slate-50 p-3 flex gap-2">
                                <i data-lucide="info" class="w-4 h-4 shrink-0 text-slate-400"></i>
                                Chủ nhà sẽ duyệt trước khi người này được tính vào phòng. Bạn có thể khai giúp thông tin cá nhân.
                            </p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div><label for="hm-name" class="form-label">Họ và tên</label><input type="text" id="hm-name" name="full_name" required maxlength="255" class="form-input"></div>
                                <div><label for="hm-phone" class="form-label">Số điện thoại</label><input type="text" id="hm-phone" name="phone" inputmode="numeric" class="form-input"></div>
                                <div><label for="hm-dob" class="form-label">Ngày sinh</label><input type="date" id="hm-dob" name="date_of_birth" class="form-input"></div>
                                <div><label for="hm-id" class="form-label">Số CCCD / CMND</label><input type="text" id="hm-id" name="id_number" inputmode="numeric" class="form-input"></div>
                            </div>
                            <div><label for="hm-addr" class="form-label">Địa chỉ thường trú</label><input type="text" id="hm-addr" name="permanent_address" class="form-input"></div>
                            <div class="grid grid-cols-2 gap-3">
                                @include('partials.image-input', ['inputId' => 'hm-front', 'name' => 'id_front', 'label' => 'CCCD mặt trước', 'url' => null])
                                @include('partials.image-input', ['inputId' => 'hm-back', 'name' => 'id_back', 'label' => 'CCCD mặt sau', 'url' => null])
                            </div>
                        </div>
                        <div class="px-5 sm:px-6 py-4 border-t border-slate-100 grid grid-cols-2 sm:flex sm:justify-end gap-3">
                            <button type="button" onclick="window.closeOverlay('household-member-modal')" class="btn btn-secondary">Đóng</button>
                            <button type="submit" class="btn btn-primary"><i data-lucide="send" class="w-4 h-4"></i> Gửi yêu cầu</button>
                        </div>
                    </form>
                </div>
            @endif
        @endif
    </div>
@endsection
