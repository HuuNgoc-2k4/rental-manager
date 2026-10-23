@if(isset($breadcrumbs) && count($breadcrumbs) > 0)
    <nav class="mb-3 sm:mb-4" aria-label="Breadcrumb">
        {{-- Điện thoại: chỉ hiện 1 nút quay lại mục cha, chuỗi đầy đủ chữ hoa không vừa 375px --}}
        @php $parentCrumb = count($breadcrumbs) > 1 ? $breadcrumbs[count($breadcrumbs) - 2] : null; @endphp
        @if($parentCrumb)
            <a href="{{ $parentCrumb['url'] }}"
               class="sm:hidden inline-flex items-center gap-1 min-h-[44px] -my-2 text-xs font-bold text-indigo-600 uppercase tracking-wider">
                <i data-lucide="chevron-left" class="w-4 h-4 shrink-0"></i>
                <span class="break-words">{{ $parentCrumb['name'] }}</span>
            </a>
        @endif

        <ol class="hidden sm:flex flex-wrap items-center gap-y-1">
            @foreach($breadcrumbs as $breadcrumb)
                <li class="inline-flex items-center min-w-0">
                    @if(!$loop->first)
                        <i data-lucide="chevron-right" class="w-4 h-4 text-slate-300 mx-1 shrink-0"></i>
                    @endif

                    @if($loop->last)
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider break-words">{{ $breadcrumb['name'] }}</span>
                    @else
                        <a href="{{ $breadcrumb['url'] }}" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-700 uppercase tracking-wider transition-colors break-words">
                            {{ $breadcrumb['name'] }}
                        </a>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
