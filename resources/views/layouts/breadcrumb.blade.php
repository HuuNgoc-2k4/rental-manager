@if(isset($breadcrumbs) && count($breadcrumbs) > 0)
    <nav class="flex mb-4" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            @foreach($breadcrumbs as $breadcrumb)
                <li class="inline-flex items-center">
                    @if(!$loop->first)
                        <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400 mx-1"></i>
                    @endif

                    @if($loop->last)
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ $breadcrumb['name'] }}</span>
                    @else
                        <a href="{{ $breadcrumb['url'] }}" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-700 uppercase tracking-wider transition-colors">
                            {{ $breadcrumb['name'] }}
                        </a>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
