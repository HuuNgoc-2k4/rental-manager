{{-- Hai vá BẮT BUỘC cho Safari trên iPhone. Cả hai KHÔNG tái hiện được ở chế độ responsive của trình
     duyệt desktop (không có thanh công cụ, không có cơ chế auto-zoom) nên chỉ lộ trên máy thật.
     Include trong <head> của mọi layout / trang độc lập có khung cuộn riêng hoặc có ô nhập. --}}
<style>
    /* 1) Khung app cao đúng phần MÀN HÌNH ĐANG THẤY.
          iOS Safari tính `100vh` (= Tailwind `h-screen`) theo chiều cao khi thanh công cụ ĐÃ THU, trong khi
          `body` đặt `overflow-hidden` nên trang không cuộn được: đáy khung cuộn `<main>` nằm khuất dưới thanh
          công cụ và KHÔNG vuốt tới được — mất hẳn phân trang + dòng cuối. `100dvh` co giãn theo thanh công cụ
          THẬT nên đáy luôn chạm tới. Khai `100vh` TRƯỚC làm dự phòng cho iOS < 15.4 (không hiểu dvh → bỏ qua
          dòng sau, giữ nguyên hành vi cũ). */
    .app-shell {
        height: 100vh;
        height: 100dvh;
    }

    /* 2) iOS Safari tự PHÓNG TO trang khi focus vào ô nhập có `font-size` < 16px, và KHÔNG thu lại sau đó.
          Form trong app dùng `text-sm`/`text-xs` (14/12px) nên chạm ô nào cũng nhảy zoom — rõ nhất ở ô ngày
          (flatpickr `disableMobile` nên là input text thật, không phải lịch hệ điều hành).
          Ép 16px CHỈ dưới `md` (điện thoại) — máy tính bảng/desktop giữ nguyên cỡ chữ thiết kế; iPad vốn
          không auto-zoom. Cần `!important` vì Tailwind đặt cỡ chữ bằng CLASS (`.text-sm`), thắng selector
          thẻ về độ ưu tiên. Loại checkbox/radio/range vì cỡ chữ không đổi gì mà có thể làm lệch ô. */
    @media (max-width: 767px) {
        input:not([type="checkbox"]):not([type="radio"]):not([type="range"]),
        select,
        textarea {
            font-size: 16px !important;
        }
    }
</style>
