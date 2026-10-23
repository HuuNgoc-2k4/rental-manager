# Quản lý nhà cho thuê, có đọc chỉ số điện nước từ ảnh

**Tên đề tài:** Xây dựng hệ thống quản lý nhà cho thuê tích hợp nhận dạng chỉ số điện nước từ ảnh chụp bằng mô hình YOLO

*Rental property management with YOLO-based utility meter reading*

| | |
|---|---|
| Nghiệp vụ chính | 8 module |
| Mô hình AI | YOLO tự huấn luyện, 2 tầng |
| Dữ liệu | Dataset công khai có nhãn + tự chụp bổ sung |
| Dùng được từ | Tuần 4 (chưa cần AI) |
| Thời lượng | 8 tuần / 1 người |

Nghiệp vụ bình thường, ai cũng hiểu ngay: phòng, khách thuê, hợp đồng, chốt số hàng tháng, hoá đơn, thu tiền, công nợ. Phần AI nằm đúng một chỗ — chụp ảnh đồng hồ điện nước, mô hình đọc dãy số rồi điền sẵn vào phiếu chốt.

---

## 1. Bối cảnh

Chủ nhà trọ ở Việt Nam vẫn đang làm việc này bằng sổ tay và Excel: cuối tháng đi từng phòng, ghi số đồng hồ vào giấy, về nhà nhập lại, trừ tay ra số tiêu thụ, cộng tiền phòng với tiền rác tiền xe, nhắn từng người. Sai một chữ số là tranh chấp với khách. Đề tài này số hoá đúng vòng đó, và bỏ bớt bước nhập lại bằng tay.

**Vì sao đề tài này vừa sức và vẫn đủ nặng:** phần nghiệp vụ có chu kỳ tính tiền, công nợ, thu từng phần, cọc và tất toán — đủ dày để chấm khối lượng chức năng. Phần AI là một mô hình tự huấn luyện, có tập dữ liệu công khai và có chỉ số đo được, đủ để viết một chương thực nghiệm tử tế.

---

## 2. Xương sống nghiệp vụ — vòng một tháng

Toàn bộ hệ thống chạy quanh vòng này. Làm xong vòng này là đã có sản phẩm dùng được, phần còn lại là bổ trợ.

```
Hợp đồng  →  Chốt chỉ số  →  Hoá đơn kỳ  →  Phiếu thu  →  Công nợ
(đầu kỳ)     (cuối tháng)     (tự sinh)      (thu dần)    (theo dõi)
```

| Bước | Nội dung |
|---|---|
| **Hợp đồng** — đầu kỳ | Giá thuê, đơn giá điện nước, phí cố định, cọc |
| **Chốt chỉ số** — cuối tháng | Số điện, số nước từng phòng |
| **Hoá đơn kỳ** — tự sinh | Tiền phòng + điện + nước + phí + phát sinh |
| **Phiếu thu** — thu dần | Thu từng phần, còn nợ bao nhiêu |
| **Công nợ** — theo dõi | Quá hạn, nhắc tự động |

---

## 3. Khối lượng chức năng — tám module

### 01. Nhà & phòng
Nhiều khu trọ, mỗi khu nhiều phòng: giá thuê mặc định, diện tích, số người tối đa, trạng thái trống / đã thuê / đang sửa. Sơ đồ phòng nhìn một phát thấy phòng nào trống.

### 02. Khách thuê
Thông tin liên hệ, giấy tờ, người ở cùng, lịch sử từng thuê phòng nào. Một khách có thể thuê lại nhiều lần.

### 03. Hợp đồng thuê
Ngày bắt đầu / kết thúc, tiền cọc, giá thuê chốt riêng cho hợp đồng này, đơn giá điện nước, danh sách phí cố định (rác, xe, internet), chu kỳ thanh toán. Gia hạn, kết thúc sớm, đổi phòng. In hợp đồng ra PDF.

### 04. Chốt chỉ số
Mỗi kỳ, mỗi phòng: chỉ số điện và nước đầu kỳ / cuối kỳ, tiêu thụ tự tính. Kèm ảnh đồng hồ làm bằng chứng.

Khai báo **số chữ số của từng đồng hồ** ngay khi setup phòng (mấy chữ số nguyên, mấy chữ số thập phân — đồng hồ nước cơ thường 5 số đen + vòng đỏ). Đây là chốt chặn quan trọng nhất: mô hình đọc ra số chữ số không khớp khai báo thì chặn ngay, và thế là diệt được nhóm lỗi lệch bậc 10× / 100×.

### 05. Hoá đơn kỳ
Sinh tự động từ hợp đồng + chỉ số: tiền phòng, điện, nước, phí cố định, phát sinh, giảm trừ. Tính theo ngày cho tháng ở lẻ (vào giữa tháng, trả phòng giữa tháng). Xuất PDF, xuất Excel cả khu.

### 06. Thu tiền & công nợ
Phiếu thu từng phần, số dư còn nợ, lịch sử thanh toán. Tất toán khi trả phòng: trừ nợ và hư hỏng vào cọc, trả lại phần còn. Bảng công nợ quá hạn theo số ngày.

### 07. Nhắc tự động
Cron chạy hàng ngày: tới ngày thu thì gửi thông báo tiền phòng, quá hạn thì nhắc lại, hợp đồng gần hết hạn thì báo trước. Gửi qua Telegram hoặc email, có log để biết ai đã được nhắc.

### 08. Báo cáo & phân quyền
Doanh thu theo tháng và theo khu, tỉ lệ lấp phòng, tiêu thụ điện nước theo phòng, công nợ. Ba vai trò: chủ nhà thấy tất cả, người quản lý chỉ khu mình, khách thuê chỉ xem hoá đơn của mình.

---

## 4. Phần AI — mô hình YOLO tự huấn luyện, hai tầng

Đây là chỗ duy nhất có AI, và nó giải quyết đúng bước tốn công nhất của chủ nhà: đi ghi số rồi về nhập lại. Với 30 phòng là 60 lần nhập tay mỗi tháng, mỗi lần đều có thể sai.

### 4.1 Kiến trúc hai tầng

Đây là kiến trúc chuẩn của bài toán đọc đồng hồ tự động (automatic meter reading), và cũng là kiến trúc các bài báo về UFPR-AMR dùng làm baseline:

```
Ảnh chụp cả mặt đồng hồ
        │
        ▼
┌───────────────────────────────┐
│ Tầng 1 — YOLO detect          │   1 lớp: "counter"
│ Khoanh vùng ô số trên đồng hồ │   → cắt (crop) vùng đó ra
└───────────────────────────────┘
        │
        ▼
┌───────────────────────────────┐
│ Tầng 2 — YOLO detect          │   10 lớp: chữ số 0–9
│ Phát hiện + phân loại từng    │   → sắp xếp box theo trục x
│ chữ số trong vùng đã cắt      │   → ghép thành chuỗi số
└───────────────────────────────┘
        │
        ▼
  Chuỗi số + độ tin cậy từng chữ số
```

Tách hai tầng, không làm một tầng, vì hai lý do: chữ số trên ảnh gốc quá nhỏ để YOLO bắt tốt, và cắt vùng ra trước giúp tầng 2 chạy trên ảnh đã chuẩn hoá kích thước, nhanh và chính xác hơn hẳn.

**Phương án dự phòng cho tầng 2** nếu không gom đủ nhãn vị trí từng chữ số: đồng hồ kiểu bánh xe chữ có số chữ số cố định và bố cục đều, nên có thể cắt vùng crop thành N ô bằng nhau rồi phân loại từng ô bằng một CNN nhỏ. Kém linh hoạt hơn nhưng cực rẻ và chạy tốt với loại đồng hồ này.

### 4.2 Luồng nghiệp vụ

1. Người quản lý mở trang chốt số trên điện thoại. **Chọn phòng trước**, màn hình hiện luôn chỉ số kỳ trước để đối chiếu, rồi mới bấm chụp.
2. Ảnh gửi lên server → gọi service nhận dạng → trả về chuỗi số kèm độ tin cậy từng chữ số.
3. Số được **điền sẵn** vào ô nhập, không tự lưu — người vẫn là người xác nhận.
4. Hệ thống **từ chối đọc** khi rơi vào một trong các trường hợp ở mục 4.3, và nói thẳng "không đọc được, chụp lại". Tuyệt đối không đoán.
5. Kiểm tra hợp lý ngay tại đó: số mới ≥ số cũ (có xử lý đồng hồ quay vòng); tiêu thụ nằm trong khoảng hợp lý theo 3–6 kỳ trước của chính phòng đó; tiêu thụ = 0 mà phòng có người thì chặn.
6. Ảnh được lưu kèm phiếu chốt số. Khách thuê thắc mắc thì mở ảnh ra đối chiếu.

### 4.3 Cơ chế từ chối — phần kỹ thuật đáng giá nhất

Đây là chỗ dùng YOLO hơn hẳn gọi mô hình ngôn ngữ thị giác: **độ tin cậy của YOLO là điểm phát hiện thật, đo được và hiệu chỉnh được**, không phải con số mô hình tự khai. Nhờ vậy bốn luật dưới đây đều là logic tường minh:

- Tầng 1 không tìm thấy vùng ô số, hoặc tìm ra nhiều vùng → từ chối.
- Số chữ số phát hiện được **không khớp số chữ số đã khai báo** cho đồng hồ đó → từ chối. Luật này chặn nhóm lỗi nguy hiểm nhất là lệch bậc.
- Có bất kỳ chữ số nào độ tin cậy dưới ngưỡng → từ chối cả chuỗi.
- Các box chữ số không thẳng hàng hoặc chồng lấn bất thường → từ chối (thường là chụp chéo, hoặc đồng hồ đang chuyển số).

> **Điểm cốt lõi khi bảo vệ:** giá trị thật của tính năng không nằm ở chỗ "mô hình đọc được số", mà ở chỗ nó **biết khi nào mình không đọc được**. Một hệ thống đọc sai âm thầm rồi xuất hoá đơn sai còn tệ hơn nhập tay. Ngưỡng từ chối là thứ phải chọn bằng số liệu, không phải chọn bằng cảm tính — xem mục 7.

### 4.4 Ràng buộc ở tầng dữ liệu

Mô hình **không bao giờ ghi thẳng vào giá trị dùng để tính tiền**. Bảng chốt số có hai cột:

| Cột | Nguồn |
|---|---|
| `suggested_reading` | mô hình đọc ra |
| `reading` | người xác nhận — **chỉ cột này đi vào hoá đơn** |

Đây là quyết định ở tầng schema chứ không phải tầng giao diện, nên không có đường nào để mô hình tự đẩy số vào tiền. Phụ phẩm hay: so hai cột trên dữ liệu đang chạy thật cho bạn số liệu sai sót ngoài thực địa, tốt hơn cả bộ test.

---

## 5. Dữ liệu huấn luyện & đánh giá

Không tự gán nhãn từ đầu — đã có dataset công khai có nhãn sẵn, tiết kiệm được nguyên vài tuần.

| Bộ | Loại | Số ảnh | Nhãn có sẵn | Dùng cho |
|---|---|---|---|---|
| **WMR (Hangzhou)** | Nước, bánh xe chữ | 50.000+ (2,62 GB) | mask vùng số, giá trị đọc 5–6 chữ số, **nhãn điều kiện khó** (mờ / loá / bị che) | Tầng 1 + đánh giá đầu-cuối |
| **UFPR-AMR** | Điện | 2.000 | vùng ô số, giá trị, **vị trí từng chữ số**, chia sẵn train/val/test | Tầng 2 |
| Water Meters (Yandex.Toloka) | Nước | 1.244 | mask + reading | Bổ sung tầng 1 |
| Roboflow Universe (nhiều bộ) | Điện tử 7 đoạn | 121–1.943 mỗi bộ | bbox chữ số | Bổ sung, tải ngay |
| **Tự chụp** | Điện + nước ở VN | 60–80 | tự ghi | **Tập kiểm tra thực địa** |

- **WMR** — DOI `10.5061/dryad.7d7wm3860`, paper *Scientific Data* 13, 479 (2026). Có mask vùng số → chuyển sang bbox cho tầng 1. Nhãn điều kiện khó là thứ để đo cơ chế từ chối ở mục 4.3.
- **UFPR-AMR** — github.com/raysonlaroca/ufpr-amr-dataset. Là bộ duy nhất có **vị trí từng chữ số**, nên là nguồn chính cho tầng 2. Phải ký license agreement bằng email trường, **làm từ tuần 1** vì duyệt mất vài ngày.
- Giả định cần kiểm ở tuần 5: bộ nhận dạng chữ số huấn luyện trên công tơ điện có chuyển tốt sang đồng hồ nước bánh xe chữ không. Cả hai đều là chữ số trên bánh xe quay nên khả năng cao là được; nếu không, tự gán vị trí chữ số cho vài trăm ảnh crop của WMR — ảnh crop nhỏ, gán rất nhanh.
- **Vẫn phải tự chụp 60–80 ảnh đồng hồ ở Việt Nam.** Không bộ công khai nào chụp ở VN. Cố tình đa dạng: đồng hồ cơ và điện tử, ngược sáng, chụp chéo, mặt kính bám bụi, chụp ban đêm. Đây là tập trả lời cho câu hỏi chắc chắn sẽ bị hỏi: "chạy trên đồng hồ Việt Nam thì sao".

**Giấy phép — đọc kỹ và ghi vào chương 3 báo cáo:**
- Phần lớn các bộ này là *academic / non-commercial*. Đồ án thì hợp lệ.
- **Đừng commit ảnh dataset vào repo.** Viết script tải, ghi rõ nguồn trong README.
- Bộ Kaggle Yandex.Toloka là CC BY-NC-ND 4.0: được dùng và đánh giá, nhưng không phát hành lại bản đã chỉnh sửa.

---

## 6. Kiến trúc triển khai

Laravel không chạy được YOLO. Tách thành hai service, gọi nhau qua HTTP — **đúng mô hình `cms-manager → ads-tool` bạn đã dựng rồi**, nên phần này không phải học lại gì.

```
┌────────────────────────┐        POST /read-meter          ┌──────────────────────────┐
│  Laravel 12            │  ─────  multipart: ảnh  ───────▶ │  Python service          │
│  (nghiệp vụ, DB, UI)   │                                  │  FastAPI + Ultralytics   │
│                        │ ◀──  JSON: reading, per-digit ── │  2 model YOLO (.pt/.onnx)│
│  MeterReadingService   │       confidence, boxes,         │                          │
│  extends BaseHttpSvc   │       reject_reason              │  stateless, không DB     │
└────────────────────────┘                                  └──────────────────────────┘
        docker-compose: 2 service + MySQL
```

- Service Python **không có database, không có state** — nhận ảnh, trả JSON. Mọi logic nghiệp vụ (ngưỡng từ chối theo loại đồng hồ, kiểm tra hợp lý theo lịch sử) nằm bên Laravel.
- Gọi qua queue nếu ảnh nhiều; chốt số từng phòng thì gọi đồng bộ cho nhanh.
- Xuất model sang **ONNX** để chạy suy luận bằng CPU trên server rẻ — không cần GPU khi vận hành, chỉ cần GPU lúc huấn luyện.

---

## 7. Cách đo

Có mô hình tự huấn luyện thì chương thực nghiệm dày lên đáng kể, và phần lớn số liệu lấy được tự động.

### Chỉ số mô hình
- **Tầng 1:** mAP@0.5 của việc khoanh vùng ô số.
- **Tầng 2:** độ chính xác từng chữ số, **và độ chính xác cả chuỗi** — con số quan trọng hơn hẳn, vì đọc đúng 4/5 chữ số vẫn ra hoá đơn sai.
- So kết quả trên tập test của UFPR-AMR với **số đã công bố trong các bài báo** dùng chính bộ đó. Không nhiều đồ án có được mốc đối chiếu kiểu này.

### Chỉ số vận hành — phần đắt giá nhất
- **Đường cong ngưỡng tin cậy:** trục hoành là ngưỡng, hai đường là *tỉ lệ từ chối* và *tỉ lệ đọc sai trong số đã chấp nhận*. Chọn điểm vận hành trên đường cong này, có lý lẽ. Đây là biểu đồ hay nhất của đồ án.
- Tỉ lệ từ chối trên nhóm ảnh "điều kiện khó" của WMR so với nhóm ảnh sạch — chứng minh cơ chế từ chối hoạt động đúng chỗ.
- Kết quả trên tập tự chụp ở VN so với tập công khai, để nói về khoảng cách miền dữ liệu.
- Tốc độ suy luận: ms/ảnh trên CPU và trên GPU.

### Đối chứng
- **YOLO tự huấn luyện vs gọi API mô hình thị giác thương mại** trên cùng tập test: độ chính xác, độ trễ, chi phí mỗi 1.000 ảnh. Làm một buổi, được nguyên một mục so sánh.
- Thời gian chốt số mỗi phòng: chụp ảnh so với nhập tay, bấm giờ trên 20 phòng.

---

## 8. Kế hoạch 8 tuần

Điểm quan trọng nhất của thứ tự này: **tuần 4 phải có hệ thống hoàn chỉnh dùng được mà chưa có AI**. Nhập chỉ số bằng tay vẫn ra hoá đơn đúng. Mô hình thêm vào từ tuần 5 chỉ để bỏ bước nhập tay — nếu huấn luyện không đạt, đồ án vẫn nguyên vẹn, và bạn có luôn mốc để so thời gian.

| Tuần | Nội dung | Đầu ra kiểm được |
|---|---|---|
| **T1** | ERD, migration, auth, phân quyền ba vai trò. CRUD nhà, phòng, khách thuê. Sơ đồ phòng. **Gửi đơn xin UFPR-AMR ngay ngày đầu.** | Quản lý được phòng và khách |
| **T2** | Hợp đồng thuê: đơn giá riêng, phí cố định, cọc, gia hạn, kết thúc, đổi phòng, in PDF. Chốt chỉ số nhập tay. | Ký và quản lý được hợp đồng |
| **T3** | Sinh hoá đơn kỳ, tính theo ngày cho tháng ở lẻ, xuất PDF/Excel. **Viết test cho phần tính tiền** — chỗ sai là chết. | Ra được hoá đơn đúng số tiền |
| **T4** ◆ | Thu tiền từng phần, công nợ, tất toán trả phòng và trừ cọc. Nhắc tự động qua cron + queue. Báo cáo và dashboard. | **CHỐT: hệ thống hoàn chỉnh, chưa cần AI** |
| **T5** | Tải WMR + UFPR-AMR, chuyển nhãn sang định dạng YOLO, chia train/val/test. Tự chụp 60–80 ảnh ở VN. **Huấn luyện tầng 1** (khoanh vùng ô số). | Tầng 1 đạt mAP chấp nhận được |
| **T6** | **Huấn luyện tầng 2** (chữ số 0–9). Ghép pipeline đọc → chuỗi số. Đóng gói FastAPI + Docker, xuất ONNX. | Service trả đúng chuỗi số qua HTTP |
| **T7** ◆ | Tích hợp Laravel ↔ service. Cài bốn luật từ chối + kiểm tra hợp lý. Chạy đánh giá đầy đủ, vẽ đường cong ngưỡng, chọn điểm vận hành. Đối chứng với API thương mại. Đóng băng tính năng. | **CHỐT: toàn bộ số liệu cho báo cáo** |
| **T8** | Báo cáo, slide, video demo. Dự phòng. **Không code.** | Nộp |

**Máy huấn luyện:** không cần mua GPU. Kaggle Notebooks cho khoảng 30 giờ GPU miễn phí mỗi tuần, đủ thừa cho YOLO cỡ nhỏ trên vài chục nghìn ảnh. Google Colab là phương án hai. Chuẩn bị sẵn script train chạy được không cần sửa, vì phiên notebook bị ngắt giữa chừng là chuyện thường — nhớ lưu checkpoint ra Drive/Kaggle Dataset sau mỗi epoch.

---

## 9. Cạm bẫy làm vỡ kế hoạch

**Đừng huấn luyện từ đầu.**
Luôn fine-tune từ trọng số pretrained (YOLOv8n / YOLO11n là đủ, và đủ nhẹ để chạy CPU). Train from scratch trên vài chục nghìn ảnh là cách chắc chắn nhất để hết tuần 6 vẫn chưa có gì.

**Đừng ôm cả 50.000 ảnh.**
Lấy tập con cân bằng theo nhóm điều kiện là đủ để hội tụ. Tăng dần nếu còn thời gian, đừng bắt đầu bằng bản đầy đủ.

**Đừng cố chạy YOLO trong PHP.**
Service Python riêng, gọi qua HTTP. Bạn đã làm đúng mô hình này ở `cms-manager → ads-tool` rồi.

**Đừng làm app mobile.**
Web responsive là đủ. Chụp ảnh bằng `<input type="file" capture="environment">` — mở thẳng camera trên điện thoại, không cần React Native, không cần Flutter, không mất ba tuần.

**Đừng làm SaaS nhiều chủ nhà.**
Một chủ nhà, nhiều khu trọ là đủ dày rồi. Thêm tầng multi-tenant chỉ làm phình schema và không ai chấm điểm cho nó.

**Đừng tích hợp cổng thanh toán thật.**
Ghi nhận thanh toán thủ công, cho upload ảnh chuyển khoản là xong. Sandbox cổng thanh toán ăn thời gian mà chẳng thêm điểm nào.

---

## 10. Stack

**Web:** `Laravel 12` · `Blade + Tailwind` · `MySQL` · `queue + scheduler` · `spatie/laravel-permission` · `mpdf` hoặc `spatie/laravel-pdf` · `phpoffice/phpspreadsheet` · `laravel-notification-channels/telegram`

**Nhận dạng:** `Python` · `ultralytics` (YOLOv8/YOLO11) · `FastAPI` · `ONNX Runtime` · `OpenCV` · `Docker`

Giữ nguyên nếp Controller → Validator → Service → Repository đang dùng ở `mpub`, và Blade + Tailwind thay vì học một framework admin mới — hai tháng không đủ để trả giá học phí đó.

> **Lưu ý giấy phép:** `ultralytics` phát hành theo **AGPL-3.0**. Với đồ án học thuật có công bố mã nguồn thì hoàn toàn ổn — chỉ cần ghi rõ trong báo cáo. Nếu về sau muốn thương mại hoá mà không mở mã, phải mua giấy phép Enterprise của Ultralytics, hoặc đổi sang mô hình giấy phép nới hơn như YOLOX (Apache-2.0).

---

*Nghiệp vụ bình thường · một mô hình tự huấn luyện · dữ liệu công khai có nhãn + tự chụp bổ sung.*
*Cùng bộ xương này áp được cho quản lý kho tự đọc phiếu nhập, hoặc quản lý xe tự đọc đồng hồ km — nếu domain nhà trọ không hợp.*
