{{-- Class dùng chung cho phần tử "lá" (input, label, nút, thẻ, badge) — Tailwind CDN tự biên dịch @apply.
     KHÔNG dùng các class này cho phần tử mà JS chọn theo class (overlay modal/drawer giữ utility gốc
     vì global-esc + closeActiveModal tìm theo `.fixed.inset-0.z-50`). --}}
<style>
    body { font-family: 'Inter', sans-serif; }
    /* Đóng/mở mượt cho submenu sidebar (grid 0fr → 1fr, giống cms-manager) */
    .smooth-collapse { display: grid; grid-template-rows: 0fr; opacity: 0; visibility: hidden; transition: grid-template-rows .3s ease-in-out, opacity .3s ease-in-out; }
    .smooth-collapse.is-open { grid-template-rows: 1fr; opacity: 1; visibility: visible; }
    .smooth-collapse-inner { overflow: hidden; }
</style>
<style type="text/tailwindcss">
    @layer components {
        .form-label { @apply block text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1; }
        .form-input {
            @apply w-full min-h-[44px] px-4 py-2.5 bg-slate-50 border-2 border-slate-100 rounded-xl text-sm font-semibold text-slate-800
                   placeholder:text-slate-300 placeholder:font-medium focus:bg-white focus:ring-4 focus:ring-indigo-50 focus:border-indigo-500
                   outline-none transition-all disabled:opacity-60 disabled:cursor-not-allowed;
        }
        .form-file {
            @apply w-full text-xs font-semibold text-slate-500 file:mr-3 file:min-h-[40px] file:px-4 file:rounded-xl file:border-0
                   file:bg-indigo-50 file:text-indigo-700 file:font-bold hover:file:bg-indigo-100 file:cursor-pointer;
        }

        .btn {
            @apply inline-flex items-center justify-center gap-2 min-h-[44px] px-5 rounded-xl text-sm font-bold transition-all
                   active:scale-[0.98] disabled:opacity-60 disabled:cursor-not-allowed whitespace-nowrap;
        }
        .btn-primary { @apply bg-indigo-600 text-white shadow-md shadow-indigo-200 hover:bg-indigo-700; }
        .btn-dark { @apply bg-slate-900 text-white shadow-md hover:bg-indigo-600; }
        .btn-secondary { @apply bg-slate-100 text-slate-600 hover:bg-slate-200; }
        .btn-outline { @apply bg-white border-2 border-slate-100 text-slate-600 hover:border-slate-200 hover:bg-slate-50; }
        .btn-soft { @apply bg-indigo-50 text-indigo-700 hover:bg-indigo-100; }
        .btn-success { @apply bg-emerald-600 text-white shadow-md shadow-emerald-100 hover:bg-emerald-700; }
        .btn-warning-soft { @apply bg-amber-50 text-amber-700 hover:bg-amber-100; }
        .btn-danger-soft { @apply bg-rose-50 text-rose-600 hover:bg-rose-100; }
        .btn-icon { @apply inline-flex items-center justify-center w-11 h-11 shrink-0 rounded-xl transition-colors; }

        .card { @apply bg-white rounded-2xl border border-slate-200 shadow-sm; }
        .section-label { @apply text-[11px] font-bold text-slate-400 uppercase tracking-widest; }
        .badge { @apply inline-flex items-center gap-1.5 whitespace-nowrap px-2.5 py-1 rounded-full text-[11px] font-bold ring-1 ring-inset; }
    }
</style>
