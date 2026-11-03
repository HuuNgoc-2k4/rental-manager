@extends('layouts.app')

@section('title', 'Hợp đồng thuê')
@section('subtitle', $isOwner ? 'Xem kỹ hợp đồng chủ nhà gửi, duyệt hoặc yêu cầu sửa.' : 'Hợp đồng thuê phòng của hộ bạn.')

@php
    $status = $document ? \App\Enums\Contract\ContractDocumentStatus::tryFrom($document->status) : null;
    $awaitingOwner = $isOwner && $status === \App\Enums\Contract\ContractDocumentStatus::Pending;
@endphp

@section('content')
    @if(!$household)
        <div class="card">
            @include('partials.empty-state', ['icon' => 'door-closed', 'title' => 'Bạn chưa thuộc phòng nào', 'text' => 'Liên hệ chủ nhà nếu bạn nghĩ đây là nhầm lẫn.'])
        </div>
    @elseif(!$document)
        <div class="card">
            @include('partials.empty-state', [
                'icon' => 'file-clock',
                'title' => 'Chưa có hợp đồng để xem',
                'text' => $isOwner ? 'Chủ nhà đang soạn hợp đồng, bạn sẽ thấy ở đây khi được gửi duyệt.' : 'Hợp đồng sẽ hiện ở đây sau khi chủ hộ duyệt.'
            ])
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">
            <div class="space-y-4 lg:order-2 lg:sticky lg:top-0">
                <div class="card p-4 sm:p-5 space-y-4">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="section-label">Số hợp đồng</p>
                            <p class="text-sm font-bold text-slate-800 break-words mt-1">{{ $document->code }}</p>
                        </div>
                        @if($status)<span class="badge {{ $status->color() }}">{{ $status->label() }}</span>@endif
                    </div>

                    @if($awaitingOwner)
                        <p class="text-xs text-slate-600 font-medium rounded-xl bg-amber-50 p-3 flex gap-2 break-words">
                            <i data-lucide="info" class="w-4 h-4 shrink-0 text-amber-500"></i>
                            Đọc kỹ toàn văn hợp đồng. Bấm "Đồng ý" là bạn xác nhận nội dung thay cho cả hộ; sai sót thì bấm "Yêu cầu sửa".
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-2">
                            <button type="button" class="btn btn-success"
                                    onclick="window.confirmSubmit('contract-approve-form', { title: 'Đồng ý hợp đồng?', html: '<p class=&quot;text-sm text-slate-600 break-words&quot;>Sau khi đồng ý, hợp đồng được khóa và chủ nhà in ra để hai bên ký.</p>', confirmText: 'Đồng ý' })">
                                <i data-lucide="badge-check" class="w-4 h-4"></i> Đồng ý hợp đồng
                            </button>
                            <button type="button" onclick="window.openOverlay('contract-reject-modal')" class="btn btn-danger-soft">
                                <i data-lucide="message-square-warning" class="w-4 h-4"></i> Yêu cầu sửa
                            </button>
                        </div>
                        <form id="contract-approve-form" method="POST" action="{{ route('member.household.contract.approve') }}" class="hidden">
                            @csrf
                            <input type="hidden" name="version" value="{{ $document->updated_at?->timestamp }}">
                        </form>
                    @elseif($status === \App\Enums\Contract\ContractDocumentStatus::Rejected)
                        <div class="rounded-xl bg-rose-50 border border-rose-100 p-3">
                            <p class="text-[11px] font-bold text-rose-700 uppercase tracking-widest">Bạn đã yêu cầu sửa</p>
                            <p class="text-sm font-medium text-rose-800 mt-1 break-words whitespace-pre-line">{{ $document->reject_reason }}</p>
                            <p class="text-xs text-rose-600 font-semibold mt-2">Chờ chủ nhà cập nhật và gửi lại.</p>
                        </div>
                    @elseif($document->approved_at)
                        <p class="text-xs text-slate-600 font-medium rounded-xl bg-emerald-50 p-3 flex gap-2 break-words">
                            <i data-lucide="badge-check" class="w-4 h-4 shrink-0 text-emerald-600"></i>
                            Chủ hộ đã đồng ý lúc {{ $document->approved_at->format('H:i d/m/Y') }}.
                        </p>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-2 lg:order-1 card p-4 sm:p-8 min-w-0">
                @include('contracts.document', ['document' => $document, 'fontFamily' => "'Times New Roman', Times, serif", 'fontSize' => '15px'])
            </div>
        </div>

        @if($awaitingOwner)
            <div id="contract-reject-modal" data-close-fn="closeActiveModal" onclick="if (event.target === this) window.closeOverlay('contract-reject-modal')"
                 class="hidden fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm opacity-0 transition-opacity duration-300 flex items-end sm:items-center justify-center sm:p-4">
                <form method="POST" action="{{ route('member.household.contract.reject') }}" data-panel onsubmit="window.showGlobalLoading()"
                      class="bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-lg max-h-[92dvh] overflow-y-auto scale-95 transition-transform duration-300 pb-safe">
                    @csrf
                    <input type="hidden" name="version" value="{{ $document->updated_at?->timestamp }}">
                    @include('partials.modal-header', ['modalId' => 'contract-reject-modal', 'icon' => 'message-square-warning', 'title' => 'Yêu cầu sửa hợp đồng', 'tone' => 'bg-rose-50 text-rose-600'])
                    <div class="p-5 sm:p-6 space-y-3">
                        <label for="reject-reason" class="form-label">Cần sửa gì?</label>
                        <textarea id="reject-reason" name="reject_reason" rows="4" required maxlength="500" placeholder="VD: Sai ngày sinh của tôi, giá gửi xe đã thỏa thuận là 80.000đ" class="form-input">{{ old('reject_reason') }}</textarea>
                    </div>
                    <div class="px-5 sm:px-6 py-4 border-t border-slate-100 grid grid-cols-2 sm:flex sm:justify-end gap-3">
                        <button type="button" onclick="window.closeOverlay('contract-reject-modal')" class="btn btn-secondary">Đóng</button>
                        <button type="submit" class="btn btn-primary"><i data-lucide="send" class="w-4 h-4"></i> Gửi yêu cầu</button>
                    </div>
                </form>
            </div>
        @endif
    @endif
@endsection
