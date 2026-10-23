@extends('layouts.app')

@section('title', $onBehalf ? 'Khai hộ thông tin' : 'Hồ sơ của tôi')
@section('subtitle', $onBehalf ? 'Bạn đang khai thông tin cho người ở cùng chưa có tài khoản.' : 'Khai báo thông tin cá nhân để chủ nhà xác minh và lập hợp đồng.')

@php
    $inp = 'w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all disabled:opacity-60 disabled:cursor-not-allowed';
    $lbl = 'block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2';
    $status = $tenant ? \App\Enums\Tenant\TenantInfoStatus::tryFrom($tenant->info_status) : null;
    $locked = fn (string $field) => in_array($field, $lockedFields, true);
    $val = fn (string $field, $default = null) => old($field, $tenant?->{$field} instanceof \Carbon\CarbonInterface ? $tenant->{$field}->toDateString() : ($tenant?->{$field} ?? $default));
@endphp

@section('content')
    @if(!$tenant)
        <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center">
            <p class="text-sm font-bold text-slate-700">Tài khoản của bạn chưa có hồ sơ khách thuê</p>
            <p class="text-xs text-slate-500 font-medium mt-1">Liên hệ chủ nhà để được tạo hồ sơ.</p>
        </div>
    @else
        <div class="flex flex-wrap items-center gap-3 mb-4">
            @if($status)
                <span class="whitespace-nowrap px-2.5 py-1 rounded-full text-[11px] font-bold ring-1 ring-inset {{ $status->color() }}">{{ $status->label() }}</span>
            @endif
        </div>

        @if($tenant->info_reject_reason && $tenant->info_status === 'rejected')
            <div class="rounded-xl bg-rose-50 text-rose-700 text-xs font-bold p-4 mb-4 break-words">Chủ nhà trả lại: {{ $tenant->info_reject_reason }}</div>
        @endif
        @if($tenant->isVerified())
            <div class="rounded-xl bg-emerald-50 text-emerald-700 text-xs font-bold p-4 mb-4 break-words">
                Thông tin định danh đã được xác minh nên bị khóa. Muốn đổi họ tên, CCCD, ngày sinh hãy liên hệ chủ nhà.
            </div>
        @endif

        <form method="POST" action="{{ $action }}" enctype="multipart/form-data" onsubmit="window.showGlobalLoading()"
              class="bg-white rounded-2xl border border-slate-200 p-5 md:p-6 w-full max-w-2xl space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="p-full_name" class="{{ $lbl }}">Họ và tên</label>
                <input type="text" id="p-full_name" name="full_name" required maxlength="255" value="{{ $val('full_name') }}" @disabled($locked('full_name')) class="{{ $inp }}">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="p-date_of_birth" class="{{ $lbl }}">Ngày sinh</label>
                    <input type="date" id="p-date_of_birth" name="date_of_birth" value="{{ $val('date_of_birth') }}" @disabled($locked('date_of_birth')) class="{{ $inp }}">
                </div>
                <div>
                    <label for="p-gender" class="{{ $lbl }}">Giới tính</label>
                    <select id="p-gender" name="gender" @disabled($locked('gender')) class="{{ $inp }}">
                        <option value="">—</option>
                        @foreach($genders as $g)<option value="{{ $g->value }}" @selected($val('gender') === $g->value)>{{ $g->label() }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label for="p-id_number" class="{{ $lbl }}">Số CCCD / CMND</label>
                    <input type="text" id="p-id_number" name="id_number" inputmode="numeric" value="{{ $val('id_number') }}" @disabled($locked('id_number')) class="{{ $inp }}">
                </div>
                <div>
                    <label for="p-id_issued_date" class="{{ $lbl }}">Ngày cấp</label>
                    <input type="date" id="p-id_issued_date" name="id_issued_date" value="{{ $val('id_issued_date') }}" @disabled($locked('id_issued_date')) class="{{ $inp }}">
                </div>
            </div>
            <div>
                <label for="p-id_issued_place" class="{{ $lbl }}">Nơi cấp</label>
                <input type="text" id="p-id_issued_place" name="id_issued_place" value="{{ $val('id_issued_place') }}" @disabled($locked('id_issued_place')) class="{{ $inp }}">
            </div>
            <div>
                <label for="p-permanent_address" class="{{ $lbl }}">Địa chỉ thường trú</label>
                <input type="text" id="p-permanent_address" name="permanent_address" value="{{ $val('permanent_address') }}" @disabled($locked('permanent_address')) class="{{ $inp }}">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="p-occupation" class="{{ $lbl }}">Nghề nghiệp</label>
                    <input type="text" id="p-occupation" name="occupation" value="{{ $val('occupation') }}" class="{{ $inp }}">
                </div>
                <div>
                    <label for="p-email" class="{{ $lbl }}">Email</label>
                    <input type="email" id="p-email" name="email" value="{{ $val('email') }}" class="{{ $inp }}">
                </div>
            </div>

            @unless($tenant->isVerified())
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach(['front' => 'Ảnh CCCD mặt trước', 'back' => 'Ảnh CCCD mặt sau'] as $side => $label)
                        <div>
                            <label for="p-id_{{ $side }}" class="{{ $lbl }}">{{ $label }}</label>
                            @if($side === 'front' ? $tenant->id_front_path : $tenant->id_back_path)
                                <a href="{{ route('tenants.id-image', [$tenant, $side]) }}" target="_blank" rel="noopener" class="block mb-2 text-xs font-bold text-indigo-600 underline">Xem ảnh đã tải</a>
                            @endif
                            <input type="file" id="p-id_{{ $side }}" name="id_{{ $side }}" accept="image/*" capture="environment" class="w-full text-xs font-medium text-slate-500">
                        </div>
                    @endforeach
                </div>

                <label class="flex items-start gap-3 text-sm font-medium text-slate-600 min-h-[44px]">
                    <input type="checkbox" name="consent" value="1" class="mt-1 w-5 h-5 rounded">
                    <span class="break-words min-w-0">Tôi đồng ý cho chủ nhà xử lý dữ liệu cá nhân này để lập hợp đồng và đăng ký tạm trú (Nghị định 13/2023/NĐ-CP).</span>
                </label>
            @endunless

            <button type="submit" class="w-full min-h-[44px] bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-md hover:bg-indigo-700 transition-all">
                {{ $tenant->isVerified() ? 'Lưu thông tin liên hệ' : 'Gửi khai báo' }}
            </button>
        </form>
    @endif
@endsection
