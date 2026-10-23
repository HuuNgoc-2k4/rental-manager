<!DOCTYPE html>
<html lang="vi" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    {{-- viewport-fit=cover: cần để env(safe-area-inset-bottom) có giá trị, thanh điều hướng đáy không bị thanh Home của iPhone đè --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#4f46e5">

    <title>@yield('title', 'Quản lý nhà cho thuê')</title>

    <script src="https://cdn.tailwindcss.com"></script>
    @include('layouts.partials.fonts')
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    @include('layouts.partials.mobile-viewport')
    @include('layouts.partials.ui-styles')
    <style>
        body { -webkit-tap-highlight-color: transparent; }
        .pb-safe { padding-bottom: env(safe-area-inset-bottom); }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-800 overflow-hidden">

<div class="flex app-shell overflow-hidden">
    @include('layouts.sidebar')

    <div class="flex-1 flex flex-col overflow-hidden min-w-0">
        @include('layouts.navbar')

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-50 px-4 py-5 sm:p-6">
            <div>
                @include('layouts.breadcrumb')

                <div class="flex flex-col md:flex-row md:items-end justify-between mb-5 sm:mb-8 gap-4 min-w-0">
                    <div class="min-w-0">
                        <div class="flex items-center gap-3 flex-wrap">
                            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight break-words min-w-0">@yield('title')</h1>
                            @yield('title_action')
                        </div>
                        @hasSection('subtitle')
                            <p class="text-slate-500 text-sm font-medium mt-1 break-words">@yield('subtitle')</p>
                        @endif
                    </div>
                    {{-- Chỉ render khi có nội dung: div rỗng vẫn ăn gap-4 của cha, tạo khoảng trắng thừa trên mobile --}}
                    @hasSection('page_actions')
                        <div class="flex flex-wrap gap-2 sm:gap-3 items-center shrink-0">
                            @yield('page_actions')
                        </div>
                    @endif
                </div>

                @yield('content')
            </div>
            @include('partials.loading')
        </main>

        @include('layouts.bottom-nav')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/vn.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@include('layouts.partials.ajax-helper')
@include('layouts.partials.scripts')
@include('partials.flash_messages_swal')
@stack('scripts')
@include('layouts.partials.global-esc')
</body>
</html>
