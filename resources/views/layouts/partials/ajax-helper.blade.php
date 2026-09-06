@verbatim
<script>
    /**
     * Chuẩn hoá chuỗi để tìm kiếm tiếng Việt phía client: lowercase + bỏ dấu + đ→d.
     * @param {*} s
     * @returns {string}
     */
    window.stripVN = function (s) {
        return (s == null ? '' : String(s))
            .toLowerCase()
            .normalize('NFD').replace(/[̀-ͯ]/g, '')
            .replace(/đ/g, 'd')
            .trim();
    };

    /**
     * Gọi API JSON chuẩn (fetch + CSRF + parse + chuẩn hoá lỗi).
     * Thành công: trả JSON body. Lỗi: throw Error kèm .status (HTTP code) + .data (JSON lỗi nếu có).
     *
     * @param {string} url
     * @param {Object} [opts] - { method, body (object|string), headers, timeoutMs }
     * @returns {Promise<Object>}
     */
    window.apiRequest = async function (url, opts = {}) {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            || document.querySelector('input[name="_token"]')?.value;
        const method = (opts.method || 'GET').toUpperCase();

        const config = {
            method,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': token,
                ...(opts.headers || {})
            }
        };
        if (opts.body !== undefined && method !== 'GET' && method !== 'HEAD') {
            config.headers['Content-Type'] = 'application/json';
            config.body = typeof opts.body === 'string' ? opts.body : JSON.stringify(opts.body);
        }

        let timeoutId = null;
        if (opts.timeoutMs) {
            const controller = new AbortController();
            config.signal = controller.signal;
            timeoutId = setTimeout(() => controller.abort(), opts.timeoutMs);
        }

        let response;
        try {
            response = await fetch(url, config);
        } catch (e) {
            const err = new Error(
                e.name === 'AbortError'
                    ? 'Máy chủ phản hồi quá lâu. Tải lại trang để kiểm tra, hoặc thử lại sau.'
                    : 'Không kết nối được máy chủ. Kiểm tra mạng rồi thử lại.'
            );
            err.status = 0;
            err.timedOut = e.name === 'AbortError';
            throw err;
        } finally {
            if (timeoutId) clearTimeout(timeoutId);
        }

        const isJson = (response.headers.get('content-type') || '').includes('application/json');
        const payload = isJson ? await response.json().catch(() => null) : null;

        if (!response.ok || !isJson) {
            const err = new Error(
                (payload && payload.message)
                || (response.status === 419 ? 'Phiên đã hết hạn (CSRF). Tải lại trang rồi thử lại.' : null)
                || (response.status === 403 ? 'Bạn không có quyền thực hiện thao tác này.' : null)
                || (response.status === 422 ? 'Dữ liệu không hợp lệ.' : null)
                || ('Lỗi hệ thống (HTTP ' + response.status + '). Vui lòng thử lại.')
            );
            err.status = response.status;
            err.data = payload;
            throw err;
        }
        return payload;
    };
</script>
@endverbatim
