@verbatim
<script>
    if (typeof lucide !== 'undefined') lucide.createIcons();

    window.showGlobalLoading = function (buttonElement = null) {
        const overlay = document.getElementById('global-loading');
        if (overlay) {
            overlay.classList.remove('hidden');
            document.body.classList.add('loading-active');
        }
        if (buttonElement) {
            buttonElement.disabled = true;
            buttonElement.classList.add('cursor-not-allowed', 'opacity-70');
        }
    };

    window.hideGlobalLoading = function (buttonElement = null) {
        const overlay = document.getElementById('global-loading');
        if (overlay) {
            overlay.classList.add('hidden');
            document.body.classList.remove('loading-active');
        }
        if (buttonElement) {
            buttonElement.disabled = false;
            buttonElement.classList.remove('cursor-not-allowed', 'opacity-70');
        }
    };

    /** flatpickr chuẩn: lưu Y-m-d, hiển thị d/m/Y, locale vn. */
    window.initDatepicker = function (selector, customConfig = {}) {
        if (typeof flatpickr === 'undefined' || !document.querySelector(selector)) return null;

        return flatpickr(selector, {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd/m/Y',
            allowInput: true,
            locale: 'vn',
            ...customConfig
        });
    };

    /** Format tiền VND theo vi-VN (mặc định kèm hậu tố " đ"). */
    window.formatVnd = function (amount, withSuffix = true) {
        const formatted = new Intl.NumberFormat('vi-VN').format(Math.round(amount || 0));
        return withSuffix ? formatted + ' đ' : formatted;
    };

    /** Style Swal dùng chung — khai báo 1 lần, mọi trang tái dùng qua window.swalConfirm. */
    window.baseSwalClasses = {
        popup: 'rounded-[2rem] p-4 shadow-2xl',
        title: 'text-xl font-black text-slate-800',
        htmlContainer: 'text-sm font-medium text-slate-500',
        cancelButton: 'bg-slate-100 text-slate-600 rounded-xl px-6 py-3 font-bold hover:bg-slate-200 transition-all',
        input: 'rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-50 text-sm font-medium p-3 outline-none transition-all mt-4'
    };

    if (typeof Swal !== 'undefined') {
        window.swalConfirm = Swal.mixin({
            customClass: {
                ...window.baseSwalClasses,
                confirmButton: 'bg-indigo-600 text-white rounded-xl px-6 py-3 font-bold shadow-md hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-100 transition-all ml-3'
            },
            buttonsStyling: false,
            reverseButtons: true
        });

        window.swalDanger = Swal.mixin({
            customClass: {
                ...window.baseSwalClasses,
                confirmButton: 'bg-rose-500 text-white rounded-xl px-6 py-3 font-bold shadow-md hover:bg-rose-600 focus:ring-4 focus:ring-rose-100 transition-all ml-3'
            },
            buttonsStyling: false,
            reverseButtons: true
        });
    }

    window.toggleMobileSidebar = function () {
        const sidebar = document.getElementById('app-sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        if (!sidebar || !overlay) return;

        const isClosed = sidebar.classList.contains('-translate-x-full');
        if (isClosed) {
            overlay.classList.remove('hidden');
            requestAnimationFrame(() => {
                overlay.classList.remove('opacity-0');
                sidebar.classList.remove('-translate-x-full');
            });
            return;
        }
        overlay.classList.add('opacity-0');
        sidebar.classList.add('-translate-x-full');
        setTimeout(() => overlay.classList.add('hidden'), 300);
    };

    window.confirmLogout = function () {
        const submit = () => {
            window.showGlobalLoading();
            document.getElementById('logout-form').submit();
        };
        if (typeof window.swalConfirm === 'undefined') return submit();

        window.swalConfirm.fire({
            icon: 'question',
            iconColor: '#4f46e5',
            title: 'Đăng xuất?',
            html: '<p class="text-sm text-slate-600">Bạn sẽ cần đăng nhập lại để tiếp tục sử dụng hệ thống.</p>',
            showCancelButton: true,
            confirmButtonText: 'Đăng xuất',
            cancelButtonText: 'Ở lại'
        }).then(r => { if (r.isConfirmed) submit(); });
    };

    /**
     * Modal / drawer chung: overlay có id, class hidden + opacity-0; drawer có thêm panel `[data-panel]` trượt theo translate-x-full.
     * Overlay khai báo data-close-fn="closeActiveModal" (modal z-50) hoặc "closeActiveDrawer" (drawer, data-drawer-overlay) để ESC đóng được.
     */
    window.openOverlay = function (id) {
        const overlay = document.getElementById(id);
        if (!overlay) return;
        overlay.classList.remove('hidden');
        requestAnimationFrame(() => {
            overlay.classList.remove('opacity-0');
            overlay.querySelector('[data-panel]')?.classList.remove('translate-x-full', 'scale-95');
        });
    };

    window.closeOverlay = function (id) {
        const overlay = document.getElementById(id);
        if (!overlay) return;
        overlay.classList.add('opacity-0');
        overlay.querySelector('[data-panel]')?.classList.add(overlay.hasAttribute('data-drawer-overlay') ? 'translate-x-full' : 'scale-95');
        setTimeout(() => overlay.classList.add('hidden'), 300);
    };

    window.closeActiveModal = function () {
        const open = document.querySelector('.fixed.inset-0.z-50:not(.hidden):not(.opacity-0)');
        if (open) window.closeOverlay(open.id);
    };

    window.closeActiveDrawer = function () {
        const open = document.querySelector('[data-drawer-overlay]:not(.hidden):not(.opacity-0)');
        if (open) window.closeOverlay(open.id);
    };

    /** Xác nhận rồi submit form ẩn (xóa, khóa, duyệt...). */
    window.confirmSubmit = function (formId, { title, html, confirmText = 'Xác nhận', danger = false }) {
        const mixin = danger ? window.swalDanger : window.swalConfirm;
        mixin.fire({
            icon: danger ? 'warning' : 'question',
            iconColor: danger ? '#e11d48' : '#4f46e5',
            title,
            html,
            showCancelButton: true,
            confirmButtonText: confirmText,
            cancelButtonText: 'Hủy'
        }).then(r => {
            if (!r.isConfirmed) return;
            window.showGlobalLoading();
            document.getElementById(formId).submit();
        });
    };

    // Chặn wheel + phím ↑↓ làm đổi giá trị input số (gõ chỉ số điện nước rất dễ lệch vì cuộn trang)
    document.addEventListener('wheel', function () {
        if (document.activeElement.type === 'number') document.activeElement.blur();
    });
    document.addEventListener('keydown', function (e) {
        if (['ArrowUp', 'ArrowDown'].includes(e.key) && document.activeElement.type === 'number') e.preventDefault();
    });
</script>
@endverbatim
