<!DOCTYPE html>
<html lang="vi" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Quản lý nhà cho thuê')</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar-item-active { background-color: #eef2ff; color: #4f46e5; border-right: 4px solid #4f46e5; }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-50 overflow-hidden">

<div class="flex h-dvh overflow-hidden">
    @include('layouts.sidebar')

    <div class="flex-1 flex flex-col overflow-hidden">
        @include('layouts.navbar')

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-50 p-4 md:p-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 md:mb-8 gap-4">
                <div>
                    <h1 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight">@yield('title')</h1>
                    @hasSection('subtitle')
                        <p class="text-slate-500 text-sm font-medium mt-1">@yield('subtitle')</p>
                    @endif
                </div>
                <div class="flex flex-wrap gap-3 items-center">
                    @yield('page_actions')
                </div>
            </div>

            @include('layouts.breadcrumb')
            @yield('content')
            @include('partials.loading')
        </main>
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
