{{-- Flash message + lỗi validate render tự động ở đây — từng trang KHÔNG tự gọi Swal cho session. --}}
@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Swal === 'undefined') return;
            Swal.fire({
                icon: 'success',
                title: 'Thành công!',
                text: @json(session('success')),
                toast: true,
                position: 'top-end',
                timer: 2500,
                showConfirmButton: false
            });
        });
    </script>
@endif

@if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Swal === 'undefined') return;
            Swal.fire({
                icon: 'error',
                title: 'Thất bại!',
                text: @json(session('error')),
                customClass: { popup: 'rounded-3xl' }
            });
        });
    </script>
@endif

@if($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Swal === 'undefined') return;
            const messages = @json(collect($errors->all())->unique()->values());
            Swal.fire({
                icon: 'error',
                title: 'Dữ liệu không hợp lệ!',
                html: '<div class="flex flex-col gap-2 mt-3">' + messages.map(m =>
                    '<div class="px-4 py-2.5 rounded-xl bg-rose-50 text-rose-700 text-xs font-bold border border-rose-100 text-left">' + m + '</div>'
                ).join('') + '</div>',
                confirmButtonText: 'Đóng',
                customClass: {
                    popup: 'rounded-3xl',
                    confirmButton: 'bg-rose-500 text-white rounded-xl px-6 py-3 font-bold shadow-md hover:bg-rose-600 transition-all'
                },
                buttonsStyling: false
            });
        });
    </script>
@endif
