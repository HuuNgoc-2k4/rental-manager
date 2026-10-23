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
        title: 'text-xl font-bold text-slate-800',
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
        // Chỉ ẩn nếu chưa bị mở lại trong 300ms (bấm 2 lần nhanh) — tránh sidebar mở mà mất lớp phủ để đóng
        setTimeout(() => { if (sidebar.classList.contains('-translate-x-full')) overlay.classList.add('hidden'); }, 300);
    };

    /** Dropdown tài khoản ở navbar — bấm ra ngoài thì đóng. */
    window.toggleUserMenu = function (open = null) {
        const panel = document.getElementById('user-menu-panel');
        if (!panel) return;
        panel.classList.toggle('hidden', open === null ? !panel.classList.contains('hidden') : !open);
    };
    document.addEventListener('click', function (e) {
        const menu = document.getElementById('user-menu');
        if (menu && !menu.contains(e.target)) window.toggleUserMenu(false);
    });

    /** Escape HTML cho chuỗi người nhập trước khi ghép vào innerHTML. */
    window.escapeHtml = function (v) {
        return String(v ?? '').replace(/[&<>"']/g, c => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c]));
    };

    /** Avatar chữ cái đầu — cùng bảng màu + thuật toán với partials/avatar.blade.php. */
    window.avatarHtml = function (name, size = 'w-10 h-10 text-sm') {
        const palette = ['bg-indigo-100 text-indigo-700', 'bg-emerald-100 text-emerald-700', 'bg-amber-100 text-amber-700', 'bg-rose-100 text-rose-700', 'bg-sky-100 text-sky-700', 'bg-violet-100 text-violet-700'];
        const words = String(name ?? '').trim().split(/\s+/).filter(Boolean);
        const initials = words.length ? (words[0][0] + (words.length > 1 ? words[words.length - 1][0] : '')).toUpperCase() : '?';
        const chars = Array.from(String(name ?? ''));
        const sum = (chars.length ? chars : ['0']).reduce((s, c) => s + c.codePointAt(0), 0);
        return '<span class="inline-flex items-center justify-center shrink-0 rounded-full font-bold ' + size + ' ' + palette[sum % palette.length] + '" aria-hidden="true">' + window.escapeHtml(initials) + '</span>';
    };

    /**
     * Đặt trạng thái ô ảnh (partials/image-input): có url = hiện ảnh + "Đổi ảnh", null = ô trống.
     * isLocalPreview = ảnh vừa chọn từ máy, không gắn link mở tab mới.
     */
    window.setImageField = function (field, url, isLocalPreview = false) {
        const img = field.querySelector('[data-image-img]');
        const link = field.querySelector('[data-image-preview]');
        if (url) { img.src = url; } else { img.removeAttribute('src'); }
        if (url && !isLocalPreview) { link.href = url; } else { link.removeAttribute('href'); }
        link.classList.toggle('hidden', !url);
        field.querySelector('[data-image-change]').classList.toggle('hidden', !url);
        field.querySelector('[data-image-empty]').classList.toggle('hidden', !!url);
    };
    document.addEventListener('change', function (e) {
        const field = e.target.closest?.('[data-image-field]');
        if (!field || e.target.type !== 'file' || !e.target.files[0]) return;
        window.setImageField(field, URL.createObjectURL(e.target.files[0]), true);
    });

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
        setTimeout(() => { if (overlay.classList.contains('opacity-0')) overlay.classList.add('hidden'); }, 300);
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
