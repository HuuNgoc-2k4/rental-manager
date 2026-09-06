<script>
    // ESC đóng modal / drawer đang mở — handler chung, từng trang KHÔNG tự thêm keydown riêng.
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;

        const modal = document.querySelector('.fixed.inset-0.z-50:not(.opacity-0):not(.hidden):not(.pointer-events-none)');
        if (modal) {
            const closeFn = modal.dataset.closeFn;
            if (closeFn && typeof window[closeFn] === 'function') {
                window[closeFn]();
            } else {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
            return;
        }

        // Drawer: overlay dùng z-[100] + data-close-fn để handler biết gọi hàm đóng nào.
        const drawer = document.querySelector('[data-drawer-overlay]:not(.hidden)');
        if (drawer) {
            const closeFn = drawer.dataset.closeFn;
            if (closeFn && typeof window[closeFn] === 'function') {
                window[closeFn]();
                return;
            }
            drawer.classList.add('hidden');
            document.body.style.overflow = '';
        }
    });
</script>
