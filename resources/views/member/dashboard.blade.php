@extends('layouts.app')

@section('title', 'Phòng của tôi')
@section('subtitle', 'Xin chào ' . $user->name . ', đây là thông tin thuê phòng của bạn.')

@php
    $inp = 'w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all';
    $lbl = 'block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2';
@endphp

@if($household && $household['isOwner'])
    @section('page_actions')
        <button type="button" onclick="window.openOverlay('household-member-modal')"
                class="min-h-[44px] px-5 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-md hover:bg-indigo-700 transition-all flex items-center gap-2">
            <i data-lucide="user-plus" class="w-4 h-4"></i> Thêm người ở cùng
        </button>
    @endsection
@endif

@section('content')
    @if($profile && in_array($profile->info_status, ['draft', 'rejected'], true))
        <a href="{{ route('member.profile.edit') }}" class="block rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm font-bold p-4 mb-6 break-words">
            {{ $profile->info_status === 'rejected' ? 'Khai báo của bạn bị trả lại, bấm để sửa và gửi lại.' : 'Bạn chưa khai báo thông tin cá nhân. Bấm để khai báo ngay.' }}
        </a>
    @endif

    @if(!$household)
        <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center">
            <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-4">
                <i data-lucide="door-closed" class="w-6 h-6 text-slate-400"></i>
            </div>
            <p class="text-sm font-bold text-slate-700">Bạn chưa thuộc phòng nào</p>
            <p class="text-xs text-slate-500 font-medium mt-1 break-words">Liên hệ chủ nhà nếu bạn nghĩ đây là nhầm lẫn.</p>
        </div>
    @else
        @php
            $room = $household['room'];
        @endphp
        <div class="bg-white rounded-2xl border border-slate-200 p-5 mb-6">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $room->property->name }}</p>
            <p class="text-xl font-black text-slate-800 break-words mt-1">Phòng {{ $room->name }}</p>
            <p class="text-xs text-slate-500 font-medium mt-1 break-words">{{ $room->property->address }}</p>
            <div class="grid grid-cols-2 gap-3 mt-4 text-sm">
                <div class="bg-slate-50 rounded-xl p-3"><p class="{{ $lbl }}">Ngày vào ở</p><p class="font-bold text-slate-800">{{ $household['contract']->start_date->format('d/m/Y') }}</p></div>
                <div class="bg-slate-50 rounded-xl p-3"><p class="{{ $lbl }}">Số người tối đa</p><p class="font-bold text-slate-800">{{ $room->max_occupants }}</p></div>
            </div>
        </div>

        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Người trong phòng</p>
        <div class="space-y-3">
            @foreach($household['links'] as $link)
                @php
                    $approval = \App\Enums\Contract\MemberApproval::tryFrom($link->approval_status);
                    $canManage = $household['isOwner'] && !$link->is_primary && $approval?->value === 'approved';
                @endphp
                <div class="bg-white rounded-2xl border border-slate-200 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-slate-800 break-words">
                            {{ $link->tenant->full_name }}
                            @if($link->is_primary)<span class="text-[10px] text-indigo-600 uppercase ml-1">Chủ hộ</span>@endif
                        </p>
                        <p class="text-xs text-slate-500 font-medium break-words">
                            {{ $link->tenant->phone ?? 'Chưa có SĐT' }} ·
                            <span class="font-bold">{{ $approval?->label() }}</span> ·
                            {{ $link->tenant->user_id ? 'Đã có tài khoản' : 'Chưa có tài khoản' }}
                        </p>
                    </div>
                    @if($canManage)
                        <div class="flex gap-2 shrink-0">
                            @if(!$link->tenant->user_id && $link->tenant->phone)
                                <form method="POST" action="{{ route('member.household.members.account', $link->tenant) }}" onsubmit="window.showGlobalLoading()">
                                    @csrf
                                    <button type="submit" class="min-h-[44px] px-4 bg-indigo-50 text-indigo-700 rounded-xl text-sm font-bold hover:bg-indigo-100 transition-all">Cấp tài khoản</button>
                                </form>
                            @elseif($link->tenant->user_id)
                                <form method="POST" action="{{ route('member.household.members.reset-password', $link->tenant) }}" onsubmit="window.showGlobalLoading()">
                                    @csrf
                                    <button type="submit" class="min-h-[44px] px-4 bg-amber-50 text-amber-700 rounded-xl text-sm font-bold hover:bg-amber-100 transition-all">Đặt lại mật khẩu</button>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        @if($household['isOwner'])
            <div id="household-member-modal" data-close-fn="closeActiveModal" onclick="if (event.target === this) window.closeOverlay('household-member-modal')"
                 class="hidden fixed inset-0 z-50 bg-slate-900/40 opacity-0 transition-opacity duration-300 flex items-center justify-center p-4">
                <form method="POST" action="{{ route('member.household.members.store') }}" enctype="multipart/form-data" data-panel onsubmit="window.showGlobalLoading()"
                      class="bg-white rounded-2xl w-full max-w-lg max-h-[90dvh] overflow-y-auto p-5 md:p-6 space-y-4 scale-95 transition-transform duration-300">
                    @csrf
                    <h3 class="text-lg font-black text-slate-800">Thêm người ở cùng</h3>
                    <p class="text-xs text-slate-500 font-medium">Chủ nhà sẽ duyệt trước khi người này được tính vào phòng. Bạn có thể khai giúp thông tin cá nhân.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div><label for="hm-name" class="{{ $lbl }}">Họ và tên</label><input type="text" id="hm-name" name="full_name" required maxlength="255" class="{{ $inp }}"></div>
                        <div><label for="hm-phone" class="{{ $lbl }}">Số điện thoại</label><input type="text" id="hm-phone" name="phone" inputmode="numeric" class="{{ $inp }}"></div>
                        <div><label for="hm-dob" class="{{ $lbl }}">Ngày sinh</label><input type="date" id="hm-dob" name="date_of_birth" class="{{ $inp }}"></div>
                        <div><label for="hm-id" class="{{ $lbl }}">Số CCCD / CMND</label><input type="text" id="hm-id" name="id_number" inputmode="numeric" class="{{ $inp }}"></div>
                    </div>
                    <div><label for="hm-addr" class="{{ $lbl }}">Địa chỉ thường trú</label><input type="text" id="hm-addr" name="permanent_address" class="{{ $inp }}"></div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div><label for="hm-front" class="{{ $lbl }}">Ảnh CCCD mặt trước</label><input type="file" id="hm-front" name="id_front" accept="image/*" capture="environment" class="w-full text-xs font-medium text-slate-500"></div>
                        <div><label for="hm-back" class="{{ $lbl }}">Ảnh CCCD mặt sau</label><input type="file" id="hm-back" name="id_back" accept="image/*" capture="environment" class="w-full text-xs font-medium text-slate-500"></div>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" onclick="window.closeOverlay('household-member-modal')" class="min-h-[44px] px-6 bg-slate-100 text-slate-600 rounded-xl text-sm font-bold hover:bg-slate-200 transition-all">Đóng</button>
                        <button type="submit" class="min-h-[44px] px-6 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-md hover:bg-indigo-700 transition-all">Gửi yêu cầu</button>
                    </div>
                </form>
            </div>
        @endif
    @endif
@endsection
