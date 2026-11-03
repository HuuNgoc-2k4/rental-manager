@extends('layouts.app')

@php
    $status = \App\Enums\Contract\ContractDocumentStatus::tryFrom($document->status);
    $canSubmit = in_array($document->status, ['draft', 'rejected'], true);
    $breadcrumbs = [
        ['name' => 'Phòng trọ', 'url' => route('manage.rooms.index')],
        ['name' => 'Phòng '.$document->room_name, 'url' => route('manage.rooms.show', $document->contract->room_id)],
        ['name' => 'Hợp đồng', 'url' => '#']
    ];
    $timeline = array_filter([
        ['file-plus-2', 'Lập lúc', $document->created_at],
        $document->submitted_at ? ['send', 'Gửi chủ hộ lúc', $document->submitted_at] : null,
        $document->approved_at ? ['badge-check', 'Chủ hộ duyệt lúc', $document->approved_at] : null
    ]);
@endphp

@section('title', 'Hợp đồng phòng '.$document->room_name)
@section('subtitle', 'Số '.$document->code.' · Bên thuê: '.($document->tenant['full_name'] ?? '—'))

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">
        {{-- Trạng thái + thao tác: lên đầu trên mobile, cột phải trên desktop --}}
        <div class="space-y-4 lg:order-2 lg:sticky lg:top-0">
            <div class="card p-4 sm:p-5 space-y-4">
                <div class="flex items-center justify-between gap-3">
                    <p class="section-label">Trạng thái</p>
                    @if($status)<span class="badge {{ $status->color() }}">{{ $status->label() }}</span>@endif
                </div>

                @if($status === \App\Enums\Contract\ContractDocumentStatus::Rejected && $document->reject_reason)
                    <div class="rounded-xl bg-rose-50 border border-rose-100 p-3">
                        <p class="text-[11px] font-bold text-rose-700 uppercase tracking-widest">Chủ hộ yêu cầu sửa</p>
                        <p class="text-sm font-medium text-rose-800 mt-1 break-words whitespace-pre-line">{{ $document->reject_reason }}</p>
                    </div>
                @elseif($status === \App\Enums\Contract\ContractDocumentStatus::Pending)
                    <p class="text-xs text-slate-500 font-medium rounded-xl bg-amber-50 p-3 flex gap-2 break-words">
                        <i data-lucide="hourglass" class="w-4 h-4 shrink-0 text-amber-500"></i>
                        Đang chờ chủ hộ xem và duyệt. Nếu sửa lúc này, hợp đồng về bản nháp và phải gửi lại.
                    </p>
                @elseif($status === \App\Enums\Contract\ContractDocumentStatus::Approved)
                    <p class="text-xs text-slate-500 font-medium rounded-xl bg-emerald-50 p-3 flex gap-2 break-words">
                        <i data-lucide="lock" class="w-4 h-4 shrink-0 text-emerald-600"></i>
                        Chủ hộ đã duyệt, hợp đồng đã khóa. Thành viên trong phòng xem được bản này.
                    </p>
                @endif

                <ul class="space-y-2">
                    @foreach($timeline as [$icon, $label, $time])
                        <li class="flex items-center gap-2 text-xs font-semibold text-slate-500">
                            <i data-lucide="{{ $icon }}" class="w-4 h-4 text-slate-300 shrink-0"></i>
                            <span class="min-w-0 break-words">{{ $label }} <b class="text-slate-700">{{ $time->format('H:i d/m/Y') }}</b></span>
                        </li>
                    @endforeach
                </ul>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-2">
                    @if($document->isApproved())
                        <a href="{{ route('manage.contracts.documents.pdf', $document) }}" class="btn btn-primary"><i data-lucide="file-down" class="w-4 h-4"></i> Tải PDF</a>
                        <a href="{{ route('manage.contracts.documents.word', $document) }}" class="btn btn-soft"><i data-lucide="file-type" class="w-4 h-4"></i> Tải Word</a>
                    @else
                        @if($canSubmit)
                            <button type="button" class="btn btn-primary"
                                    onclick="window.confirmSubmit('document-submit-form', { title: 'Gửi chủ hộ duyệt?', html: '<p class=&quot;text-sm text-slate-600 break-words&quot;>Chủ hộ sẽ thấy toàn văn hợp đồng ở trang Hợp đồng của họ.</p>', confirmText: 'Gửi duyệt' })">
                                <i data-lucide="send" class="w-4 h-4"></i> Gửi chủ hộ duyệt
                            </button>
                        @endif
                        <a href="{{ route('manage.contracts.documents.edit', $document) }}" class="btn btn-soft"><i data-lucide="pencil" class="w-4 h-4"></i> Sửa nội dung</a>
                        <button type="button" class="btn btn-outline"
                                onclick="window.confirmSubmit('document-refresh-form', { title: 'Cập nhật theo hồ sơ?', html: '<p class=&quot;text-sm text-slate-600 break-words&quot;>Lấy lại Bên B, người ở cùng và thông tin phòng từ dữ liệu hiện tại. Giá, dịch vụ và điều khoản bạn đã sửa được giữ nguyên.</p>', confirmText: 'Cập nhật' })">
                            <i data-lucide="refresh-cw" class="w-4 h-4"></i> Cập nhật theo hồ sơ
                        </button>
                    @endif
                </div>
            </div>

            <form id="document-submit-form" method="POST" action="{{ route('manage.contracts.documents.submit', $document) }}" class="hidden">@csrf</form>
            <form id="document-refresh-form" method="POST" action="{{ route('manage.contracts.documents.refresh', $document) }}" class="hidden">@csrf</form>
        </div>

        {{-- Toàn văn hợp đồng --}}
        <div class="lg:col-span-2 lg:order-1 card p-4 sm:p-8 min-w-0">
            @include('contracts.document', ['document' => $document, 'fontFamily' => "'Times New Roman', Times, serif", 'fontSize' => '15px'])
        </div>
    </div>
@endsection
