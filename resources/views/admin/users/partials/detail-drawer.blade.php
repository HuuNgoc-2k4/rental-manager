{{-- Form đổi trạng thái đặt NGOÀI drawer để không lồng form trong form --}}
<form id="user-status-form" method="POST" class="hidden">
    @csrf
    @method('PATCH')
    <input type="hidden" name="status">
</form>

<form id="user-reset-form" method="POST" class="hidden">@csrf</form>

<div id="user-drawer-overlay" data-drawer-overlay data-close-fn="closeUserDrawer" onclick="if (event.target === this) window.closeUserDrawer()"
     style="margin-top:0"
     class="hidden fixed inset-0 z-[100] bg-slate-900/40 backdrop-blur-sm opacity-0 transition-opacity duration-300">
    <div id="user-drawer-panel"
         class="absolute right-0 top-0 h-full w-full lg:w-[52vw] xl:w-[44vw] bg-slate-50 shadow-2xl z-[101]
                translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
        <div class="px-5 sm:px-6 py-4 border-b border-slate-200 flex items-center gap-3 bg-white shrink-0">
            <span id="user-drawer-avatar" class="shrink-0"></span>
            <div class="min-w-0 flex-1">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Sửa tài khoản</p>
                <h3 id="user-drawer-title" class="text-lg font-bold text-slate-900 break-words min-w-0"></h3>
            </div>
            <button type="button" onclick="window.closeUserDrawer()" aria-label="Đóng" class="btn-icon -mr-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="user-edit-form" method="POST" class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-4">
            @csrf
            @method('PUT')

            <div class="card p-4 sm:p-5 space-y-4">
                <div class="flex items-center gap-2"><i data-lucide="user" class="w-4 h-4 text-indigo-500"></i><p class="section-label">Thông tin</p></div>
                <div>
                    <label for="edit-name" class="form-label">Họ và tên</label>
                    <input type="text" id="edit-name" name="name" required class="form-input">
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div>
                        <label for="edit-email" class="form-label">Email</label>
                        <input type="email" id="edit-email" name="email" class="form-input">
                    </div>
                    <div>
                        <label for="edit-phone" class="form-label">Số điện thoại</label>
                        <input type="text" id="edit-phone" name="phone" inputmode="numeric" class="form-input">
                    </div>
                </div>
            </div>

            <div class="card p-4 sm:p-5 space-y-4">
                <div class="flex items-center gap-2"><i data-lucide="shield" class="w-4 h-4 text-indigo-500"></i><p class="section-label">Phân quyền</p></div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="edit-role" class="form-label">Vai trò</label>
                        <select id="edit-role" name="role" required class="form-input">
                            @foreach($roles as $role)
                                <option value="{{ $role->value }}">{{ $role->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="edit-status" class="form-label">Trạng thái</label>
                        <select id="edit-status" name="status" required class="form-input">
                            @foreach($statuses as $status)
                                <option value="{{ $status->value }}">{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="card p-4 sm:p-5 space-y-4">
                <div class="flex items-center gap-2"><i data-lucide="key-round" class="w-4 h-4 text-indigo-500"></i><p class="section-label">Mật khẩu</p></div>
                <div>
                    <label for="edit-password" class="form-label">Mật khẩu mới</label>
                    <input type="password" id="edit-password" name="password" placeholder="Để trống nếu không đổi" autocomplete="new-password" class="form-input">
                </div>
                <button type="button" onclick="window.resetUserPassword()" class="btn btn-warning-soft w-full whitespace-normal text-center">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i> Đặt lại mật khẩu (sinh mật khẩu tạm)
                </button>
            </div>
        </form>

        <div class="px-4 sm:px-6 py-3 border-t border-slate-200 bg-white shrink-0 grid grid-cols-2 sm:flex sm:justify-end gap-2 pb-safe">
            <button type="button" onclick="window.closeUserDrawer()" class="btn btn-secondary">Đóng</button>
            <button type="submit" form="user-edit-form" class="btn btn-primary"><i data-lucide="save" class="w-4 h-4"></i> Lưu thay đổi</button>
        </div>
    </div>
</div>
