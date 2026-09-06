{{-- Form đổi trạng thái đặt NGOÀI drawer để không lồng form trong form --}}
<form id="user-status-form" method="POST" class="hidden">
    @csrf
    @method('PATCH')
    <input type="hidden" name="status">
</form>

<div id="user-drawer-overlay" data-drawer-overlay data-close-fn="closeUserDrawer" onclick="if (event.target === this) window.closeUserDrawer()"
     style="margin-top:0"
     class="hidden fixed inset-0 z-[100] bg-slate-900/40 opacity-0 transition-opacity duration-300">
    <div id="user-drawer-panel"
         class="absolute right-0 top-0 h-full w-full lg:w-[52vw] xl:w-[44vw] bg-white shadow-2xl z-[101]
                translate-x-full transition-transform duration-300 ease-in-out overflow-y-auto">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white">
            <div class="min-w-0">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Sửa tài khoản</p>
                <h3 id="user-drawer-title" class="text-lg font-black text-slate-800 break-words min-w-0"></h3>
            </div>
            <button type="button" onclick="window.closeUserDrawer()" class="text-slate-400 hover:text-slate-600 transition-colors shrink-0">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="user-edit-form" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="edit-name" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Họ và tên</label>
                <input type="text" id="edit-name" name="name" required
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all">
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div>
                    <label for="edit-email" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Email</label>
                    <input type="email" id="edit-email" name="email"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all">
                </div>
                <div>
                    <label for="edit-phone" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Số điện thoại</label>
                    <input type="text" id="edit-phone" name="phone"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all">
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div>
                    <label for="edit-role" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Vai trò</label>
                    <select id="edit-role" name="role" required
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:border-indigo-500 outline-none">
                        @foreach($roles as $role)
                            <option value="{{ $role->value }}">{{ $role->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="edit-status" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Trạng thái</label>
                    <select id="edit-status" name="status" required
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:border-indigo-500 outline-none">
                        @foreach($statuses as $status)
                            <option value="{{ $status->value }}">{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label for="edit-password" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Mật khẩu mới</label>
                <input type="password" id="edit-password" name="password" placeholder="Để trống nếu không đổi"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all">
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="window.closeUserDrawer()"
                        class="px-6 py-3 bg-slate-100 text-slate-600 rounded-xl text-sm font-bold hover:bg-slate-200 transition-all">
                    Đóng
                </button>
                <button type="submit"
                        class="px-6 py-3 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-md hover:bg-indigo-700 transition-all">
                    Lưu thay đổi
                </button>
            </div>
        </form>
    </div>
</div>
