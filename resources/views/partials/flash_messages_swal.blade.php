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

{{-- Mật khẩu tạm: hiện đúng 1 lần sau khi cấp tài khoản / đặt lại mật khẩu. --}}
@if(session('credentials'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Swal === 'undefined') return;
            const c = @json(session('credentials'));
            const text = c.password
                ? 'Tài khoản: ' + c.login + '\nMật khẩu tạm: ' + c.password + '\n(Đăng nhập lần đầu sẽ phải đổi mật khẩu)'
                : '';
            const esc = (v) => String(v ?? '').replace(/[&<>"']/g, ch => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[ch]));
            Swal.fire({
                icon: c.password ? 'success' : 'info',
                title: c.password ? 'Mật khẩu tạm' : 'Tài khoản đã có sẵn',
                html: c.password
                    ? '<p class="text-sm text-slate-600 break-words mb-3">' + esc(c.name) + ' đăng nhập bằng <b>' + esc(c.login) + '</b></p>'
                      + '<p class="text-2xl font-bold tracking-widest text-slate-800 bg-slate-50 rounded-xl py-3 break-all select-all">' + esc(c.password) + '</p>'
                      + '<p class="text-xs text-rose-600 font-bold mt-3">Mật khẩu chỉ hiện một lần. Hãy gửi cho người dùng ngay.</p>'
                    : '<p class="text-sm text-slate-600 break-words">' + esc(c.name) + ' đã có tài khoản (' + esc(c.login) + '), giữ nguyên mật khẩu cũ.</p>',
                showCancelButton: !!c.password,
                confirmButtonText: 'Đã ghi lại',
                cancelButtonText: 'Sao chép',
                allowOutsideClick: false,
                customClass: { popup: 'rounded-3xl' }
            }).then(r => {
                if (r.dismiss === Swal.DismissReason.cancel && navigator.clipboard) navigator.clipboard.writeText(text);
            });
        });
    </script>
@endif
