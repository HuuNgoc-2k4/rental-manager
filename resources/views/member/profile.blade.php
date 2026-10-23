@extends('layouts.app')

@section('title', $onBehalf ? 'Khai hộ thông tin' : 'Hồ sơ của tôi')
@section('subtitle', $onBehalf ? 'Bạn đang khai thông tin cho người ở cùng chưa có tài khoản.' : 'Khai báo thông tin cá nhân để chủ nhà xác minh và lập hợp đồng.')

@use('App\Enums\Tenant\TenantInfoStatus')

@php
    if ($onBehalf) {
        $breadcrumbs = [
            ['name' => 'Phòng của tôi', 'url' => route('dashboard')],
            ['name' => 'Khai hộ thông tin', 'url' => '#']
        ];
    }
    $status = $tenant ? TenantInfoStatus::tryFrom($tenant->info_status) : null;
    $locked = fn (string $field) => in_array($field, $lockedFields, true);
    $val = fn (string $field, $default = null) => old($field, $tenant?->{$field} instanceof \Carbon\CarbonInterface ? $tenant->{$field}->toDateString() : ($tenant?->{$field} ?? $default));
    // Thanh tiến trình: bước hiện tại theo trạng thái khai báo (bị trả lại = quay về bước 2 màu đỏ)
    $currentStep = match ($status) {
        TenantInfoStatus::Verified => 3,
        TenantInfoStatus::Submitted, TenantInfoStatus::Rejected => 2,
        default => 1
    };
    $steps = [
        [1, 'Khai báo', 'file-pen-line'],
        [2, $status === TenantInfoStatus::Rejected ? 'Bị trả lại' : 'Chờ duyệt', $status === TenantInfoStatus::Rejected ? 'undo-2' : 'hourglass'],
        [3, 'Đã xác minh', 'badge-check']
    ];
    $lockIcon = fn (string $field) => $locked($field) ? '<i data-lucide="lock" class="inline w-3 h-3 ml-1 -mt-0.5"></i>' : '';
@endphp

@section('content')
    @if(!$tenant)
        <div class="card p-10 text-center">
            <div class="w-16 h-16 rounded-3xl bg-slate-100 flex items-center justify-center mx-auto mb-4">
                <i data-lucide="user-x" class="w-8 h-8 text-slate-400"></i>
            </div>
            <p class="text-base font-bold text-slate-800">Tài khoản của bạn chưa có hồ sơ khách thuê</p>
            <p class="text-sm text-slate-500 font-medium mt-1">Liên hệ chủ nhà để được tạo hồ sơ.</p>
        </div>
    @else
        <div class="max-w-3xl space-y-4">
            {{-- Tiến trình khai báo --}}
            <div class="card p-4 sm:p-5">
                <div class="flex items-center">
                    @foreach($steps as [$number, $label, $icon])
                        @php
                            $isDone = $number < $currentStep || ($number === 3 && $currentStep === 3);
                            $isCurrent = $number === $currentStep && !$isDone;
                            $isRejectedStep = $isCurrent && $status === TenantInfoStatus::Rejected;
                            $circle = $isDone ? 'bg-emerald-500 text-white' : ($isRejectedStep ? 'bg-rose-500 text-white' : ($isCurrent ? 'bg-indigo-600 text-white ring-4 ring-indigo-100' : 'bg-slate-100 text-slate-400'));
                        @endphp
                        <div class="flex flex-col items-center gap-1.5 shrink-0 w-20 sm:w-24">
                            <span class="w-10 h-10 rounded-full flex items-center justify-center {{ $circle }}">
                                <i data-lucide="{{ $isDone ? 'check' : $icon }}" class="w-5 h-5"></i>
                            </span>
                            <span class="text-[11px] font-bold text-center leading-tight {{ $isDone || $isCurrent ? 'text-slate-800' : 'text-slate-400' }}">{{ $label }}</span>
                        </div>
                        @if(!$loop->last)
                            <span class="flex-1 h-1 rounded-full -mt-6 {{ $number < $currentStep ? 'bg-emerald-400' : 'bg-slate-100' }}"></span>
                        @endif
                    @endforeach
                </div>
            </div>

            @if($tenant->info_reject_reason && $status === TenantInfoStatus::Rejected)
                <div class="rounded-2xl bg-rose-50 border border-rose-100 p-4 flex gap-3">
                    <i data-lucide="message-square-warning" class="w-5 h-5 text-rose-500 shrink-0"></i>
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-rose-700 uppercase tracking-wider">Chủ nhà trả lại</p>
                        <p class="text-sm font-semibold text-rose-700 break-words mt-0.5">{{ $tenant->info_reject_reason }}</p>
                    </div>
                </div>
            @endif
            @if($tenant->isVerified())
                <div class="rounded-2xl bg-emerald-50 border border-emerald-100 p-4 flex gap-3">
                    <i data-lucide="shield-check" class="w-5 h-5 text-emerald-600 shrink-0"></i>
                    <p class="text-sm font-semibold text-emerald-800 break-words">
                        Thông tin định danh đã được xác minh nên bị khóa. Muốn đổi họ tên, CCCD, ngày sinh hãy liên hệ chủ nhà.
                    </p>
                </div>
            @endif

            <form method="POST" action="{{ $action }}" enctype="multipart/form-data" onsubmit="window.showGlobalLoading()" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="card p-4 sm:p-6 space-y-4">
                    <div class="flex items-center gap-2"><i data-lucide="user" class="w-4 h-4 text-indigo-500"></i><p class="section-label">Thông tin cá nhân</p></div>
                    <div>
                        <label for="p-full_name" class="form-label">Họ và tên{!! $lockIcon('full_name') !!}</label>
                        <input type="text" id="p-full_name" name="full_name" required maxlength="255" value="{{ $val('full_name') }}" @disabled($locked('full_name')) class="form-input">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="p-date_of_birth" class="form-label">Ngày sinh{!! $lockIcon('date_of_birth') !!}</label>
                            <input type="date" id="p-date_of_birth" name="date_of_birth" value="{{ $val('date_of_birth') }}" @disabled($locked('date_of_birth')) class="form-input">
                        </div>
                        <div>
                            <label for="p-gender" class="form-label">Giới tính{!! $lockIcon('gender') !!}</label>
                            <select id="p-gender" name="gender" @disabled($locked('gender')) class="form-input">
                                <option value="">—</option>
                                @foreach($genders as $g)<option value="{{ $g->value }}" @selected($val('gender') === $g->value)>{{ $g->label() }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label for="p-occupation" class="form-label">Nghề nghiệp</label>
                        <input type="text" id="p-occupation" name="occupation" value="{{ $val('occupation') }}" class="form-input">
                    </div>
                </div>

                <div class="card p-4 sm:p-6 space-y-4">
                    <div class="flex items-center gap-2"><i data-lucide="id-card" class="w-4 h-4 text-indigo-500"></i><p class="section-label">Giấy tờ tùy thân</p></div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="p-id_number" class="form-label">Số CCCD / CMND{!! $lockIcon('id_number') !!}</label>
                            <input type="text" id="p-id_number" name="id_number" inputmode="numeric" value="{{ $val('id_number') }}" @disabled($locked('id_number')) class="form-input">
                        </div>
                        <div>
                            <label for="p-id_issued_date" class="form-label">Ngày cấp{!! $lockIcon('id_issued_date') !!}</label>
                            <input type="date" id="p-id_issued_date" name="id_issued_date" value="{{ $val('id_issued_date') }}" @disabled($locked('id_issued_date')) class="form-input">
                        </div>
                    </div>
                    <div>
                        <label for="p-id_issued_place" class="form-label">Nơi cấp{!! $lockIcon('id_issued_place') !!}</label>
                        <input type="text" id="p-id_issued_place" name="id_issued_place" value="{{ $val('id_issued_place') }}" @disabled($locked('id_issued_place')) class="form-input">
                    </div>
                    <div>
                        <label for="p-permanent_address" class="form-label">Địa chỉ thường trú{!! $lockIcon('permanent_address') !!}</label>
                        <input type="text" id="p-permanent_address" name="permanent_address" value="{{ $val('permanent_address') }}" @disabled($locked('permanent_address')) class="form-input">
                    </div>

                    @unless($tenant->isVerified())
                        <div class="grid grid-cols-2 gap-3">
                            @foreach(['front' => 'CCCD mặt trước', 'back' => 'CCCD mặt sau'] as $side => $label)
                                @include('partials.image-input', [
                                    'inputId' => 'p-id_'.$side,
                                    'name' => 'id_'.$side,
                                    'label' => $label,
                                    'url' => ($side === 'front' ? $tenant->id_front_path : $tenant->id_back_path) ? route('tenants.id-image', [$tenant, $side]) : null
                                ])
                            @endforeach
                        </div>
                    @endunless
                </div>

                <div class="card p-4 sm:p-6 space-y-4">
                    <div class="flex items-center gap-2"><i data-lucide="mail" class="w-4 h-4 text-indigo-500"></i><p class="section-label">Liên hệ</p></div>
                    <div>
                        <label for="p-email" class="form-label">Email</label>
                        <input type="email" id="p-email" name="email" value="{{ $val('email') }}" placeholder="ban@example.com" class="form-input">
                    </div>
                </div>

                @unless($tenant->isVerified())
                    <label class="flex items-start gap-3 rounded-2xl border-2 border-indigo-100 bg-indigo-50/50 p-4 text-sm font-medium text-slate-600 cursor-pointer">
                        <input type="checkbox" name="consent" value="1" class="mt-0.5 w-5 h-5 rounded accent-indigo-600 shrink-0">
                        <span class="break-words min-w-0">Tôi đồng ý cho chủ nhà xử lý dữ liệu cá nhân này để lập hợp đồng và đăng ký tạm trú (Nghị định 13/2023/NĐ-CP).</span>
                    </label>
                @endunless

                <button type="submit" class="btn btn-primary w-full min-h-[52px] text-base">
                    <i data-lucide="{{ $tenant->isVerified() ? 'save' : 'send' }}" class="w-5 h-5"></i>
                    {{ $tenant->isVerified() ? 'Lưu thông tin liên hệ' : 'Gửi khai báo' }}
                </button>
            </form>
        </div>
    @endif
@endsection
