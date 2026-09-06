@push('styles')
    <style>
        body.loading-active { overflow: hidden; }

        @keyframes loading-bar {
            0% { transform: translateX(-100%); }
            50% { transform: translateX(0); }
            100% { transform: translateX(100%); }
        }
        .animate-loading-bar { animation: loading-bar 1.5s infinite ease-in-out; }
    </style>
@endpush

<div id="global-loading" class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-white/40 backdrop-blur-md">
    <div class="flex flex-col items-center gap-5">
        <div class="w-16 h-16 rounded-full border-4 border-indigo-500/20 border-t-indigo-600 animate-spin"></div>
        <div class="w-24 h-[2px] bg-indigo-100 overflow-hidden rounded-full">
            <div class="w-full h-full bg-indigo-600 origin-left animate-loading-bar"></div>
        </div>
    </div>
</div>
