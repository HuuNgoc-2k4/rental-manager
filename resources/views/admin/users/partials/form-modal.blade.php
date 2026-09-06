@php $isCreateOld = old('_form') === 'create'; @endphp

<div id="user-create-modal" data-close-fn="closeCreateUserModal" style="margin-top:0"
     class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 opacity-0 pointer-events-none transition-opacity duration-300">
    <div id="user-create-panel" class="bg-white rounded-3xl w-full max-w-lg shadow-2xl transition-all duration-300 scale-95 max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-lg font-black text-slate-800">Thêm tài khoản</h3>
            <button type="button" onclick="window.closeCreateUserModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="user-create-form" action="{{ route('admin.users.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="_form" value="create">

            <div>
                <label for="create-name" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Họ và tên</label>
                <input type="text" id="create-name" name="name" value="{{ $isCreateOld ? old('name') : '' }}" required
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="create-email" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Email</label>
                    <input type="email" id="create-email" name="email" value="{{ $isCreateOld ? old('email') : '' }}"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all">
                </div>
                <div>
                    <label for="create-phone" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Số điện thoại</label>
                    <input type="text" id="create-phone" name="phone" value="{{ $isCreateOld ? old('phone') : '' }}" placeholder="09xxxxxxxx"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all">
                </div>
            </div>
            <p class="text-[11px] text-slate-400 font-medium">Nhập ít nhất một trong hai: email hoặc số điện thoại — đó là tên đăng nhập.</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="create-role" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Vai trò</label>
                    <select id="create-role" name="role" required
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:border-indigo-500 outline-none">
                        @foreach($roles as $role)
                            <option value="{{ $role->value }}" @selected($isCreateOld && old('role') === $role->value)>{{ $role->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="create-status" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Trạng thái</label>
                    <select id="create-status" name="status"
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:border-indigo-500 outline-none">
                        @foreach($statuses as $status)
                            <option value="{{ $status->value }}" @selected($isCreateOld ? old('status') === $status->value : $status->value === 'active')>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label for="create-password" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Mật khẩu</label>
                <input type="password" id="create-password" name="password" required placeholder="Tối thiểu 8 ký tự"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all">
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="window.closeCreateUserModal()"
                        class="px-6 py-3 bg-slate-100 text-slate-600 rounded-xl text-sm font-bold hover:bg-slate-200 transition-all">
                    Hủy
                </button>
                <button type="submit"
                        class="px-6 py-3 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-md hover:bg-indigo-700 transition-all">
                    Tạo tài khoản
                </button>
            </div>
        </form>
    </div>
</div>
