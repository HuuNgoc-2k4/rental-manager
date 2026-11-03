{{--
    Cấu hình đồng hồ điện / nước của phòng (form thêm phòng và form sửa phòng dùng chung).

    @param string $prefix                   tiền tố id input (new / room)
    @param \Illuminate\Support\Collection|null $meters  đồng hồ đang dùng theo type để điền sẵn (form sửa)
--}}
@foreach(\App\Enums\Meter\MeterType::cases() as $type)
    @php
        $meter = $meters?->get($type->value);
        $isElectric = $type === \App\Enums\Meter\MeterType::Electric;
    @endphp
    <fieldset class="rounded-2xl border-2 border-slate-100 p-4">
        <legend class="px-2 flex items-center gap-1.5 text-[11px] font-bold text-slate-500 uppercase tracking-widest">
            <i data-lucide="{{ $isElectric ? 'zap' : 'droplet' }}" class="w-3.5 h-3.5 {{ $isElectric ? 'text-amber-500' : 'text-sky-500' }}"></i>
            Đồng hồ {{ $type->label() }}
        </legend>
        <div class="grid grid-cols-3 gap-2 sm:gap-3">
            <div>
                <label for="{{ $prefix }}-{{ $type->value }}-int" class="form-label">Số nguyên</label>
                <input type="number" id="{{ $prefix }}-{{ $type->value }}-int" name="{{ $type->value }}_integer_digits" required min="1" max="10" value="{{ old($type->value.'_integer_digits', $meter?->integer_digits ?? 5) }}" class="form-input">
            </div>
            <div>
                <label for="{{ $prefix }}-{{ $type->value }}-dec" class="form-label">Số lẻ</label>
                <input type="number" id="{{ $prefix }}-{{ $type->value }}-dec" name="{{ $type->value }}_decimal_digits" required min="0" max="3" value="{{ old($type->value.'_decimal_digits', $meter?->decimal_digits ?? 0) }}" class="form-input">
            </div>
            <div>
                <label for="{{ $prefix }}-{{ $type->value }}-init" class="form-label">Chỉ số đầu</label>
                <input type="number" step="any" id="{{ $prefix }}-{{ $type->value }}-init" name="{{ $type->value }}_initial_reading" required min="0" value="{{ old($type->value.'_initial_reading', $meter ? (float) $meter->initial_reading : 0) }}" class="form-input">
            </div>
        </div>
    </fieldset>
@endforeach
